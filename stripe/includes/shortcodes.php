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
	
	global $sc_options, $sc_script_options, $script_vars;
	
	STATIC $uid = 1;
	
	extract( shortcode_atts( array(
					'name'                  => ( ! empty( $sc_options['name'] ) ? $sc_options['name'] : get_bloginfo( 'title' ) ),
					'description'           => '',
					'amount'                => '',
					'image_url'             => '',
					'currency'              => ( ! empty( $sc_options['currency'] ) ? $sc_options['currency'] : 'USD' ),
					'checkout_button_label' => '',
					'billing'               => '',    // true or false
					'shipping'              => '',    // true or false
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
	
	// Save all of our options to an array so others can run them through a filter if they need to
	$sc_script_options = array( 
		'script' => array(
			'key'                  => $data_key,
			'name'                 => $name,
			'description'          => $description,
			'amount'               => $amount,
			'image'                => $image_url,
			'currency'             => strtoupper( $currency ),
			'panel-label'          => $checkout_button_label,
			'billing-address'      => $billing,
			'shipping-address'     => $shipping,
			'label'                => $payment_button_label,
			'allow-remember-me'    => $enable_remember,
			'email'                => $prefill_email,
			'verify_zip'           => $verify_zip
		),
		'other' => array(
			'success-redirect-url' => $success_redirect_url,
			'failure-redirect-url' => $failure_redirect_url
		)
	);
	
	$sc_script_options = apply_filters( 'sc_modify_script_options', $sc_script_options );
	
	// Set our global array based on the uid so we can make sure each button/form is unique
	$script_vars[$uid] = array(
			'key'             => ( ! empty( $sc_script_options['script']['key'] ) ? $sc_script_options['script']['key'] : ( ! empty( $sc_options['key'] ) ? $sc_options['key'] : -1 ) ),
			'name'            => ( ! empty( $sc_script_options['script']['name'] ) ? $sc_script_options['script']['name'] : ( ! empty( $sc_options['name'] ) ? $sc_options['name'] : -1 ) ),
			'description'     => ( ! empty( $sc_script_options['script']['description'] ) ? $sc_script_options['script']['description'] : ( ! empty( $sc_options['description'] ) ? $sc_options['description'] : -1 ) ),
			'amount'          => ( ! empty( $sc_script_options['script']['amount'] ) ? $sc_script_options['script']['amount'] : ( ! empty( $sc_options['amount'] ) ? $sc_options['amount'] : -1 ) ),
			'image'           => ( ! empty( $sc_script_options['script']['image'] ) ? $sc_script_options['script']['image'] : ( ! empty( $sc_options['image_url'] ) ? $sc_options['image_url'] : -1 ) ),
			'currency'        => ( ! empty( $sc_script_options['script']['currency'] ) ? $sc_script_options['script']['currency'] : ( ! empty( $sc_options['currency'] ) ? $sc_options['currency'] : -1 ) ),
			'panelLabel'      => ( ! empty( $sc_script_options['script']['panel-label'] ) ? $sc_script_options['script']['panel-label'] : ( ! empty( $sc_options['checkout_button_label'] ) ? $sc_options['checkout_button_label'] : -1 ) ),
			'billingAddress'  => ( ! empty( $sc_script_options['script']['billing-address'] ) ? $sc_script_options['script']['billing-address'] : ( ! empty( $sc_options['billing'] ) ? $sc_options['billing'] : -1 ) ),
			'shippingAddress' => ( ! empty( $sc_script_options['script']['shipping-address'] ) ? $sc_script_options['script']['shipping-address'] : ( ! empty( $sc_options['shipping'] ) ? $sc_options['shipping'] : -1 ) ),
			'allowRememberMe' => ( ! empty( $sc_script_options['script']['allow-remember-me'] ) ? $sc_script_options['script']['allow-remember-me'] : ( ! empty( $sc_options['enable_remember'] ) ? $sc_options['enable_remember'] : -1 ) ),
			'email'           => ( ! empty( $sc_script_options['script']['email'] ) && ! ( $sc_script_options['script']['email'] === 'false' ) ? $sc_script_options['script']['email'] : -1 ),
			'zipCode'         => ( ! empty( $sc_script_options['script']['verify_zip'] ) && ! ( $sc_script_options['script']['verify_zip'] === 'false' ) ? $sc_script_options['script']['verify_zip'] : -1 )
	);

	// Reference for Stripe's zero-decimal currencies in JS.
	$script_vars['zero_decimal_currencies'] = sc_zero_decimal_currencies();
	
	$name                 = $sc_script_options['script']['name'];
	$description          = $sc_script_options['script']['description'];
	$amount               = $sc_script_options['script']['amount'];
	$success_redirect_url = $sc_script_options['other']['success-redirect-url'];
	$failure_redirect_url = $sc_script_options['other']['failure-redirect-url'];
	$currency             = $sc_script_options['script']['currency'];

	//Add Parsley JS form validation attribute here.
	$html  = '<form id="sc_checkout_form_' . $uid . '" method="POST" action="" data-sc-id="' . $uid . '" class="sc-checkout-form" ';
	$html .= 'data-parsley-validate>';

	$content = parse_shortcode_content( $content );
	
	$html .= apply_filters( 'sc_shortcode_content', $content );
	
	$html .= '<input type="hidden" name="sc-name" value="' . esc_attr( $name ) . '" />';
	$html .= '<input type="hidden" name="sc-description" value="' . esc_attr( $description ) . '" />';
	$html .= '<input type="hidden" name="sc-amount" class="sc_amount" value="" />';
	$html .= '<input type="hidden" name="sc-redirect" value="' . esc_attr( ( ! empty( $success_redirect_url ) ? $success_redirect_url : get_permalink() ) ) . '" />';
	$html .= '<input type="hidden" name="sc-redirect-fail" value="' . esc_attr( ( ! empty( $failure_redirect_url ) ? $failure_redirect_url : get_permalink() ) ) . '" />';
	$html .= '<input type="hidden" name="sc-currency" value="' .esc_attr( $currency ) . '" />';
	$html .= '<input type="hidden" name="stripeToken" value="" class="sc_stripeToken" />';
	$html .= '<input type="hidden" name="stripeEmail" value="" class="sc_stripeEmail" />';
	
	// Add shipping information fields if it is enabled
	if( $shipping === 'true' ) {
		$html .= '<input type="hidden" name="sc-shipping-name" class="sc-shipping-name" value="" />';
		$html .= '<input type="hidden" name="sc-shipping-country" class="sc-shipping-country" value="" />';
		$html .= '<input type="hidden" name="sc-shipping-zip" class="sc-shipping-zip" value="" />';
		$html .= '<input type="hidden" name="sc-shipping-state" class="sc-shipping-state" value="" />';
		$html .= '<input type="hidden" name="sc-shipping-address" class="sc-shipping-address" value="" />';
		$html .= '<input type="hidden" name="sc-shipping-city" class="sc-shipping-city" value="" />';
	}

	$html .= '<button class="sc_checkout stripe-button-el"><span>' . $payment_button_label . '</span></button>';
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
