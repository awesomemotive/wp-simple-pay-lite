<?php
/**
 * Plugin shortcode functions
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to process the [stripe_checkout] shortcode
 * 
 * @since 1.0.0
 */
function sc_stripe_shortcode( $attr ) {
	
	global $sc_options;
	
	extract( shortcode_atts( array(
					'name'                  => ( ! empty( $sc_options['name'] )                  ? $sc_options['name']                  : get_bloginfo( 'title' ) ),
					'description'           => ( ! empty( $sc_options['description'] )           ? $sc_options['description']           : '' ),
					'amount'                => ( ! empty( $sc_options['amount'] )                ? $sc_options['amount']                : '' ),
					'image_url'             => ( ! empty( $sc_options['image_url'] )             ? $sc_options['image_url']             : '' ),
					'currency'              => ( ! empty( $sc_options['currency'] )              ? $sc_options['currency']              : 'USD' ),
					'checkout_button_label' => ( ! empty( $sc_options['checkout_button_label'] ) ? $sc_options['checkout_button_label'] : '' ),
					'billing'               => ( ! empty( $sc_options['billing'] )               ? $sc_options['billing']               : '' ),    // true or false
					'shipping'              => ( ! empty( $sc_options['shipping'] )              ? $sc_options['shipping']              : '' ),    // true or false
					'payment_button_label'  => ( ! empty( $sc_options['payment_button_label'] )  ? $sc_options['payment_button_label']  : '' ),
					'enable_remember'       => ( ! empty( $sc_options['enable_remember'] )       ? $sc_options['enable_remember']       : 0 ),    // true or false
					'success_redirect_url'  => ( ! empty( $sc_options['success_redirect_url'] )  ? $sc_options['success_redirect_url']  : get_permalink() )
				), $attr ) );
	
	if( empty( $amount ) || $amount < 50 )
		return '';
	
	
	if( empty( $sc_options['enable_test_key'] ) ) {
		$data_key = ( ! empty( $sc_options['live_publish_key'] ) ? $sc_options['live_publish_key'] : '' );
	} else {
		$data_key = ( ! empty( $sc_options['test_publish_key'] ) ? $sc_options['test_publish_key'] : '' );
	}
	
	// We will set all of our Script options here now
	// Need to make sure to add a {space} at the end of each string so they don't all become one line of continuous text
	$script_options = '';
	
	// Required
	$script_options .= 'data-key="' . $data_key . '" ';
	
	// Highly recommended
	$script_options .= 'data-name="' . esc_attr( $name ) . '" ';
	$script_options .= 'data-description="' . esc_attr( $description ) . '" ';
	$script_options .= 'data-amount="' . esc_attr( $amount ) . '" ';
	$script_options .= ( ! empty( $image_url ) ? 'data-image="' . esc_url( $image_url ) . '" ' : '' );
	
	// Optional
	$script_options .= 'data-currency="' . esc_attr( $currency ) . '" ';
	$script_options .= ( ! empty( $checkout_button_label ) ? 'data-panel-label="' . esc_attr( $checkout_button_label ) . '" ' : '' );
	$script_options .= ( ( ! empty( $billing ) && $billing != 'false' ) ? 'data-billing-address="' . esc_attr( $billing ) . '" ' : '' );
	$script_options .= ( ( ! empty( $shipping ) && $shipping != 'false' ) ? 'data-shipping-address="' . esc_attr( $shipping ) . '" ' : '' );
	$script_options .= ( ! empty( $payment_button_label ) ? 'data-label="' . esc_attr( $payment_button_label ) . '" ' : '' );
	$script_options .= ( $enable_remember != 1 ? 'data-allow-remember-me="false" ' : '' );
	
	
	if( ! isset( $_GET['payment'] ) ) { 
		$html = '<form action="" method="POST">
					<script src="https://checkout.stripe.com/checkout.js" class="stripe-button" ' . $script_options . '>
					</script>
					<input type="hidden" name="sc-name" value="' . esc_attr( $name ) . '" />
					<input type="hidden" name="sc-description" value="' . esc_attr( $description ) . '" />
					<input type="hidden" name="sc-amount" value="' . esc_attr( $amount ) . '" />
					<input type="hidden" name="sc-redirect" value="' . esc_attr( ( ! empty( $success_redirect_url ) ? $success_redirect_url : get_permalink() ) ) . '" />
					<input type="hidden" name="sc-currency" value="' .esc_attr( $currency ) . '" />
				  </form>';
		
		return $html;
	}
	
	return '';
	
}
add_shortcode( 'stripe', 'sc_stripe_shortcode' );

