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
use SimplePay\Vendor\Stripe\Stripe;

/**
 * CouponUtils class.
 *
 * @since 4.7.0
 */
class CouponUtils {

	/**
	 * The payment request that `$resolved_coupon_ids` belongs to.
	 *
	 * Only the most recent request is remembered, so the memo cannot grow in
	 * a long-lived process (WP-CLI, queue workers, test runs), and a new
	 * request never sees another request's result.
	 *
	 * @since 4.17.5
	 * @var \WP_REST_Request|null
	 */
	private static $resolved_request = null;

	/**
	 * Stripe Coupon IDs already resolved for `$resolved_request`, keyed by
	 * the entered code.
	 *
	 * A failed resolution is not remembered, so a later call can retry it.
	 *
	 * @since 4.17.5
	 * @var array<string, string>
	 */
	private static $resolved_coupon_ids = array();

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

		$coupon = $coupons->get_by_code( $coupon_code );

		// Fall back to a direct Stripe check.
		if ( ! $coupon instanceof Coupon ) {
			try {
				$coupon = API\Coupons\retrieve( $coupon_code, $api_args );
			} catch ( Exception $e ) {
				return array(
					'error' => esc_html__(
						'Sorry, this coupon is not valid.',
						'stripe'
					),
				);
			}
		} else {
			// We can only check for restrictions on an internally tracked coupon.
			if ( false === $coupon->applies_to_form( $form->id ) ) {
				return array(
					'error' => esc_html__(
						'Sorry, this coupon is not valid.',
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
					'Sorry, this coupon is not valid.',
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
						'Sorry, this coupon is not valid for the selected currency.',
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
		$coupon = self::get_stripe_coupon_id( $request );
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

	/**
	 * Returns the Stripe Coupon ID to redeem for the given request.
	 *
	 * The coupon code is typed by the customer: it may not match the stored
	 * coupon's case, and coupons imported from Stripe can have an ID that is
	 * unrelated to their name. Stripe Coupon IDs are case-sensitive, so the
	 * typed code is resolved to the locally stored coupon's Stripe Coupon ID
	 * for the form's mode. When there is no local record the typed code is
	 * returned unchanged, as it may be a Stripe-only coupon.
	 *
	 * A single payment request resolves the coupon in several places (the
	 * Customer, the Checkout Session, the discount amount and the tax
	 * calculation), so the result is remembered for the most recent request
	 * object.
	 *
	 * @since 4.17.5
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return string|null Stripe Coupon ID, or null if no coupon was entered.
	 */
	public static function get_stripe_coupon_id( $request ) {
		$coupon_code = PaymentRequestUtils::get_coupon_code( $request );

		if ( empty( $coupon_code ) ) {
			return null;
		}

		// Coupons are a Pro feature.
		if ( ! class_exists( Coupon_Query::class ) ) {
			return $coupon_code;
		}

		// A different request starts with an empty memo.
		if ( self::$resolved_request !== $request ) {
			self::$resolved_request    = $request;
			self::$resolved_coupon_ids = array();
		}

		if ( isset( self::$resolved_coupon_ids[ $coupon_code ] ) ) {
			return self::$resolved_coupon_ids[ $coupon_code ];
		}

		$form = PaymentRequestUtils::get_form( $request );

		try {
			$api_args = $form->get_api_request_args();
			$coupons  = new Coupon_Query(
				$form->is_livemode(),
				$api_args['api_key']
			);

			$stripe_coupon_id = $coupons->get_stripe_coupon_id( $coupon_code );

			self::$resolved_coupon_ids[ $coupon_code ] = $stripe_coupon_id;

			return $stripe_coupon_id;
		} catch ( Exception $e ) {
			// Redeem the code as entered, but leave a trace: if it is not a
			// valid Stripe Coupon ID the payment fails with "No such coupon".
			Stripe::getLogger()->error(
				sprintf(
					/* translators: %1$s Coupon code. %2$s Error message. */
					__(
						'Unable to resolve coupon code "%1$s" to a Stripe Coupon ID, so it was used as entered: %2$s',
						'stripe'
					),
					$coupon_code,
					$e->getMessage()
				)
			);

			return $coupon_code;
		}
	}
}
