<?php
/**
 * Adjust the Customer functionality.
 *
 * @since 3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save the "Billing Address" fields to the customer.
 *
 * The Stripe API uses the `shipping` parameter, but is displayed as billing,
 * so we use billing values.
 *
 * @link https://stripe.com/docs/api/customers/create#create_customer-shipping
 *
 * @since 3.5.0
 *
 * @param array $args Customer arguments.
 * @return array
 */
function simpay_stripe_customer_args_shipping( $args ) {
	$billing_address = simpay_get_form_address_data( 'billing', $_POST );

	if ( $billing_address && ! empty( $billing_address ) ) {
		$args['address'] = $billing_address;
	}

	$shipping_address = simpay_get_form_address_data( 'shipping', $_POST );

	if ( $shipping_address && ! empty( $shipping_address ) ) {
		$args['shipping']['address'] = $shipping_address;

		// A name is required if `shipping` is set.
		// Check if any existing data exists, and use that if there is.
		$name = isset( $args['shipping']['name'] ) ? $args['shipping']['name'] : '';

		// Check for shipping name from Stripe Checkout.
		if ( isset( $_POST['simpay_billing_customer_name'] ) ) {
			$name = sanitize_text_field( $_POST['simpay_billing_customer_name'] );
		}

		$args['shipping']['name'] = $name;
	}

	return $args;
}
add_filter( 'simpay_stripe_customer_args', 'simpay_stripe_customer_args_shipping' );
