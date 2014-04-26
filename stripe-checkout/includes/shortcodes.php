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
					'name'                  => get_bloginfo( 'title' ),
					'description'           => '',
					'amount'                => '',
					'image_url'             => '',
					'currency'              => 'USD',
					'checkout_button_label' => '',
					'billing'               => '',    // true or false
					'shipping'              => '',    // true or false
					'payment_button_label'  => '',
					'enable_remember'       => '',    // true or false
					'success_redirect_url'  => get_permalink()
				), $attr, 'stripe' ) );
	
	$attr = apply_filters( 'shortcode_atts_stripe', $attr );
	
	
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
	// TODO change these to key => value pairs as an array to pass?
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
	$script_options .= ( ! empty( $enable_remember ) ? 'data-allow-remember-me="' . esc_attr( $enable_remember ) . '" ' : '' );
	
	// Add in the script options as an argument to pass to our filter
	$attr['script_options'] = $script_options;
	
	$form_attr = apply_filters( 'sc_form_attr', $attr );
	
	if( ( empty( $amount ) || $amount < 50 ) && ! isset( $form_attr['amount'] ) )
		return '';
	
	if( ! isset( $_GET['payment'] ) ) { 
		
		$form        = '';
		$form_open   = '';
		$form_script = '';
		$form_fields = '';
		$form_close  = '';
		
		$form .= apply_filters( 'sc_form_open', $form_open );
		$form .= apply_filters( 'sc_form_script', $form_script );
		$form .= apply_filters( 'sc_form_fields', $form_fields );
		$form .= apply_filters( 'sc_form_close', $form_close );
		
		
		return $form;
	}
	
	return '';
	
}
add_shortcode( 'stripe', 'sc_stripe_shortcode' );

