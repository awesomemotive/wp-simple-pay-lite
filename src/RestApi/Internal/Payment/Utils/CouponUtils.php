<?php
/**
 * Utils: Coupon
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

use Exception;
use SimplePay\Core\API;
use SimplePay\Pro\Coupons\Coupon_Query;
use SimplePay\Pro\Coupons\Coupon;

/**
 * CouponUtils class.
 *
 * @since 4.7.0
 */
class CouponUtils {

	/**
	 * Returns the coupon data for the given request.
	 *
	 * For an amount, return information about what the coupon request data would be.
	 * If the coupon cannot be found, cannot be applied, or is invalid, return an error message.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $coupon_code The coupon code to get data for.
	 * @param int              $amount The amount to apply the coupon to.
	 * @param string           $currency The currency the coupon can apply to.
	 * @return array<string, string|\SimplePay\Pro\Coupons\Coupon|string|float|int> The coupon data.
	 */
	public static function get_coupon_data( $request, $coupon_code, $amount, $currency ) {
		$form = PaymentRequestUtils::get_form( $request );

		// Look at internal records first to force a sync between modes.
		$api_args = $form->get_api_request_args();
		$coupons  = new Coupon_Query(
			$form->is_livemode(),
			$api_args['api_key']
		);

		$coupon = $coupons->get_by_name( $coupon_code );

		// Fall back to a direct Stripe check.
		if ( ! $coupon instanceof Coupon ) {
			try {
				$coupon = API\Coupons\retrieve( $coupon_code, $api_args );
			} catch ( Exception $e ) {
				return array(
					'error' => esc_html__(
						'Sorry, this coupon not valid.',
						'stripe'
					),
				);
			}
		} else {
			// We can only check for restrictions on an internally tracked coupon.
			if ( false === $coupon->applies_to_form( $form->id ) ) {
				return array(
					'error' => esc_html__(
						'Sorry, this coupon not valid.',
						'stripe'
					),
				);
			}

			// Use just the Stripe object of the internal record for the remaining
			// checks to match preexisting direct API usage.
			$coupon = $coupon->object;
		}

		/** @var \SimplePay\Vendor\Stripe\Coupon $coupon */

		// Invalid coupon.
		if ( ! simpay_is_coupon_valid( $coupon ) ) {
			return array(
				'error' => esc_html__(
					'Sorry, this coupon not valid.',
					'stripe'
				),
			);
		}

		// Determines the discounted amount if percentage-based.
		if ( ! empty( $coupon->percent_off ) ) {

			$discount_percent   = ( 100 - $coupon->percent_off ) / 100;
			$discount           = (
				$amount - round( $amount * $discount_percent )
			);
			$discount_formatted = "$coupon->percent_off%";

			// Determines the discounted amount if fixed.
		} elseif ( ! empty( $coupon->amount_off ) ) {
			if ( $coupon->currency !== $currency ) {
				return array(
					'error' => esc_html__(
						'Sorry, this coupon not valid for the selected currency.',
						'stripe'
					),
				);
			}

			$discount           = $coupon->amount_off;
			$discount_formatted = simpay_format_currency(
				$discount,
				$currency
			);
		} else {
			return array(
				'error' => esc_html__(
					'Invalid request. Please try again.',
					'stripe'
				),
			);
		}

		$min          = simpay_convert_amount_to_cents(
			simpay_global_minimum_amount()
		);
		$is_recurring = PaymentRequestUtils::is_recurring( $request );
		// Check if the coupon is not 100% and puts the total below the minimum amount for recurring price.
		if ( $is_recurring && $amount > $discount && (float) 100 !== $coupon->percent_off && ( $amount - $discount ) < $min ) {
			return array(
				'error' => esc_html__(
					'Sorry, this coupon puts the total below the required minimum amount.',
					'stripe'
				),
			);
		}

		// Check if the coupon is not 100% and puts the total below the minimum amount for non-recurring price.
		if ( ! $is_recurring && ( $amount - $discount ) < $min ) {
			return array(
				'error' => esc_html__(
					'Sorry, this coupon puts the total below the required minimum amount.',
					'stripe'
				),
			);
		}

		return array(
			'coupon'   => $coupon,
			'discount' => $discount,
			'message'  => sprintf(
				/* translators: %1$s Coupon code. %2$s discount amount. */
				__( '%1$s: %2$s off', 'stripe' ),
				$coupon->id,
				$discount_formatted
			),
		);
	}

	/**
	 * Returns the discount amount for the given request, amount, and customer.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @param int              $amount_to_discount The amount to calculate the discount amount for.
	 * @param string|null      $customer_id The Stripe Customer ID. If not supplied the discount amount
	 *                                      will not be validated against the customer.
	 * @return int
	 */
	public static function get_discount_unit_amount( $request, $amount_to_discount, $customer_id = null ) {
		$coupon = PaymentRequestUtils::get_coupon_code( $request );
		$form   = PaymentRequestUtils::get_form( $request );

		if ( ! $coupon ) {
			return 0;
		}

		// If we have a customer available, retrieve the coupon from the customer.
		if ( $customer_id ) {
			$customer = API\Customers\retrieve(
				$customer_id,
				$form->get_api_request_args()
			);

			$coupon = isset( $customer->discount )
				? $customer->discount->coupon
				: false;

			if ( false === $coupon ) {
				return 0;
			}

			// Otherwise retrieve the coupon directly.
		} else {
			$coupon = API\Coupons\retrieve(
				$coupon,
				$form->get_api_request_args()
			);
		}

		if ( $coupon->amount_off ) {
			$unit_amount = $coupon->amount_off;
		} else {
			$percent_off = $coupon->percent_off / 100;
			$unit_amount = $amount_to_discount * $percent_off;
		}

		return (int) round( $unit_amount );
	}
}
