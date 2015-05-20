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

// Check for and include Stripe PHP (v2.0.0 +) library here until we refactor.
// Note plugins using Stripe PHP v1.18.0 or before can run alongside this plugin since the class namespace was updated.
// Was 'Stripe', now 'Stripe\Stripe'.

if ( ! class_exists( 'Stripe\Stripe' ) ) {
	require_once( SC_PATH . 'libraries/stripe-php/init.php' );
}

/**
 * Common method to set Stripe API key from options.
 *
 * @since 1.2.4
 */
function sc_set_stripe_key( $test_mode = 'false' ) {
	global $sc_options;
	$key = '';
	
	// Check first if in live or test mode.
	if( ! empty( $sc_options['enable_live_key'] ) && $sc_options['enable_live_key'] == 1 && $test_mode != 'true' ) {
		$key = ( ! empty( $sc_options['live_secret_key'] ) ? $sc_options['live_secret_key'] : '' );
	} else {
		$key = ( ! empty( $sc_options['test_secret_key'] ) ? $sc_options['test_secret_key'] : '' );
	}

	\Stripe\Stripe::setApiKey( $key );
}

/**
 * Function that will actually charge the customers credit card
 *
 * @since 1.0.0
 */
function sc_charge_card() {

	$redirect      = $_POST['sc-redirect'];
	$fail_redirect = $_POST['sc-redirect-fail'];

	// Get the credit card details submitted by the form
	$token             = $_POST['stripeToken'];
	$amount            = $_POST['sc-amount'];
	$description       = $_POST['sc-description'];
	$store_name        = $_POST['sc-name'];
	$currency          = $_POST['sc-currency'];
	$test_mode         = ( isset( $_POST['sc_test_mode'] ) ? $_POST['sc_test_mode'] : 'false' );
	$details_placement = $_POST['sc-details-placement'];

	$charge = array();
	$query_args = array();

	$meta = array();
	$meta = apply_filters( 'sc_meta_values', $meta );

	sc_set_stripe_key( $test_mode );

	// Create new customer
	$new_customer = \Stripe\Customer::create( array(
		'email' => $_POST['stripeEmail'],
		'card'  => $token
	));

	// Create the charge on Stripe's servers - this will charge the user's default card
	try {
		$charge = \Stripe\Charge::create( array(
				'amount'      => $amount, // amount in cents, again
				'currency'    => $currency,
				'customer'    => $new_customer['id'],
				'description' => $description,
				'metadata'    => $meta
			)
		);

		// Add Stripe charge ID to querystring.
		$query_args = array( 'charge' => $charge->id, 'store_name' => urlencode( $store_name ) );

		$failed = false;

	} catch( \Stripe\Error\Card $e ) {

		// Catch Stripe errors
		$redirect = $fail_redirect;

		$e = $e->getJsonBody();

		// Add failure indicator to querystring.
		$query_args = array( 'charge' => $e['error']['charge'], 'charge_failed' => true );

		$failed = true;
	}

	unset( $_POST['stripeToken'] );

	do_action( 'sc_redirect_before' );

	if( $test_mode == 'true' ) {
		$query_args['test_mode'] = 'true';
	}
	
	if ( 'below' == $details_placement ) {
		$query_args['details_placement'] = $details_placement;
	}

	wp_redirect( esc_url_raw( add_query_arg( $query_args, apply_filters( 'sc_redirect', $redirect, $failed ) ) ) );

	do_action( 'sc_redirect_after' );

	exit;
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
	
	
	if( in_the_loop() && is_main_query() ) {
		global $sc_options;

		$html = '';

		$test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : 'false' );
		
		$details_placement = ( isset( $_GET['details_placement'] ) ? $_GET['details_placement'] : 'above' );
		
		// Since this is a GET query arg I reset it here in case someone tries to submit it again with their own string written in the URL. 
		// This helps ensure it can only be set to below or above.
		$details_placement = ( $details_placement == 'below' ? 'below' : 'above' );
		
		$is_above = ( $details_placement == 'below' ? 0 : 1 );

		sc_set_stripe_key( $test_mode );

		// Successful charge output.
		if ( isset( $_GET['charge'] ) && !isset( $_GET['charge_failed'] ) ) {

			if ( empty( $sc_options['disable_success_message'] ) ) {

				$charge_id = esc_html( $_GET['charge'] );

				// https://stripe.com/docs/api/php#charges
				$charge_response = \Stripe\Charge::retrieve( $charge_id );

				$html = '<div class="sc-payment-details-wrap">' . "\n";

				$html .= '<p>' . __( 'Congratulations. Your payment went through!', 'sc' ) . '</p>' . "\n";
				$html .= '<p>' . "\n";

				if ( ! empty( $charge_response->description ) ) {
					$html .= __( "Here's what you purchased:", 'sc' ) . '<br/>' . "\n";
					$html .= $charge_response->description . '<br/>' . "\n";
				}

				if ( isset( $_GET['store_name'] ) && ! empty( $_GET['store_name'] ) ) {
					$html .= 'From: ' . esc_html( $_GET['store_name'] ) . '<br/>' . "\n";
				}

				$html .= '<br/>' . "\n";
				$html .= '<strong>' . __( 'Total Paid: ', 'sc' ) . sc_stripe_to_formatted_amount( $charge_response->amount, $charge_response->currency ) . ' ' .
						 strtoupper( $charge_response->currency ) . '</strong>' . "\n";

				$html .= '</p>' . "\n";

				$html .= '<p>' . sprintf( __( 'Your transaction ID is: %s', 'sc' ), $charge_id ) . '</p>' . "\n";

				$html .= '</div>' . "\n";
				
				if ( $is_above ) {
					return apply_filters( 'sc_payment_details', $html, $charge_response ) . $content;
				} else {
					return $content . apply_filters( 'sc_payment_details', $html, $charge_response );
				}

			} else {

				return $content;
			}

		} elseif ( isset( $_GET['charge_failed'] ) ) {
			
			$charge_id = esc_html( $_GET['charge'] );
			
			$charge = \Stripe\Charge::retrieve( $charge_id );
			
			// LITE ONLY: Payment details error included in payment details function.

			$html  = '<div class="sc-payment-details-wrap sc-payment-details-error">' . "\n";
			$html .= '<p>' . __( 'Sorry, but there has been an error processing your payment.', 'sc' ) . '</p>' . "\n";
			$html .= '<p>' . $charge->failure_message . '</p>';
			$html .= '</div>' . "\n";
			
			if ( $is_above ) {
				return apply_filters( 'sc_payment_details_error', $html ) . $content;
			} else {
				return $content . apply_filters( 'sc_payment_details_error', $html );
			}
		}
	}

	return $content;
}
add_filter( 'the_content', 'sc_show_payment_details', 11 );

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

	$url = esc_url( add_query_arg( array(
		'utm_source'   => $source,
		'utm_medium'   => $medium,
		'utm_campaign' => $campaign
	), $base_url ) );

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

/**
 * Filters the content to remove any extra paragraph or break tags
 * caused by shortcodes.
 *
 * @since 1.0.0
 *
 * @param string $content  String of HTML content.
 * @return string $content Amended string of HTML content.
 * 
 * REF: https://thomasgriffin.io/remove-empty-paragraph-tags-shortcodes-wordpress/
 */
function sc_shortcode_fix( $content ) {

    $array = array(
        '<p>['    => '[',
        ']</p>'   => ']',
        ']<br />' => ']'
    );
	
    return strtr( $content, $array );
}
add_filter( 'the_content', 'sc_shortcode_fix' );
