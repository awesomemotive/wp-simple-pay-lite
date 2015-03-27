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
 * Function to process the [stripe] shortcode
 * 
 * @since 1.0.0
 */
function sc_stripe_shortcode( $attr, $content = null ) {
	
	//global $sc_options;
	
	global $sc_options;
	
	STATIC $uid = 1;
	
	extract( shortcode_atts( array(
					'name'                  => ( $sc_options->get_setting_value( 'name' ) !== null ? $sc_options->get_setting_value( 'name' ) : get_bloginfo( 'title' ) ),
					'description'           => '',
					'amount'                => '',
					'image_url'             => ( $sc_options->get_setting_value( 'image_url' ) !== null ? $sc_options->get_setting_value( 'image_url' ) : '' ),
					'currency'              => ( $sc_options->get_setting_value( 'currency' ) !== null ? $sc_options->get_setting_value( 'currency' ) : 'USD' ),
					'checkout_button_label' => ( $sc_options->get_setting_value( 'checkout_button_label' ) !== null ? $sc_options->get_setting_value( 'checkout_button_label' ) : '' ),
					'billing'               => ( $sc_options->get_setting_value( 'billing' ) !== null ? 'true' : 'false' ),    // true or false
					'payment_button_label'  => ( $sc_options->get_setting_value( 'payment_button_label' ) !== null ? $sc_options->get_setting_value( 'payment_button_label' ) : __( 'Pay with Card', 'sc' ) ),
					'enable_remember'       => ( $sc_options->get_setting_value( 'enable_remember' ) !== null ? 'true' : 'false' ),    // true or false
					'success_redirect_url'  => ( $sc_options->get_setting_value( 'success_redirect_url' ) !== null ? $sc_options->get_setting_value( 'success_redirect_url' ) : get_permalink() ),
					'failure_redirect_url'  => ( $sc_options->get_setting_value( 'failure_redirect_url' ) !== null ? $sc_options->get_setting_value( 'failure_redirect_url' ) : get_permalink() ),
					'prefill_email'         => 'false',
					'verify_zip'            => ( $sc_options->get_setting_value( 'verify_zip' ) !== null ? 'true' : 'false' ),
					'test_mode'             => 'false'
				), $attr, 'stripe' ) );
	
	
	// Check if in test mode or live mode
	if( $sc_options->get_setting_value( 'enable_live_key' ) == 0 || $test_mode == 'true' ) {
		// Test mode
		$data_key = ( $sc_options->get_setting_value( 'test_publish_key' ) !== null ? $sc_options->get_setting_value( 'test_publish_key' ) : '' );
		
		if( null === $sc_options->get_setting_value( 'test_secret_key' ) ) {
			$data_key = '';
		}
	} else {
		// Live mode
		$data_key = ( $sc_options->get_setting_value( 'live_publish_key' ) !== null ? $sc_options->get_setting_value( 'live_publish_key' ) : '' );
		
		if( null === $sc_options->get_setting_value( 'live_secret_key' ) ) {
			$data_key = '';
		}
	}
	
	if( empty( $data_key ) ) {
		
		if( current_user_can( 'manage_options' ) ) {
			return '<h6>' . __( 'You must enter your API keys before the Stripe button will show up here.', 'sc' ) . '</h6>';
		}
		
		return '';
	}
	
	if( ! empty( $prefill_email ) && $prefill_email !== 'false' ) {
		// Get current logged in user email
		if( is_user_logged_in() ) {
			$prefill_email = get_userdata( get_current_user_id() )->user_email;
		} else { 
			$prefill_email = 'false';
		}
	}

	$html  = '<form id="sc_checkout_form_' . $uid . '" method="POST" action="" data-sc-id="' . $uid . '" class="sc-checkout-form">';
	
	$html .= '<script
				src="https://checkout.stripe.com/checkout.js" class="stripe-button"
				data-key="' . $data_key . '" ' .
				( ! empty( $image_url ) ? 'data-image="' . $image_url . '" ' : '' ) . 
				( ! empty( $name ) ? 'data-name="' . $name . '" ' : '' ) .
				( ! empty( $description ) ? 'data-description="' . $description . '" ' : '' ) .
				( ! empty( $amount ) ? 'data-amount="' . $amount . '" ' : '' ) .
				( ! empty( $currency ) ? 'data-currency="' . $currency . '" ' : '' ) .
				( ! empty( $checkout_button_label ) ? 'data-panel-label="' . $checkout_button_label . '" ' : '' ) .
				( ! empty( $verify_zip ) ? 'data-zip-code="' . $verify_zip . '" ' : '' ) .
				( ! empty( $prefill_email ) && 'false' != $prefill_email ? 'data-email="' . $prefill_email . '" ' : '' ) .
				( ! empty( $payment_button_label ) ? 'data-label="' . $payment_button_label . '" ' : '' ) .
				( ! empty( $enable_remember ) ? 'data-allow-remember-me="' . $enable_remember . '" ' : 'data-allow-remember-me="true" ' ) .
				( ! empty( $billing ) ? 'data-billing-address="' . $billing . '" ' : 'data-billing-address="false" ' ) .
				'></script>';
	
	$html .= '<input type="hidden" name="sc-name" value="' . esc_attr( $name ) . '" />';
	$html .= '<input type="hidden" name="sc-description" value="' . esc_attr( $description ) . '" />';
	$html .= '<input type="hidden" name="sc-amount" class="sc_amount" value="' . esc_attr( $amount ) . '" />';
	$html .= '<input type="hidden" name="sc-redirect" value="' . esc_attr( ( ! empty( $success_redirect_url ) ? $success_redirect_url : get_permalink() ) ) . '" />';
	$html .= '<input type="hidden" name="sc-redirect-fail" value="' . esc_attr( ( ! empty( $failure_redirect_url ) ? $failure_redirect_url : get_permalink() ) ) . '" />';
	$html .= '<input type="hidden" name="sc-currency" value="' .esc_attr( $currency ) . '" />';
	
	if( $test_mode == 'true' ) {
		$html .= '<input type="hidden" name="sc_test_mode" value="true" />';
	}
	
	// Add a filter here to allow developers to hook into the form
	$filter_html = '';
	$html .= apply_filters( 'sc_before_payment_button', $filter_html );

	$html .= '</form>';

	// Increment static uid counter
	$uid++;

	//Stripe minimum amount allowed.
	$stripe_minimum_amount = 50;

	if( ( empty( $amount ) || $amount < $stripe_minimum_amount ) || ! isset( $amount ) ) {

		if( current_user_can( 'manage_options' ) ) {
			$html =  '<h6>';
			$html .= __( 'Stripe checkout requires an amount of ', 'sc' ) . $stripe_minimum_amount;
			$html .= ' (' . Stripe_Checkout_Misc::to_formatted_amount( $stripe_minimum_amount, $currency ) . ' ' . $currency . ')';
			$html .= __( ' or larger.', 'sc' );
			$html .= '</h6>';

			return $html;
		}
		
		return '';
		
	} else if( ! isset( $_GET['charge'] ) ) {
		return $html;
	}
	
	return '';
	
}
add_shortcode( 'stripe', 'sc_stripe_shortcode' );
