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
	
	
	if( empty( $sc_options['enable_test_key'] ) ) {
		$data_key = ( ! empty( $sc_options['live_publish_key'] ) ? $sc_options['live_publish_key'] : '' );
	} else {
		$data_key = ( ! empty( $sc_options['test_publish_key'] ) ? $sc_options['test_publish_key'] : '' );
	}
	
	$options = array( 
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
	
	$options = apply_filters( 'sc_modify_script_options', $options );
	
	$script = sc_get_script_options_string( $options );
	
	if( ( empty( $options['script']['amount'] ) || $options['script']['amount'] < 50 ) && ! isset( $options['script']['amount'] ) ) {
		return '';
	}
	
	if( ! isset( $_GET['payment'] ) ) { 
		
		$form        = '';
		$form_open   = '';
		$form_script = '';
		$form_fields = '';
		$form_close  = '';
		
		$form .= apply_filters( 'sc_form_open', $form_open );
		$form .= apply_filters( 'sc_form_script', $form_script, $options );
		$form .= apply_filters( 'sc_form_fields', $form_fields, $options );
		$form .= apply_filters( 'sc_form_close', $form_close );
		
		
		return $form;
	}
	
	return '';
	
}
add_shortcode( 'stripe', 'sc_stripe_shortcode' );


function sc_get_script_options_string( $script_options ) {
	
	$string = '';
	
	foreach( $script_options['script'] as $k => $v ) {
		if( ! empty( $v ) ) {
			$string .= 'data-' . $k . '="' . $v . '" ';
		}
	}
	
	return $string;
}

