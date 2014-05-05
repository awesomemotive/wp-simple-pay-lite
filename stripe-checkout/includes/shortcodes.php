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
	
	global $sc_options, $sc_script_options;
	
	extract( shortcode_atts( array(
					'name'                  => ( ! empty( $sc_options['name'] ) ? $sc_options['name'] : get_bloginfo( 'title' ) ),
					'description'           => '',
					'amount'                => '',
					'image_url'             => '',
					'currency'              => ( ! empty( $sc_options['currency'] ) ? $sc_options['currency'] : 'USD' ),
					'checkout_button_label' => '',
					'billing'               => '',    // true or false
					'shipping'              => '',    // true or false
					'payment_button_label'  => '',
					'enable_remember'       => '',    // true or false
					'success_redirect_url'  => ( ! empty( $sc_options['success_redirect_url'] ) ? $sc_options['success_redirect_url'] : get_permalink() )
				), $attr, 'stripe' ) );
	
	
	// Check if in test mode or live mode
	if( empty( $sc_options['enable_test_key'] ) ) {
		$data_key = ( ! empty( $sc_options['live_publish_key'] ) ? $sc_options['live_publish_key'] : '' );
	} else {
		$data_key = ( ! empty( $sc_options['test_publish_key'] ) ? $sc_options['test_publish_key'] : '' );
	}
	
	// Save all of our options to an array so others can run them through a filter if they need to
	$sc_script_options = array( 
		'script' => array(
			'key'                  => $data_key,
			'name'                 => $name,
			'description'          => $description,
			'amount'               => $amount,
			'image'                => $image_url,
			'currency'             => $currency,
			'panel-label'          => $checkout_button_label,
			'billing-address'      => $billing,
			'shipping-address'     => $shipping,
			'label'                => $payment_button_label,
			'allow-remember-me'    => $enable_remember
		),
		'other' => array(
			'success-redirect-url' => $success_redirect_url
		)
	);
	
	$sc_script_options = apply_filters( 'sc_modify_script_options', $sc_script_options );
	
	$script = sc_get_script_options_string( $sc_script_options );
	
	
	
	//if( ! isset( $_GET['payment'] ) ) { 
		
		$form        = '';
		$form_open   = '';
		$form_script = '';
		$form_fields = '';
		$form_close  = '';
		
		// We run these all through filters so anyone can easily modify any part of the form
		$form .= apply_filters( 'sc_form_open', $form_open );
		$form .= apply_filters( 'sc_form_script', $form_script, $sc_script_options );
		$form .= apply_filters( 'sc_form_fields', $form_fields, $sc_script_options );
		$form .= apply_filters( 'sc_form_close', $form_close );
		
		//return $form;
	//}
	
	if( ( empty( $sc_script_options['script']['amount'] ) || $sc_script_options['script']['amount'] < 50 ) || ! isset( $sc_script_options['script']['amount'] ) ) {
		return '';
	} else if( ! isset( $_GET['payment'] ) ) {
		return $form;
	}
	
	return '';
	
}
add_shortcode( 'stripe', 'sc_stripe_shortcode' );

/*
 * Function to return the script options as a string to attach to the <script> tag if we need to
 * 
 * @since 1.1.1
 */
function sc_get_script_options_string( $script_options ) {
	
	$string = '';
	
	foreach( $script_options['script'] as $k => $v ) {
		if( ! empty( $v ) ) {
			$string .= 'data-' . $k . '="' . $v . '" ';
		}
	}
	
	return $string;
}
