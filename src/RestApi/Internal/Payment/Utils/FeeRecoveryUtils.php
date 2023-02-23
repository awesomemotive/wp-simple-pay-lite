<?php
/**
 * Utils: Fee recovery
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

use SimplePay\Pro\Payment_Methods;

/**
 * FeeRecoveryUtils class.
 *
 * @since 4.7.0
 */
class FeeRecoveryUtils {

	/**
	 * Returns the additional fee recovery line items for the given request, and
	 * subscription arguments.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request     $request The payment request.
	 * @param array<string, mixed> $subscription_args The subscription arguments.
	 * @return array<string, mixed>
	 */
	public static function add_subscription_fee_recovery_line_items( $request, $subscription_args ) {
		$price = PaymentRequestUtils::get_price( $request );

		/** @var array<int, array<string, array<string, int>>> $invoice_items */
		$invoice_items = $subscription_args['add_invoice_items'];

		$one_time_line_items_total = array_reduce(
			$invoice_items,
			function( $total, $item ) {
				/** @var array<string, array<string, int>> $item */
				/** @var array<string, int> $price_data */
				$price_data = $item['price_data'];

				return $total + $price_data['unit_amount'];
			},
			0
		);

		$is_trial = (
			$price->recurring &&
			isset( $price->recurring['trial_period_days'] )
		);

		$recurring_line_items_total = ! $is_trial
			? PaymentRequestUtils::get_unit_amount( $request )
			: 0;

		$total_due_today = (
			$one_time_line_items_total + $recurring_line_items_total
		);

		$fee_recovery_today     = self::get_fee_recovery_unit_amount(
			$request,
			$total_due_today
		);
		$fee_recovery_recurring = self::get_fee_recovery_unit_amount(
			$request,
			PaymentRequestUtils::get_unit_amount( $request )
		);

		$one_time_fee_recovery_line_item = array(
			'quantity'   => 1,
			'price_data' => array(
				'unit_amount' => $is_trial
					? $fee_recovery_today
					: $fee_recovery_today - $fee_recovery_recurring,
				'currency'    => $price->currency,
				'product'     => $price->product_id,
			),
		);

		$recurring_fee_recovery_line_item = array(
			'quantity'   => 1,
			'price_data' => array(
				'unit_amount' => $fee_recovery_recurring,
				'currency'    => $price->currency,
				'product'     => $price->product_id,
				'recurring'   => array(
					'interval'       => $price->recurring['interval'],
					'interval_count' => $price->recurring['interval_count'],
				),
			),
		);

		$subscription_args['items'][] = $recurring_fee_recovery_line_item; // @phpstan-ignore-line

		if ( 0 !== $total_due_today ) {
			$subscription_args['add_invoice_items'][] = $one_time_fee_recovery_line_item; // @phpstan-ignore-line
		}

		// Attach the amount as metadata so we can access it later. This is
		// probably not the most obvious spot, but currently it provides
		// the most flexibility for updating the payment amount when a failure occurs.
		$subscription_args['metadata']['simpay_fee_recovery_unit_amount'] = $fee_recovery_recurring; // @phpstan-ignore-line

		return $subscription_args;
	}

	/**
	 * Returns the Fee Recovery amount for the given request, and amount.
	 *
	 * The payment method type must be available in the request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @param int              $amount_to_recover_for The amount to calculate the Fee Recovery amount for.
	 * @return int
	 */
	public static function get_fee_recovery_unit_amount( $request, $amount_to_recover_for ) {
		$is_covering_fees = $request->get_param( 'is_covering_fees' );
		$form             = PaymentRequestUtils::get_form( $request );

		if ( ! $form->has_forced_fee_recovery() && ! $is_covering_fees ) {
			return 0;
		}

		$payment_method_settings = Payment_Methods\get_form_payment_method_settings(
			$form,
			PaymentRequestUtils::get_payment_method_type( $request )
		);

		if (
			! isset(
				$payment_method_settings['fee_recovery'],
				$payment_method_settings['fee_recovery']['enabled']
			) ||
			'yes' !== $payment_method_settings['fee_recovery']['enabled']
		) {
			return 0;
		}

		$percent = $payment_method_settings['fee_recovery']['percent'];
		$fixed   = $payment_method_settings['fee_recovery']['amount'];

		return (int) round(
			( $amount_to_recover_for + $fixed )
				/
			( 1 - ( $percent / 100 ) )
				-
			$amount_to_recover_for
		);
	}

}
