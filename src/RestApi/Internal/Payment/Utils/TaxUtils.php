<?php
/**
 * Utils: Tax
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

use SimplePay\Core\Payments\Stripe_API;

/**
 * TaxUtils class.
 *
 * @since 4.7.0
 */
class TaxUtils {

	/**
	 * Adds automatic tax arguments for the given request and object arguments.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request     $request The payment request.
	 * @param array<string, mixed> $object_args The object arguments.
	 * @return array<string, mixed>
	 */
	public static function add_automatic_tax_args( $request, $object_args ) {
		$form       = PaymentRequestUtils::get_form( $request );
		$tax_status = get_post_meta( $form->id, '_tax_status', true );

		if ( 'automatic' !== $tax_status ) {
			return $object_args;
		}

		$object_args['automatic_tax'] = array(
			'enabled' => true,
		);

		return $object_args;
	}

	/**
	 * Adds fixed tax rates for the given line items and request.
	 *
	 * Unlike `FeeRecoveryUtils::add_subscription_fee_recovery_line_items()`, this
	 * method needs to work for arbitrary line item parameters. Fee Recovery can
	 * do this because Fee Recovery is not compatible with Stripe Checkout.
	 *
	 * Checkout Session: `line_items=`.
	 * Subscription: `add_invoice_items=`, and `items=`.
	 *
	 * @param \WP_REST_Request     $request The payment request.
	 * @param array<string, mixed> $line_items Line items to add tax information.
	 * @return array<string, mixed>
	 */
	public static function add_tax_rates_to_line_items( $request, $line_items ) {
		$form       = PaymentRequestUtils::get_form( $request );
		$tax_rates  = simpay_get_payment_form_tax_rates( $form );
		$tax_status = get_post_meta( $form->id, '_tax_status', true );

		// If using fixed tax rates, add them to the line items.
		if ( ! ( empty( $tax_status ) || 'fixed-global' === $tax_status ) ) {
			return $line_items;
		}

		$tax_rates    = simpay_get_payment_form_tax_rates( $form );
		$tax_rate_ids = ! empty( $tax_rates )
			? wp_list_pluck( $tax_rates, 'id' )
			: array();

		$line_items = array_map(
			function( $line_item ) use ( $tax_rate_ids ) {
				/** @var array<string, mixed> $line_item */
				$line_item['tax_rates'] = $tax_rate_ids;

				return $line_item;
			},
			$line_items
		);

		return $line_items;
	}

	/**
	 * Returns the tax amount for the given request, and amount.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @param int              $amount_to_tax The amount to calculate the tax amount for.
	 * @return int
	 */
	public static function get_tax_unit_amount( $request, $amount_to_tax ) {
		$form       = PaymentRequestUtils::get_form( $request );
		$tax_status = get_post_meta( $form->id, '_tax_status', true );

		// No tax.
		if ( 'none' === $tax_status ) {
			return 0;
		}

		// Automatic tax.
		$tax_calc_id = PaymentRequestUtils::get_tax_calc_id( $request );

		if ( 'automatic' === $tax_status && ! empty( $tax_calc_id ) ) {
			$tax_line_items = Stripe_API::request(
				'Tax\Calculation',
				'allLineItems',
				$tax_calc_id,
				$form->get_api_request_args()
			);

			return array_reduce(
				$tax_line_items->data,
				function( $total, $tax_line_item ) {
					return $total + $tax_line_item->amount_tax;
				},
				0
			);
		}

		// Fixed rates.
		$tax_rates = simpay_get_payment_form_tax_rates( $form );

		if ( empty( $tax_rates ) ) {
			return 0;
		}

		// Remove inclusive tax amount.
		$inclusive_tax_amount = array_reduce(
			$tax_rates,
			function( $amount, $tax_rate ) use ( $amount_to_tax ) {
				if ( 'exclusive' === $tax_rate->calculation ) {
					return $amount;
				}

				return $amount + ( $amount_to_tax * ( $tax_rate->percentage / 100 ) );
			},
			0
		);

		$post_inclusive_unit_amount = round( $amount_to_tax - $inclusive_tax_amount );

		$tax = array_reduce(
			$tax_rates,
			function( $tax, $tax_rate ) use ( $post_inclusive_unit_amount ) {
				if ( 'inclusive' === $tax_rate->calculation ) {
					return $tax;
				}

				$tax_rate = $tax_rate->percentage / 100;

				return $tax + ( $post_inclusive_unit_amount * $tax_rate );
			},
			0
		);

		return (int) round( $tax );
	}

}
