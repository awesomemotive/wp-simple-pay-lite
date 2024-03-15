<?php
/**
 * Report: Activity Overview
 *
 * Top stats (with deltas) and top 5 forms (with deltas), and a DYK blurb.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Report;

use DateInterval;
use stdClass;

/**
 * ActivityOverviewReport class.
 *
 * @since 4.7.3
 */
class ActivityOverviewReport {

	use ReportTrait;

	/**
	 * The report date range.
	 *
	 * The start and end dates of the range are used to determine the dates that
	 * are calculated for each stat.
	 *
	 * e.g if the range has 7 days then gross volume would be the total amount
	 * of all transactions for each of the 7 days compared to the total amount
	 * of all transactions for the previous 7 days.
	 *
	 * @since 4.7.3
	 * @var \SimplePay\Core\Report\DateRange
	 */
	protected $range;

	/**
	 * The report currency.
	 *
	 * @since 4.7.3
	 * @var string
	 */
	protected $currency;

	/**
	 * The report ranges.
	 *
	 * @since 4.7.3
	 * @var array<string, \SimplePay\Core\Report\DateRange>
	 */
	protected $ranges;

	/**
	 * ActivityOverviewReport.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Core\Report\DateRange $range The report date range.
	 * @param string                           $currency The report currency.
	 */
	public function __construct( $range, $currency ) {
		$this->range    = $range;
		$this->currency = $currency;

		// The current range is the range passed in.
		$current = $range;

		// The previous range is created by offsetting the current range by the
		// number of days in the current range.
		$diff = $this->range->end->diff( $this->range->start );

		// If the difference is less than a day, set the difference to 1 day.
		$days = 0 === $diff->days ? 1 : $diff->days - 1;

		$interval = new DateInterval( sprintf( 'P%dD', $days ) );

		$previous = new DateRange(
			'custom',
			$range->start->sub( $interval )->format( 'Y-m-d 00:00:00' ),
			$range->end->sub( $interval )->format( 'Y-m-d 23:59:59' )
		);

		$this->ranges = array(
			'current'  => $current,
			'previous' => $previous,
		);
	}

	/**
	 * Formats the transaction data for the report.
	 *
	 * @since 4.7.3
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_stats() {
		$stats    = $this->get_stats_data();
		$current  = $stats['current'];
		$previous = $stats['previous'];

		return array(
			// Gross Volume.
			array(
				'id'    => 'gross-volume',
				'label' => __( 'Gross Volume', 'stripe' ),
				'icon'  => '💰',
				'type'  => 'currency',
				'value' => array(
					'raw'      => $current->gross_volume,
					'rendered' => simpay_format_currency(
						$current->gross_volume,
						$this->currency,
						true,
						false
					),
				),
				'delta' => $this->get_delta(
					$current->gross_volume,
					$previous->gross_volume
				),
			),
			// Successful Payments.
			array(
				'id'    => 'successful-payments',
				'label' => __( 'Successful Payments', 'stripe' ),
				'icon'  => '✅',
				'type'  => 'number',
				'value' => array(
					'raw'      => $current->successful_payments,
					'rendered' => $current->successful_payments,
				),
				'delta' => $this->get_delta(
					$current->successful_payments,
					$previous->successful_payments
				),
			),
			// Customers.
			array(
				'id'    => 'new-customers',
				'label' => __( 'Customers', 'stripe' ),
				'icon'  => '👤',
				'value' => array(
					'raw'      => $current->customers,
					'rendered' => $current->customers,
				),
				'delta' => $this->get_delta(
					$current->customers,
					$previous->customers
				),
			),
			// All Payments.
			array(
				'id'    => 'all-payments',
				'label' => __( 'All Payments', 'stripe' ),
				'icon'  => '📊',
				'value' => array(
					'raw'      => $current->all_payments,
					'rendered' => $current->all_payments,
				),
				'delta' => $this->get_delta(
					$current->all_payments,
					$previous->all_payments
				),
			),
		);
	}

	/**
	 * Formats the top forms data for the report.
	 *
	 * @since 4.7.3
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_top_forms() {
		$top_forms = $this->get_top_forms_data();
		$current   = $top_forms['current'];
		$previous  = $top_forms['previous'];

		$top_forms = array_map(
			function( $form ) use ( $current, $previous ) {
				/** @var float $current_gross */
				$current_gross = isset( $current[ $form->id ] )
					? (float) $current[ $form->id ]->gross_volume
					: (float) 0;

				/** @var float $previous_gross */
				$previous_gross = isset( $previous[ $form->id ] )
					? (float) $previous[ $form->id ]->gross_volume
					: (float) 0;

				$delta = $this->get_delta( $current_gross, $previous_gross );

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
					'href'         => add_query_arg(
						array(
							'post'   => $form->id,
							'action' => 'edit',
						),
						admin_url( 'post.php' )
					),
					'gross_volume' => array(
						'raw'      => $form->gross_volume,
						'rendered' => simpay_format_currency(
							$form->gross_volume,
							$this->currency
						),
					),
					'delta'        => $delta,
				);
			},
			$current
		);

		return array_values( $top_forms );
	}

	/**
	 * Returns a tip to display in the report.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string, string>
	 */
	public function get_tip() {
		$tips = $this->get_tips();

		return $tips[ array_rand( $tips ) ];
	}

	/**
	 * Returns the transaction data for to be used in the stats.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string, \stdClass>
	 */
	private function get_stats_data() {
		$stats   = array();
		$periods = array( 'current', 'previous' );

		foreach ( $periods as $period ) {
			$period_stats = new stdClass();

			// Gross volume.
			$period_stats->gross_volume = $this->get_gross_volume( $period );

			// Customers.
			$period_stats->customers = $this->get_customers( $period );

			// Successful payments.
			$period_stats->successful_payments = $this->get_payments(
				$period,
				array( 'succeeded' )
			);

			// All payments.
			$period_stats->all_payments = $this->get_payments( $period );

			$stats[ $period ] = $period_stats;
		}

		return $stats;
	}

	/**
	 * Returns the transaction data for to be used in the Top Forms list.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string, array<int, \stdClass>>
	 */
	private function get_top_forms_data() {
		global $wpdb;

		$forms   = array();
		$periods = array( 'current', 'previous' );

		foreach ( $periods as $period ) {
			$forms[ $period ] = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT form_id as id, SUM(amount_total) as gross_volume FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s GROUP BY form_id ORDER BY gross_volume DESC LIMIT 0, 5",
					simpay_is_test_mode() ? 0 : 1,
					$this->currency,
					$this->ranges[ $period ]->start->format( 'Y-m-d 00:00:00' ),
					$this->ranges[ $period ]->end->format( 'Y-m-d 23:59:59' )
				),
				OBJECT_K
			);
		}

		return $forms;
	}

	/**
	 * Returns the gross volume for the given period.
	 *
	 * @since 4.7.3
	 *
	 * @param string $period The period to get the gross volume for.
	 * @return float
	 */
	private function get_gross_volume( $period ) {
		global $wpdb;

		$select_value = simpay_is_zero_decimal( $this->currency )
			? 'SUM(amount_total)'
			: 'SUM(amount_total / 100)';

		$volume = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT {$select_value} FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				simpay_is_test_mode() ? 0 : 1,
				$this->currency,
				$this->ranges[ $period ]->start->format( 'Y-m-d 00:00:00' ),
				$this->ranges[ $period ]->end->format( 'Y-m-d 23:59:59' )
			)
		);

		return null === $volume ? 0 : $volume;
	}

	/**
	 * Returns the number of payments for the given period and state(s).
	 *
	 * @since 4.7.3
	 *
	 * @param string               $period The period to get the gross volume for.
	 * @param string|array<string> $states The state(s) to get the number of payments for.
	 * @return float
	 */
	private function get_payments( $period, $states = array() ) {
		global $wpdb;

		if ( is_string( $states ) ) {
			$states = array( $states );
		}

		if ( empty( $states ) ) {
			$select = "SUM(CASE WHEN object != 'checkout_session' THEN 1 ELSE 0 END)";
		} else {
			$in = implode( ',', $states );

			$select = $wpdb->prepare(
				'SUM(CASE WHEN status IN(%s) THEN 1 ELSE 0 END)',
				$in
			);
		}

		$payments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT {$select} FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND date_created BETWEEN %s AND %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				simpay_is_test_mode() ? 0 : 1,
				$this->currency,
				$this->ranges[ $period ]->start->format( 'Y-m-d 00:00:00' ),
				$this->ranges[ $period ]->end->format( 'Y-m-d 23:59:59' )
			)
		);

		return null === $payments ? 0 : $payments;
	}

	/**
	 * Returns the number of customers (unique by email) for the given period.
	 *
	 * @since 4.7.3
	 *
	 * @param string $period The period to get the gross volume for.
	 * @return int
	 */
	private function get_customers( $period ) {
		global $wpdb;

		$customers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT email) FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				simpay_is_test_mode() ? 0 : 1,
				$this->currency,
				$this->ranges[ $period ]->start->format( 'Y-m-d 00:00:00' ),
				$this->ranges[ $period ]->end->format( 'Y-m-d 23:59:59' )
			)
		);

		return null === $customers ? 0 : $customers;
	}

	/**
	 * Returns an array of tips to display in the report.
	 *
	 * @since 4.7.3
	 *
	 * @return array<array<string, string>>
	 */
	private function get_tips() {
		$tips = array(
			array(
				'title' => __(
					'Set up automatic tax collection',
					'stripe'
				),
				'href'  => 'https://wpsimplepay.com/how-to-collect-taxes-for-stripe-payments-in-wordpress/',
				'text'  => __(
					'Charge a fixed tax rate to everyone or collect different taxes dynamically based on customer location',
					'stripe'
				),
			),
			array(
				'title' => __(
					'Dynamically customize payment receipts',
					'stripe'
				),
				'href'  => 'https://wpsimplepay.com/how-to-easily-customize-stripe-email-receipts-in-wordpress/ ',
				'text'  => __(
					'Customize every single detail based on the data collected from users.',
					'stripe'
				),
			),
			array(
				'title' => __(
					'We support 10+ payment methods',
					'stripe'
				),
				'href'  => 'https://wpsimplepay.com/how-to-allow-users-to-choose-a-payment-method-in-wordpress/',
				'text'  => __(
					'Offer multiple payment methods and allow users to pay with their favorite.',
					'stripe'
				),
			),
			array(
				'title' => __(
					'Smart inventory management',
					'stripe'
				),
				'href'  => 'https://wpsimplepay.com/how-to-create-an-order-form-with-wordpress-step-by-step/',
				'text'  => __(
					'Automatically hide your payment form after a set number of payments.',
					'stripe'
				),
			),
			array(
				'title' => __(
					'Encourage payments with discount codes',
					'stripe'
				),
				'href'  => 'https://wpsimplepay.com/how-to-add-a-coupon-code-field-to-your-wordpress-payment-forms/',
				'text'  => __(
					'Offer a fixed amount or a percentage discount for a specified duration or forever.',
					'stripe'
				)
			),
			array(
				'title' => __(
					'Remove Stripe processing fees',
					'stripe'
				),
				'href'  => 'https://wpsimplepay.com/how-to-remove-credit-card-processing-fees-in-wordpress/',
				'text'  => __(
					'Charge an additional fee to ensure you receive the full payment amount.',
					'stripe'
				),
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

}
