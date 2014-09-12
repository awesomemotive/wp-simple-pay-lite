<?php

/**
 * Misc plugin functions
 * 
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function that will actually charge the customers credit card
 * 
 * @since 1.0.0
 */
function sc_charge_card() {
	if( isset( $_POST['stripeToken'] ) ) {
		
		if( ! class_exists( 'Stripe' ) ) {
			require_once( SC_PLUGIN_DIR . 'libraries/stripe-php/Stripe.php' );
		}
		
		global $sc_options;
		
		// Set redirect
		$redirect      = $_POST['sc-redirect'];
		$fail_redirect = $_POST['sc-redirect-fail'];
		
		// Get the credit card details submitted by the form
		$token       = $_POST['stripeToken'];
		$amount      = $_POST['sc-amount'];
		$description = $_POST['sc-description'];
		$name        = $_POST['sc-name'];
		$currency    = $_POST['sc-currency'];
		
		$test_mode   = ( isset( $_POST['sc_test_mode'] ) ? 'true' : 'false' );
		
		if( ! empty( $sc_options['enable_live_key'] ) && $sc_options['enable_live_key'] == 1 && $test_mode != 'true' ) {
			$key = ( ! empty( $sc_options['live_secret_key'] ) ? $sc_options['live_secret_key'] : '' );
		} else {
			$key = ( ! empty( $sc_options['test_secret_key'] ) ? $sc_options['test_secret_key'] : '' );
		}
		
		$meta = array();
		
		$meta = apply_filters( 'sc_meta_values', $meta );
		
		// Set your secret key: remember to change this to your live secret key in production
		Stripe::setApiKey( $key );
		
		// Create new customer 
		$new_customer = Stripe_Customer::create( array( 
				'email' => $_POST['stripeEmail'],
				'card'  => $token
			));
		
		$amount = apply_filters( 'sc_charge_amount', $amount );
		
		// Create the charge on Stripe's servers - this will charge the user's default card
		try {
			$charge = Stripe_Charge::create( array(
					'amount'      => $amount, // amount in cents, again
					'currency'    => $currency,
					'customer'    => $new_customer['id'],
					'description' => $description,
					'metadata'  => $meta
				)
			);
			
			$query_args = array( 'payment' => 'success', 'amount' => $amount );
			
			$failed = false;
			
			
		} catch(Stripe_CardError $e) {
		  
			$redirect = $fail_redirect;
			
			$query_args = array( 'payment' => 'failed' );
			
			$failed = true;
		}
		
		unset( $_POST['stripeToken'] );
		
		
		if( ! $failed ) {

			// Update our payment details option so we can show it at the top of the content
			$sc_payment_details['show']        = true;
			$sc_payment_details['amount']      = $amount;
			$sc_payment_details['name']        = $name;
			$sc_payment_details['description'] = $description;
			$sc_payment_details['currency']    = $currency;

			Stripe_Checkout::get_instance()->session->set( 'sc_payment_details', $sc_payment_details );		
		} else {
			$sc_payment_details['show'] = true;
			$sc_payment_details['fail'] = true;
			
			Stripe_Checkout::get_instance()->session->set( 'sc_payment_details', $sc_payment_details );	
		}
		
		do_action( 'sc_redirect_before' );
		
		wp_redirect( add_query_arg( $query_args, apply_filters( 'sc_redirect', $redirect, $failed ) ) );
		
		do_action( 'sc_redirect_after' );
		
		exit;
	}
}

// We only want to run the charge if the Token is set
if( isset( $_POST['stripeToken'] ) ) {
	add_action( 'init', 'sc_charge_card' );
}

/*
 * Function to show the payment details after the purchase
 * 
 * @since 1.0.0
 */
function sc_show_payment_details( $content ) {
	
	$sc_payment_details = Stripe_Checkout::get_instance()->session->get( 'sc_payment_details' );
	
	$payment_details_html = '';
	
	if( ! empty( $sc_payment_details ) ) {
		if( $sc_payment_details['show'] != false ) {
			if( empty( $sc_payment_details['fail'] ) ) {
				$before_payment_details_html = '<div class="sc-payment-details-wrap">' . "\n";

				$payment_details_html .= '<p>' . __( 'Congratulations. Your payment went through!', 'sc' ) . '</p>' . "\n";
				$payment_details_html .= '<p>' . __( 'Here\'s what you bought:', 'sc' ) . '</p>' . "\n";

				if ( ! empty( $sc_payment_details['description'] ) ) {
					$payment_details_html .= $sc_payment_details['description'] . '<br/>' . "\n";
				}
				if ( ! empty( $sc_payment_details['name'] ) ) {
					$payment_details_html .= 'From: ' . $sc_payment_details['name'] . '<br/>' . "\n";
				}
				if ( ! empty( $sc_payment_details['amount'] ) ) {
					$payment_details_html .=  '<br/>' . "\n";
					$payment_details_html .=  '<strong>' . __( 'Total Paid: ', 'sc' );
					$payment_details_html .=  sc_stripe_to_formatted_amount( $sc_payment_details['amount'], $sc_payment_details['currency'] ) . "\n";
					$payment_details_html .=  ' ' . $sc_payment_details['currency'] . '</strong>' . "\n";
				}

				$after_payment_details_html = '</div>' . "\n";

				$before_payment_details_html = apply_filters( 'sc_before_payment_details_html', $before_payment_details_html );
				$payment_details_html        = apply_filters( 'sc_payment_details_html', $payment_details_html, $sc_payment_details );
				$after_payment_details_html  = apply_filters( 'sc_after_payment_details_html', $after_payment_details_html );

				$content = $before_payment_details_html . $payment_details_html . $after_payment_details_html . $content;

				$sc_payment_details['show'] = false;

				Stripe_Checkout::get_instance()->session->set( 'sc_payment_details', $sc_payment_details );
			} else {
				$before_payment_details_html = '<div class="sc-payment-details-wrap sc-payment-details-error">' . "\n";

				$payment_details_html .= '<p>' . __( 'Sorry, but for some reason your card was declined and your payment did not complete.', 'sc' ) . '</p>' . "\n";
				
				$after_payment_details_html = '</div>' . "\n";
				
				$before_payment_details_html = apply_filters( 'sc_before_payment_details_error_html', $before_payment_details_html );
				$payment_details_html        = apply_filters( 'sc_payment_details_error_html', $payment_details_html, $sc_payment_details );
				$after_payment_details_html  = apply_filters( 'sc_after_payment_details_error_html', $after_payment_details_html );

				$content = $before_payment_details_html . $payment_details_html . $after_payment_details_html . $content;

				$sc_payment_details['show'] = false;
				
				Stripe_Checkout::get_instance()->session->set( 'sc_payment_details', $sc_payment_details );
			}
		}
	}
	
	return $content;
}
add_filter( 'the_content', 'sc_show_payment_details' );

/**
 * Convert amount opposite of sc_decimal_to_stripe_amount().
 * Needed for Stripe's calculated amount. Don't convert if using zero-decimal currency.
 *
 * @since 1.1.1
 */
function sc_stripe_to_decimal_amount( $amount, $currency ) {

	if ( !sc_is_zero_decimal_currency( $currency) ) {
		// Always round to 2 decimals.
		$amount = round( $amount / 100, 2 );
	}

	return $amount;
}

/**
 * Format Stripe (non-decimal) amount for screen.
 *
 * @since 1.1.1
 */
function sc_stripe_to_formatted_amount( $amount, $currency ) {

	// First convert to decimal if needed.
	$amount = sc_stripe_to_decimal_amount( $amount, $currency );

	// Use 2 decimals unless zero-decimal currency.
	$formatted_amount = number_format_i18n( $amount, ( sc_is_zero_decimal_currency( $currency ) ? 0 : 2 ) );

	return $formatted_amount;
}

/**
 * List of zero-decimal currencies according to Stripe.
 * Needed for PHP and JS.
 * See: https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
 *
 * @since 1.1.1
 */
function sc_zero_decimal_currencies() {
	return array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VUV', 'XAF', 'XOF', 'XPF' );
}

/**
 * Check if currency is zero-decimal.
 *
 * @since 1.1.1
 */
function sc_is_zero_decimal_currency( $currency ) {
	return in_array( strtoupper( $currency ), sc_zero_decimal_currencies() );
}

/**
 * Check if the [stripe] shortcode exists on this page
 * 
 * @since 1.0.0
 */
function sc_has_shortcode() {
	global $post;
	
	// Currently ( 5/8/2014 ) the has_shortcode() function will not find a 
	// nested shortcode. This seems to do the trick currently, will switch if 
	// has_shortcode() gets updated. -NY
	if ( strpos( $post->post_content, '[stripe' ) !== false ) {
		return true;
	}
	
	return false;
}

/**
 * Since Stripe does not deal with Shipping information we will add it as meta to pass to the Dashboard
 * 
 * @since 1.1.1
 */
function sc_add_shipping_meta( $meta ) {
	if( isset( $_POST['sc-shipping-name'] ) ) {
		
		// Add Shipping Name as an item
		$meta['Shipping Name']    = $_POST['sc-shipping-name'];
		
		// Show address on two lines: Address 1 and Address 2 in Stripe dashboard -> payments 
		$meta['Shipping Address 1'] = $_POST['sc-shipping-address'];
		$meta['Shipping Address 2'] = $_POST['sc-shipping-zip'] . ', ' . $_POST['sc-shipping-city'] . ', ' . $_POST['sc-shipping-state'] . ', ' . $_POST['sc-shipping-country'];
	}
	
	return $meta;
}
add_filter( 'sc_meta_values', 'sc_add_shipping_meta' );

/**
 * Google Analytics campaign URL.
 *
 * @since   1.1.1
 *
 * @param   string  $base_url Plain URL to navigate to
 * @param   string  $source   GA "source" tracking value
 * @param   string  $medium   GA "medium" tracking value
 * @param   string  $campaign GA "campaign" tracking value
 * @return  string  $url      Full Google Analytics campaign URL
 */
function sc_ga_campaign_url( $base_url, $source, $medium, $campaign ) {
	// $medium examples: 'sidebar_link', 'banner_image'

	$url = add_query_arg( array(
		'utm_source'   => $source,
		'utm_medium'   => $medium,
		'utm_campaign' => $campaign
	), $base_url );

	return $url;
}

/**
 * Disables opengraph tags to avoid conflicts with WP SEO by Yoast
 *
 * @since 1.2.0
 */
function sc_disable_seo_og() {
	
	$sc_payment_details = Stripe_Checkout::get_instance()->session->get( 'sc_payment_details' );
	
	if ( $sc_payment_details['show'] == true ) {
		remove_action( 'template_redirect', 'wpseo_frontend_head_init', 999 );
	}
}
