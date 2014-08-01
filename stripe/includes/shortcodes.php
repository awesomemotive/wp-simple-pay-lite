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
	
	global $sc_options;
	
	STATIC $uid = 1;
	
	extract( shortcode_atts( array(
					'name'                  => ( ! empty( $sc_options['name'] ) ? $sc_options['name'] : get_bloginfo( 'title' ) ),
					'description'           => '',
					'amount'                => '',
					'image_url'             => ( ! empty( $sc_options['image_url'] ) ? $sc_options['image_url'] : '' ),
					'currency'              => ( ! empty( $sc_options['currency'] ) ? $sc_options['currency'] : 'USD' ),
					'checkout_button_label' => ( ! empty( $sc_options['checkout_button_label'] ) ? $sc_options['checkout_button_label'] : '' ),
					'billing'               => ( ! empty( $sc_options['billing'] ) ? 'true' : 'false' ),    // true or false
					'shipping'              => ( ! empty( $sc_options['shipping'] ) ? 'true' : 'false' ),    // true or false
					'payment_button_label'  => ( ! empty( $sc_options['payment_button_label'] ) ? $sc_options['payment_button_label'] : __( 'Pay with Card', 'sc' ) ),
					'enable_remember'       => '',    // true or false
					'success_redirect_url'  => ( ! empty( $sc_options['success_redirect_url'] ) ? $sc_options['success_redirect_url'] : get_permalink() ),
					'failure_redirect_url'  => ( ! empty( $sc_options['failure_redirect_url'] ) ? $sc_options['failure_redirect_url'] : get_permalink() ),
					'prefill_email'         => 'false',
					'verify_zip'            => ( ! empty( $sc_options['verify_zip'] ) ? 'true' : 'false' )
				), $attr, 'stripe' ) );
	
	
	// Check if in test mode or live mode
	if( ! empty( $sc_options['enable_live_key'] ) && $sc_options['enable_live_key'] == 1 ) {
		$data_key = ( ! empty( $sc_options['live_publish_key'] ) ? $sc_options['live_publish_key'] : '' );
		
		if( empty( $sc_options['live_secret_key'] ) ) {
			$data_key = '';
		}
	} else {
		$data_key = ( ! empty( $sc_options['test_publish_key'] ) ? $sc_options['test_publish_key'] : '' );
		
		if( empty( $sc_options['test_secret_key'] ) ) {
			$data_key = '';
		}
	}
	
	if( empty( $data_key ) ) {
		
		if( current_user_can( 'manage_options' ) ) {
			return '<p>' . __( 'You must enter your API keys before the Stripe button will show up here.', 'sc' ) . '</p>';
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
	
	$html = parse_shortcode_content( $content );

	$html .= '<form id="sc_checkout_form_' . $uid . '" method="POST" action="" data-sc-id="' . $uid . '" class="sc-checkout-form">';
	
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
				( ! empty( $shipping ) ? 'data-shipping-address="' . $shipping . '" ' : 'data-shipping-address="false" ' ) .
				( ! empty( $billing ) ? 'data-billing-address="' . $billing . '" ' : 'data-billing-address="false" ' ) .
				'></script>';
	
	$html .= '<input type="hidden" name="sc-name" value="' . esc_attr( $name ) . '" />';
	$html .= '<input type="hidden" name="sc-description" value="' . esc_attr( $description ) . '" />';
	$html .= '<input type="hidden" name="sc-amount" class="sc_amount" value="' . esc_attr( $amount ) . '" />';
	$html .= '<input type="hidden" name="sc-redirect" value="' . esc_attr( ( ! empty( $success_redirect_url ) ? $success_redirect_url : get_permalink() ) ) . '" />';
	$html .= '<input type="hidden" name="sc-redirect-fail" value="' . esc_attr( ( ! empty( $failure_redirect_url ) ? $failure_redirect_url : get_permalink() ) ) . '" />';
	$html .= '<input type="hidden" name="sc-currency" value="' .esc_attr( $currency ) . '" />';

	$html .= '</form>';

	// Increment static uid counter
	$uid++;
	
	if( ( empty( $amount ) || $amount < 50 ) || ! isset( $amount ) ) {
		
		if( current_user_can( 'manage_options' ) ) {
			return '<p>' . __( 'You must fill in a valid amount for the Stripe button to show up here.', 'sc' ) . '</p>';
		}
		
		return '';
		
	} else if( ! isset( $_GET['payment'] ) ) {
		return $html;
	}
	
	return '';
	
}
add_shortcode( 'stripe', 'sc_stripe_shortcode' );


/**
 * Function to process [stripe_total] shortcode
 * 
 * 
 * @since 1.1.1
 */
function sc_stripe_total( $attr ) {
	
	global $sc_options, $sc_script_options;
	
	extract( shortcode_atts( array(
					'label' => ( ! empty( $sc_options['stripe_total_label'] ) ? $sc_options['stripe_total_label'] : __( 'Total Amount:', 'sc' ) )
				), $attr, 'stripe_total' ) );

	$currency = strtoupper( $sc_script_options['script']['currency'] );
	$stripe_amount = $sc_script_options['script']['amount'];

	$html  = '<div class="sc-form-group">';
	$html .= $label . ' ';
	$html .= '<span class="sc-total-amount">';

	// USD only: Show dollar sign on left of amount.
	if ( $currency === 'USD' ) {
		$html .= '$';
	}

	$html .= sc_stripe_to_formatted_amount( $stripe_amount, $currency );

	// Non-USD: Show currency on right of amount.
	if ( $currency !== 'USD' ) {
		$html .= ' ' . $currency;
	}

	$html .= '</span>'; //sc-total-amount
	$html .= '</div>'; //sc-form-group

	return $html;
}
add_shortcode( 'stripe_total', 'sc_stripe_total' );

/**
 * Function to remove the annoying <br> and <p> tags from wpautop inside the shortcode
 * 
 * Found this function here: http://charlesforster.com/shortcodes-and-line-breaks-in-wordpress/
 * 
 * @since 1.1.1
 */
function parse_shortcode_content( $content ) {
 
    // Parse nested shortcodes and add formatting.
    $content = trim( wpautop( do_shortcode( $content ) ) ); 
 
    // Remove '</p>' from the start of the string.
    if ( substr( $content, 0, 4 ) == '</p>' ) 
        $content = substr( $content, 4 ); 
 
    // Remove '<p>' from the end of the string.
    if ( substr( $content, -3, 3 ) == '<p>' ) 
        $content = substr( $content, 0, -3 ); 
 
    // Remove any instances of '<p></p>'.
    $content = str_replace( array( '<p></p>' ), '', $content ); 
 
    return $content; 
} 