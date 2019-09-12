<?php
/**
 * General functionality for managing Stripe Checkout.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Stripe_Checkout;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves the payment types available for Stripe Checkout.
 *
 * @since 3.6.0
 *
 * @return array
 */
function get_available_payment_method_types() {
	$payment_method_types = array(
		'card',
	);

	/**
	 * Filters the payment types available for Stripe Checkout.
	 *
	 * @since 3.6.0
	 *
	 * @param array $payment_types Available payment types.
	 */
	$payment_method_types = apply_filters(
		'simpay_stripe_checkout_available_payment_method_types',
		$payment_method_types
	);

	return $payment_method_types;
}
