<?php
/**
 * Report: Latest Payments
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
 * LatestPaymentsReport class.
 *
 * @since 4.6.7
 */
class LatestPaymentsReport extends Report\AbstractReport implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API route for `GET /wpsp/__internal__/report/latest-payments`.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			'wpsp/__internal__/report',
			'latest-payments',
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
	 * Returns the Latest Payments report.
	 *
	 * @since 4.6.7
	 *
	 * @param \WP_REST_Request $request REST API request.
	 * @return \WP_REST_Response REST API response.
	 */
	public function get_report( $request ) {
		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		$report = array(
			'currency' => array(
				'code'               => $currency,
				'symbol'             => simpay_get_currency_symbol(
					$currency
				),
				'position'           => simpay_get_currency_position(),
				'thousand_separator' => simpay_get_thousand_separator(),
				'decimal_separator'  => simpay_get_decimal_separator(),
			),
			'payments' => array_values( $this->get_results( $currency ) ),
		);

		return new WP_REST_Response( $report );
	}

	/**
	 * Returns the transaction data for the report.
	 *
	 * @since 4.6.7
	 *
	 * @param string $currency Report currency.
	 * @return array<\stdClass>
	 */
	private function get_results( $currency ) {
		global $wpdb;

		// @todo add helper to TransactionRepository.
		$results = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND status IN ( 'succeeded', 'failed', 'requires_payment_method') AND object IN ('payment_intent', 'setup_intent') ORDER BY date_created DESC LIMIT 0, 10",
				simpay_is_test_mode() ? 0 : 1,
				$currency
			),
			OBJECT_K
		);

		return array_map(
			array( $this, 'format_result' ),
			$results
		);
	}

	/**
	 * Formats a result for the Latest Payments report.
	 *
	 * @since 4.6.7
	 *
	 * @param \stdClass $result Result object.
	 * @return \stdClass
	 */
	private function format_result( $result ) {
		$result->object_id = $result->_object_id;
		unset( $result->_object_id );

		$result->amount_total_formatted = simpay_format_currency(
			$result->amount_total,
			$result->currency
		);

		$result->payment_method_type_icon = $this->get_payment_method_type_icon(
			$result->payment_method_type
		);

		$result->status_formatted = $this->format_status( $result->status );

		$result->date_created_human_time_diff = sprintf(
			/* translators: %s Payment created date. */
			__( '%s ago', 'stripe' ),
			human_time_diff(
				strtotime( $result->date_created )
			)
		);

		$result->links = array(
			'customer' => sprintf(
				'https://dashboard.stripe.com/%scustomers?email=%s',
				simpay_is_test_mode() ? 'test/' : '',
				$result->email
			),
			'payment'  => sprintf(
				'https://dashboard.stripe.com/%spayments/%s',
				simpay_is_test_mode() ? 'test/' : '',
				$result->object_id
			),
		);

		return $result;
	}

	/**
	 * Formats a status for the Latest Payments report.
	 *
	 * @since 4.6.7
	 *
	 * @param string $status Status.
	 * @return string
	 */
	private function format_status( $status ) {
		switch ( $status ) {
			case 'succeeded':
				return __( 'Succeeded', 'stripe' );
			case 'failed':
				return __( 'Failed', 'stripe' );
			default:
				return __( 'Incomplete', 'stripe' );
		}
	}

	/**
	 * Returns an icon for a given payment method type from the Payment Method registry.
	 *
	 * @since 4.6.7
	 *
	 * @param string $payment_method_type Payment method type.
	 * @return string
	 */
	private function get_payment_method_type_icon( $payment_method_type ) {
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
			return '<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2H0zm0 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6H0zm3 5a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1z" fill="#6d6e78"/></svg>';
		}

		$payment_method = $payment_methods->get_item( $payment_method_type );

		if ( ! $payment_method instanceof Payment_Method ) {
			return '';
		}

		return $payment_method->icon_sm;
	}

}
