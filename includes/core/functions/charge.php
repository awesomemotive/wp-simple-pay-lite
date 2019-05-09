<?php
/**
 * Adjust the Charge functionality.
 *
 * @since 3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save the "Shipping Address" fields to the charge.
 *
 * These values are sent via hidden fields whose values have been
 * set via the simpayApp.handleStripeAddressArgs() function in `public.js`
 *
 * @since 3.5.0
 *
 * @param array $args Charge arguments.
 * @return array
 */
function simpay_stripe_charge_args_shipping( $args ) {
	$address = simpay_get_form_address_data( 'shipping', $_POST );

	if ( $address && ! empty( $address ) ) {
		$args['shipping']['address'] = $address;

		// A name is required if `shipping` is set.
		$name = '';

		// Check for shipping name from Stripe Checkout.
		if ( isset( $_POST['simpay_shipping_customer_name'] ) ) {
			$name = sanitize_text_field( $_POST['simpay_shipping_customer_name'] );
		}

		$args['shipping']['name'] = $name;
	}

	return $args;
}
add_filter( 'simpay_stripe_charge_args', 'simpay_stripe_charge_args_shipping' );
