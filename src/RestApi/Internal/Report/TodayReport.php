<?php
/**
 * Report: Today Report
 *
 * Technically a Period Over Period to find deltas, but the range is forced.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\RestApi\Internal\Report;

use DateTimeImmutable;
use DateInterval;
use DateTimeZone;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Report;
use stdClass;
use WP_REST_Response;
use WP_REST_Server;

/**
 * TodayReport class.
 *
 * @since 4.6.7
 */
class TodayReport extends Report\AbstractReport implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API route for `GET /wpsp/__internal__/report/today`.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			'wpsp/__internal__',
			'report/today',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_report' ),
					'permission_callback' => array( $this, 'can_view_report' ),
					'args'                => array(
						'currency' => Report\SchemaUtils::get_currency_schema(),
					),
				),
			)
		);
	}

	/**
	 * Returns the dashboard widget report.
	 *
	 * @since 4.6.7
	 *
	 * @param \WP_REST_Request $request REST API request.
	 * @return \WP_REST_Response REST API response.
	 */
	public function get_report( $request ) {
		$range = new Report\DateRange(
			'custom',
			$this->get_yesterday()->format( 'Y-m-d 00:00:00' ),
			$this->get_today()->format( 'Y-m-d 23:59:59' )
		);

		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		// Query for the data.
		$stat_data = $this->get_stat_data( $range, $currency );
		$stats     = $this->format_stats( $stat_data, $currency );

		$top_forms_data = $this->get_top_forms_data( $currency );
		$top_forms      = $this->format_top_forms( $top_forms_data, $currency );

		$report = array(
			'currency'  => array(
				'code'               => $currency,
				'symbol'             => simpay_get_currency_symbol(
					$currency
				),
				'position'           => simpay_get_currency_position(),
				'thousand_separator' => simpay_get_thousand_separator(),
				'decimal_separator'  => simpay_get_decimal_separator(),
			),
			'stats'     => $stats,
			'top_forms' => $top_forms,
			'tip'       => count( $top_forms ) <= 2 ? $this->get_tip() : null,
		);

		return new WP_REST_Response( $report );
	}

	/**
	 * Returns the transaction data for to be used in the stats.
	 *
	 * @since 4.6.7
	 *
	 * @param \SimplePay\Core\Report\DateRange $range Date range.
	 * @param string                           $currency Report currency.
	 * @return array<string, \stdClass>
	 */
	private function get_stat_data( $range, $currency ) {
		$select_date = $this->get_sql_select_date_as( 'D' );

		$select_amount_value = simpay_is_zero_decimal( $currency )
			? "SUM(CASE WHEN status = 'succeeded' THEN amount_total ELSE 0 END)"
			: "SUM(CASE WHEN status = 'succeeded' THEN amount_total / 100 ELSE 0 END)";

		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$select_date} as date, {$select_amount_value} as gross_volume, COUNT(DISTINCT email) customers, SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END) successful_payments, SUM(CASE WHEN object != 'checkout_session' THEN 1 ELSE 0 END) all_payments FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND date_created BETWEEN %s AND %s GROUP BY date", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				simpay_is_test_mode() ? 0 : 1,
				$currency,
				$range->start->format( 'Y-m-d 00:00:00' ),
				$range->end->format( 'Y-m-d 23:59:59' )
			),
			OBJECT_K
		);

		// Fill in the gaps manually.
		// No need to use the chart helpers here. Keep it simple.
		$empty_day                      = new stdClass();
		$empty_day->gross_volume        = 0;
		$empty_day->customers           = 0;
		$empty_day->successful_payments = 0;
		$empty_day->all_payments        = 0;

		$today     = $this->get_today()->format( 'Y-m-d' );
		$yesterday = $this->get_yesterday()->format( 'Y-m-d' );

		// ...if today is empty.
		if ( ! isset( $results[ $today ] ) ) {
			$results[ $today ] = $empty_day;
		}

		// ...if yesterday is empty.
		if ( ! isset( $results[ $yesterday ] ) ) {
			$results[ $yesterday ] = $empty_day;
		}

		return $results;
	}

	/**
	 * Formats the transaction data for the report.
	 *
	 * @since 4.6.7
	 *
	 * @param array<string, \stdClass> $results Transaction data.
	 * @param string                   $currency Report currency.
	 * @return array<int, array<string, mixed>>
	 */
	private function format_stats( $results, $currency ) {
		$today     = $results[ $this->get_today()->format( 'Y-m-d' ) ];
		$yesterday = $results[ $this->get_yesterday()->format( 'Y-m-d' ) ];

		return array(
			// Gross Volume.
			array(
				'label' => __( 'Gross Volume', 'stripe' ),
				'value' => simpay_format_currency(
					$today->gross_volume,
					$currency,
					true,
					false
				),
				'delta' => $this->get_delta(
					$today->gross_volume,
					$yesterday->gross_volume
				),
			),
			// Successful Payments.
			array(
				'label' => __( 'Successful Payments', 'stripe' ),
				'value' => $today->successful_payments,
				'delta' => $this->get_delta(
					$today->successful_payments,
					$yesterday->successful_payments
				),
			),
			// Customers.
			array(
				'label' => __( 'Customers', 'stripe' ),
				'value' => $today->customers,
				'delta' => $this->get_delta(
					$today->customers,
					$yesterday->customers
				),
			),
			// All Payments.
			array(
				'label' => __( 'All Payments', 'stripe' ),
				'value' => $today->all_payments,
				'delta' => $this->get_delta(
					$today->all_payments,
					$yesterday->all_payments
				),
			),
		);
	}

	/**
	 * Returns the transaction data for to be used in the Top Forms list.
	 *
	 * @since 4.6.7
	 *
	 * @param string $currency Report currency.
	 * @return array<string, array<int, \stdClass>>
	 */
	private function get_top_forms_data( $currency ) {
		global $wpdb;

		$forms_per_day = array();
		$days          = array(
			$this->get_today(),
			$this->get_yesterday(),
		);

		foreach ( $days as $day ) {
			$forms_per_day[ $day->format( 'Y-m-d' ) ] = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT form_id as id, SUM(amount_total) as gross_volume FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s GROUP BY form_id ORDER BY gross_volume DESC LIMIT 0, 5",
					simpay_is_test_mode() ? 0 : 1,
					$currency,
					$day->format( 'Y-m-d 00:00:00' ),
					$day->format( 'Y-m-d 23:59:59' )
				),
				OBJECT_K
			);
		}

		return $forms_per_day;
	}

	/**
	 * Formats the top forms data for the report.
	 *
	 * @since 4.6.7
	 *
	 * @param array<string, array<int, \stdClass>> $top_forms Top forms data.
	 * @param string                               $currency Report currency.
	 * @return array<int, array<string, mixed>>
	 */
	private function format_top_forms( $top_forms, $currency ) {
		$today     = $top_forms[ $this->get_today()->format( 'Y-m-d' ) ];
		$yesterday = $top_forms[ $this->get_yesterday()->format( 'Y-m-d' ) ];

		$top_forms = array_map(
			function( $form ) use ( $today, $yesterday, $currency ) {
				/** @var int $previous_position */
				$previous_position = isset( $yesterday[ $form->id ] )
					? array_search( $form->id, array_keys( $yesterday ), true )
					: 0;

				/** @var int $current_position */
				$current_position = isset( $today[ $form->id ] )
					? array_search( $form->id, array_keys( $today ), true )
					: 0;

				$delta = $this->get_delta( $current_position, $previous_position );

				$form_title = get_post_meta(
					$form->id,
					'_company_name',
					true
				);

				if ( empty( $form_title ) ) {
					$form_title = __( '(no title)', 'stripe' );
				}

				return array(
					'id'           => $form->id,
					'title'        => $form_title,
					'href'         => get_edit_post_link( $form->id ),
					'gross_volume' => simpay_format_currency(
						$form->gross_volume,
						$currency
					),
					'delta'        => $delta,
				);
			},
			$today
		);

		return array_values( $top_forms );
	}

	/**
	 * Returns a tip to display in the report.
	 *
	 * @since 4.6.7
	 *
	 * @return array<string, string>
	 */
	private function get_tip() {
		$tips = $this->get_tips();

		return $tips[ array_rand( $tips ) ];
	}

	/**
	 * Returns an array of tips to display in the report.
	 *
	 * @since 4.6.7
	 *
	 * @return array<array<string, string>>
	 */
	private function get_tips() {
		$tips = array(
			array(
				'title' => 'Set up automatic tax collection',
				'href'  => 'https://wpsimplepay.com/how-to-collect-taxes-for-stripe-payments-in-wordpress/',
				'text'  => 'Charge a fixed tax rate to everyone or collect different taxes dynamically based on customer location',
			),
			array(
				'title' => 'Dynamically customize payment receipts',
				'href'  => 'https://wpsimplepay.com/how-to-easily-customize-stripe-email-receipts-in-wordpress/ ',
				'text'  => 'Customize every single detail based on the data collected from users.',
			),
			array(
				'title' => 'We support 10+ payment methods',
				'href'  => 'https://wpsimplepay.com/how-to-allow-users-to-choose-a-payment-method-in-wordpress/',
				'text'  => 'Offer multiple payment methods and allow users to pay with their favorite.',
			),
			array(
				'title' => 'Smart inventory management',
				'href'  => 'https://wpsimplepay.com/how-to-create-an-order-form-with-wordpress-step-by-step/',
				'text'  => 'Automatically hide your payment form after a set number of payments.',
			),
			array(
				'title' => 'Encourage payments with discount codes',
				'href'  => 'https://wpsimplepay.com/how-to-add-a-coupon-code-field-to-your-wordpress-payment-forms/',
				'text'  => 'Offer a fixed amount or a percentage discount for a specified duration or forever.',
			),
			array(
				'title' => 'Remove Stripe processing fees',
				'href'  => 'https://wpsimplepay.com/how-to-remove-credit-card-processing-fees-in-wordpress/',
				'text'  => 'Charge an additional fee to ensure you receive the full payment amount.',
			),
		);

		return array_map(
			function( $tip ) {
				$tip['href'] = simpay_ga_url(
					$tip['href'],
					'activity-reports',
					$tip['title']
				);

				return $tip;
			},
			$tips
		);
	}

	/**
	 * Returns a DateTimeImmutable object for today.
	 *
	 * @since 4.6.7
	 *
	 * @return \DateTimeImmutable
	 */
	private function get_today() {
		return new DateTimeImmutable(
			'now',
			new DateTimeZone( $this->get_timezone_offset() )
		);
	}

	/**
	 * Returns a DateTimeImmutable object for yesterday.
	 *
	 * @since 4.6.7
	 *
	 * @return \DateTimeImmutable
	 */
	private function get_yesterday() {
		$yesterday = new DateTimeImmutable(
			'now',
			new DateTimeZone( $this->get_timezone_offset() )
		);
		$yesterday = $yesterday->sub( new DateInterval( 'P1D' ) );

		return $yesterday;
	}

}
