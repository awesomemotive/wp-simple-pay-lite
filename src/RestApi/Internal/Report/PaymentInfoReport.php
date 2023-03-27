<?php
/**
 * Report: Payment Info
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\RestApi\Internal\Report;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Report;
use SimplePay\Core\Utils;
use SimplePay\Pro\Payment_Methods\Payment_Method;
use WP_REST_Response;
use WP_REST_Server;

/**
 * PaymentInfoReport class.
 *
 * @since 4.6.7
 */
class PaymentInfoReport implements SubscriberInterface {

	use Report\ReportTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API route for `GET /wpsp/__internal__/report/payment-info`.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			'wpsp/__internal__',
			'report/payment-info',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_report' ),
					'permission_callback' => array( $this, 'can_view_report' ),
					'args'                => array(
						'range'    => SchemaUtils::get_date_range_schema(),
						'currency' => SchemaUtils::get_currency_schema(),
					),
				),
			)
		);
	}

	/**
	 * Determines if the current user can view the report.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function can_view_report() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns the Payment Info report.
	 *
	 * @since 4.6.7
	 *
	 * @param \WP_REST_Request $request REST API request.
	 * @return \WP_REST_Response REST API response.
	 */
	public function get_report( $request ) {
		/** @var \SimplePay\Core\Report\DateRange $range */
		$range = $request->get_param( 'range' );

		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		// Query for the data.
		$payment_method_types = $this->get_grouped_results(
			'payment_method_type',
			$range,
			$currency
		);
		$payment_method_types = $this->format_payment_method_types(
			$payment_method_types
		);

		$payment_statuses = $this->get_grouped_results(
			'status',
			$range,
			$currency
		);
		$payment_statuses = $this->format_payment_statuses(
			$payment_statuses
		);

		$report = array(
			'currency'             => array(
				'code'               => $currency,
				'symbol'             => simpay_get_currency_symbol(
					$currency
				),
				'position'           => simpay_get_currency_position(),
				'thousand_separator' => simpay_get_thousand_separator(),
				'decimal_separator'  => simpay_get_decimal_separator(),
			),
			'payment_method_types' => $payment_method_types,
			'payment_statuses'     => $payment_statuses,
		);

		return new WP_REST_Response( $report );
	}

	/**
	 * Returns the transaction data for the report.
	 *
	 * @since 4.6.7
	 *
	 * @param string                           $column Column to group by.
	 * @param \SimplePay\Core\Report\DateRange $range Report date range.
	 * @param string                           $currency Report currency.
	 * @return array<\stdClass>
	 */
	private function get_grouped_results( $column, $range, $currency ) {
		global $wpdb;

		$status = 'status' === $column ? '' : "AND status = 'succeeded'";

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT $column, COUNT(IFNULL($column, 1)) as count FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s $status AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s GROUP BY $column", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				simpay_is_test_mode() ? 0 : 1,
				$currency,
				$range->start->format( 'Y-m-d 00:00:00' ),
				$range->end->format( 'Y-m-d 23:59:59' )
			),
			OBJECT_K
		);
	}

	/**
	 * Formats the payment method types for the report.
	 *
	 * @since 4.6.7
	 *
	 * @param array<\stdClass> $payment_method_types Payment method types.
	 * @return array<array<string, float|string>>
	 */
	private function format_payment_method_types( $payment_method_types ) {
		$total = array_reduce(
			$payment_method_types,
			function( $total, $payment_method_type ) {
				return $total + (int) $payment_method_type->count;
			},
			0
		);

		$payment_method_types = array_map(
			function( $payment_method_type ) use ( $total ) {
				$count = (int) $payment_method_type->count;
				$type  = $payment_method_type->payment_method_type;

				return array(
					'label' => null === $type
						? 'n/a'
						: $this->get_payment_method_type_label( $type ),
					'value' => floor( $count / $total * 100 ),
					'color' => $this->get_payment_method_type_color( $type ),
				);
			},
			$payment_method_types
		);

		// Move N/A to the end.
		if ( isset( $payment_method_types[''] ) ) {
			$na = $payment_method_types[''];
			unset( $payment_method_types[''] );
			$payment_method_types['na'] = $na;
		}

		return array_values( $payment_method_types );
	}

	/**
	 * Formats the payment statuses for the report.
	 *
	 * @since 4.6.7
	 *
	 * @param array<\stdClass> $payment_statuses Payment statuses.
	 * @return array<array<string, float|string>>
	 */
	private function format_payment_statuses( $payment_statuses ) {
		// First, merge any statuses that are not succeeded or failed in to the incomplete status.
		$keep = array();

		foreach ( array( 'succeeded', 'failed', 'incomplete' ) as $status ) {
			if ( isset( $payment_statuses[ $status ] ) ) {
				$keep[ $status ] = $payment_statuses[ $status ];
				unset( $payment_statuses[ $status ] );
			}
		}

		// If there are any statuses left, merge them in to the incomplete status.
		if ( ! empty( $payment_statuses ) ) {
			$keep['incomplete'] = (object) array(
				'status' => 'incomplete',
				'count'  => array_reduce(
					$payment_statuses,
					function( $total, $payment_status ) {
						return $total + (int) $payment_status->count;
					},
					isset( $keep['incomplete'] ) ? (int) $keep['incomplete']->count : 0
				),
			);
		}

		$payment_statuses = $keep;

		// Then continue with formatting.

		$total = array_reduce(
			$payment_statuses,
			function( $total, $payment_status ) {
				return $total + (int) $payment_status->count;
			},
			0
		);

		$payment_statuses = array_map(
			function( $payment_status ) use ( $total ) {
				$count  = (int) $payment_status->count;
				$status = $payment_status->status;

				return array(
					'label' => null === $status
						? 'n/a'
						: $this->get_payment_status_label( $status ),
					'value' => floor( $count / $total * 100 ),
					'color' => $this->get_payment_status_color( $status ),
				);
			},
			$payment_statuses
		);

		return array_values( $payment_statuses );
	}

	/**
	 * Returns the payment method type label.
	 *
	 * @since 4.6.7
	 *
	 * @param string $payment_method_type Payment method type.
	 * @return string
	 */
	private function get_payment_method_type_label( $payment_method_type ) {
		// Reset payment method types to WP Simple Pay IDs, not Stripe IDs.
		// i.e us_bank_account back to ach-debit.
		switch ( $payment_method_type ) {
			case 'sepa_debit':
				$payment_method_type = 'sepa-debit';
				break;
			case 'us_bank_account':
				$payment_method_type = 'ach-debit';
				break;
			case 'afterpay_clearpay':
				$payment_method_type = 'afterpay-clearpay';
				break;
			default:
				$payment_method_type = $payment_method_type;
		}

		$payment_methods = Utils\get_collection( 'payment-methods' );

		if ( ! $payment_methods instanceof Utils\Collection ) {
			return __( 'Card', 'stripe' );
		}

		if ( 'link' === $payment_method_type ) {
			return 'Link';
		} else {
			$payment_method = $payment_methods->get_item( $payment_method_type );

			if ( ! $payment_method instanceof Payment_Method ) {
				return '';
			}

			return $payment_method->nicename;
		}
	}

	/**
	 * Returns the payment method type color.
	 *
	 * @since 4.6.7
	 *
	 * @param string $payment_method_type Payment method type.
	 * @return string
	 */
	private function get_payment_method_type_color( $payment_method_type ) {
		switch ( $payment_method_type ) {
			case 'alipay':
				return '#1a9fe5';
			case 'afterpay_clearpay':
				return '#b2fce4';
			case 'bancontact':
				return '#ffbf00';
			case 'card':
				return '#9ca3af';
			case 'fpx':
				return '#188acb';
			case 'giropay':
				return '#ee3525';
			case 'ideal':
				return '#cc0166';
			case 'klarna':
				return '#ffb3c7';
			case 'link':
				return '#33ddb3';
			case 'p24':
				return '#d40f2b';
			case 'sepa_debit':
				return '#10298c';
			case 'us_bank_account':
				return '#6cb065';
			default:
				return '#ebebeb';
		}
	}

	/**
	 * Returns the payment method status label.
	 *
	 * @since 4.6.7
	 *
	 * @param string $payment_status Payment status.
	 * @return string
	 */
	private function get_payment_status_label( $payment_status ) {
		switch ( $payment_status ) {
			case 'failed':
				return __( 'Failed', 'stripe' );
			case 'succeeded':
				return __( 'Succeeded', 'stripe' );
			default:
				return __( 'Incomplete', 'stripe' );
		}
	}

	/**
	 * Returns the payment method type color.
	 *
	 * @since 4.6.7
	 *
	 * @param string $payment_status Payment status.
	 * @return string
	 */
	private function get_payment_status_color( $payment_status ) {
		switch ( $payment_status ) {
			case 'failed':
				return '#c6416a';
			case 'succeeded':
				return '#6cb065';
			default:
				return '#ebebeb';
		}
	}
}
