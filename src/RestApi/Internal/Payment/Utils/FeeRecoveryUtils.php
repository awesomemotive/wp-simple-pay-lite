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

use SimplePay\Core\PaymentForm\PriceOption;
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
		$form = PaymentRequestUtils::get_form( $request );

		if ( $form->allows_multiple_line_items() ) {
			$prices = PaymentRequestUtils::get_price_ids( $request );
			$prices = array_map(
				function ( $price ) use ( $form ) {
					return new PriceOption(
						$price['price_data'],
						$form,
						$price['price_data']['instance_id']
					);
				},
				$prices
			);
		} else {
			$prices = array( PaymentRequestUtils::get_price( $request ) );
		}

		$currency   = $prices[0]->currency;
		$product_id = $prices[0]->product_id;

		/**
		 * Calculate the total line items for the given items.
		 *
		 * @param int $total The total amount.
		 * @param array<string, array{price_data: array{unit_amount:int}|\SimplePay\Core\PaymentForm\PriceOption}> $item The item.
		 * @return int
		 */
		$total_line_items = function ( $total, $item ) use ( $form ) {
			if ( isset( $item['price_data'] ) ) {
				$unit_amount = (int) $item['price_data']['unit_amount'];
			} else {
				$price       = new PriceOption( array( 'id' => $item['price'] ), $form );
				$unit_amount = (int) $price->unit_amount;
			}

			$quantity = (int) $item['quantity'];

			return $total + ( $unit_amount * $quantity );
		};

		$is_trial = false;

		foreach ( $prices as $price ) {
			if ( isset( $price->recurring['trial_period_days'] ) ) {
				$is_trial = true;
				break;
			}
		}

		/**
		 * @var array{
		 *     items: array<string, array{
		 *         price_data: array{
		 *             unit_amount: int
		 *         }|\SimplePay\Core\PaymentForm\PriceOption
		 *     }>,
		 *     add_invoice_items: array<string, array{
		 *         price_data: array{
		 *             unit_amount: int
		 *         }|\SimplePay\Core\PaymentForm\PriceOption
		 *     }>,
		 *     metadata: array<string, mixed>
		 * } $subscription_args
		 */

		$one_time_line_items_total = array_reduce(
			$subscription_args['add_invoice_items'],
			$total_line_items,
			0
		);

		$recurring_line_items_total = array_reduce(
			$subscription_args['items'],
			$total_line_items,
			0
		);

		$total_due_today = (
			$one_time_line_items_total + ( $is_trial ? 0 : $recurring_line_items_total )
		);

		$fee_recovery_today = self::get_fee_recovery_unit_amount(
			$request,
			$total_due_today
		);

		$fee_recovery_recurring = self::get_fee_recovery_unit_amount(
			$request,
			$recurring_line_items_total
		);

		$subscription_args['items'][] = array(
			'quantity'   => 1,
			'price_data' => array(
				'unit_amount' => $fee_recovery_recurring,
				'currency'    => $currency,
				'product'     => $product_id,
				'recurring'   => array(
					'interval'       => $price->recurring['interval'],
					'interval_count' => $price->recurring['interval_count'],
				),
			),
		);

		$one_time_fee_recovery_unit_amount = $is_trial
			? $fee_recovery_today
			: $fee_recovery_today - $fee_recovery_recurring;

		if ( 0 !== $one_time_fee_recovery_unit_amount ) {
			$subscription_args['add_invoice_items'][] = array(
				'quantity'   => 1,
				'price_data' => array(
					'unit_amount' => $one_time_fee_recovery_unit_amount,
					'currency'    => $currency,
					'product'     => $product_id,
				),
			);

			$subscription_args['metadata']['simpay_fee_recovery_initial_unit_amount'] = $one_time_fee_recovery_unit_amount;
		}

		// Attach the amount as metadata so we can access it later. This is
		// probably not the most obvious spot, but currently it provides
		// the most flexibility for updating the payment amount when a failure occurs.
		$subscription_args['metadata']['simpay_fee_recovery_unit_amount'] = $is_trial
			? $fee_recovery_recurring
			: $fee_recovery_today;

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
		if ( 0 === $amount_to_recover_for ) {
			return 0;
		}

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

		$percent                  = $payment_method_settings['fee_recovery']['percent'];
		$fixed                    = $payment_method_settings['fee_recovery']['amount'];
		$max_recoverable_fee      = $payment_method_settings['fee_recovery']['max_amount'];
		$fee_recovery_unit_amount = (int) round(
			( $amount_to_recover_for + $fixed )
				/
			( 1 - ( $percent / 100 ) )
				-
			$amount_to_recover_for
		);

		if ( $max_recoverable_fee && $fee_recovery_unit_amount > $max_recoverable_fee ) {
			return $max_recoverable_fee;
		}

		return $fee_recovery_unit_amount;
	}
}
