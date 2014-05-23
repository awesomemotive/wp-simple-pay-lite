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
			require_once( SC_PLUGIN_DIR . 'stripe-php/Stripe.php' );
		}
		
		global $sc_options;
		
		// Set redirect
		$redirect     = $_POST['sc-redirect'];
		
		// Get the credit card details submitted by the form
		$token       = $_POST['stripeToken'];
		$amount      = $_POST['sc-amount'];
		$description = $_POST['sc-description'];
		$name        = $_POST['sc-name'];
		$currency    = $_POST['sc-currency'];
		
		if( ! empty( $sc_options['enable_live_key'] ) && $sc_options['enable_live_key'] == 1 ) {
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
			
			$redirect = add_query_arg( array( 'payment' => 'success', 'amount' => $amount ), apply_filters( 'sc_redirect', $redirect ) );
			
			$failed = false;
			
			
		} catch(Stripe_CardError $e) {
		  
			$redirect = add_query_arg( 'payment', 'failed', get_permalink() );
			
			$failed = true;
		}
		
		unset( $_POST['stripeToken'] );
		
		
		if( ! $failed ) {

			// Update our payment details option so we can show it at the top of the content
			$sc_payment_details['show']        = 1;
			$sc_payment_details['amount']      = $amount;
			$sc_payment_details['name']        = $name;
			$sc_payment_details['description'] = $description;
			$sc_payment_details['currency']    = $currency;

			update_option( 'sc_payment_details', apply_filters( 'sc_payment_details', $sc_payment_details ) );
		}
		
		do_action( 'sc_redirect_before' );
		
		wp_redirect( $redirect );
		
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
	
	$sc_payment_details = get_option( 'sc_payment_details' );
	$payment_details_html = '';
	
	if( ! empty( $sc_payment_details ) ) {
		if( $sc_payment_details['show'] != false ) {
			$before_payment_details_html = '<div class="sc-payment-details-wrap">' . "\n";

			$payment_details_html .= '<p>' . __( 'Congratulations. Your payment went through!', 'sc' ) . '</p>' . "\n";
			$payment_details_html .= '<p>' . __( 'Here\'s what you bought:', 'sc' ) . '</p>' . "\n";
			$payment_details_html .= ( ! empty( $sc_payment_details['description'] ) ? $sc_payment_details['description'] . '<br/>' . "\n" : '' );
			$payment_details_html .= ( ! empty( $sc_payment_details['name'] ) ? 'From: ' . $sc_payment_details['name'] . '<br/>' . "\n" : '' );
			$payment_details_html .= ( ! empty( $sc_payment_details['amount'] ) ? '<br/><strong>' . __( 'Total Paid: ', 'sc' ) . sc_convert_amount( $sc_payment_details['amount'], $sc_payment_details['currency'] ) . ' ' . $sc_payment_details['currency'] . '</strong>' . "\n" : '' );
			
			$after_payment_details_html = '</div>' . "\n";
			
			$before_payment_details_html = apply_filters( 'sc_before_payment_details_html', $before_payment_details_html );
			$payment_details_html        = apply_filters( 'sc_payment_details_html', $payment_details_html, $sc_payment_details );
			$after_payment_details_html  = apply_filters( 'sc_after_payment_details_html', $after_payment_details_html );
			
			$content = $before_payment_details_html . $payment_details_html . $after_payment_details_html . $content;
			
			delete_option( 'sc_payment_details' );
		}
	}
	
	return $content;
}
add_filter( 'the_content', 'sc_show_payment_details' );

/*
 * Function to convert the amount passed from cents to dollars
 * 
 * @since 1.1.0
 */
function sc_convert_amount( $amount, $currency ) {
	
	$zero_based = array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VUV', 'XAF', 'XOF', 'XPF' );
	
	
	if( in_array( $currency, $zero_based ) ) {
		return $amount;
	}
	
	return number_format( ( $amount / 100 ), 2 );
}

/**
 * Return the admin help page URL
 * 
 * @since 1.1.1
 */
function sc_help_url( $tab = '', $string = '' ) {
	
	if( empty( $tab ) ) {
		$tab = 'base';
	}
	
	$args = array(
		'page' => 'stripe-checkout_help',
		'tab'  => $tab
	);
	
	if( empty( $string ) ) {
		$string = __( 'Help', 'sc' );
	}
	
	//$args = apply_filters( 'sc_admin_help_query_args', $args );
	
	return '<a href="' . add_query_arg( $args, admin_url( 'admin.php' ) ) . '">' . $string . '</a>';
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
		$meta['Shipping Name']    = $_POST['sc-shipping-name'];
		$meta['Shipping Address'] = $_POST['sc-shipping-address'];
		$meta['Shipping City']    = $_POST['sc-shipping-city'];
		$meta['Shipping State']   = $_POST['sc-shipping-state'];
		$meta['Shipping Zip']     = $_POST['sc-shipping-zip'];
		$meta['Shipping Country'] = $_POST['sc-shipping-country'];
	}
	
	return $meta;
}
add_filter( 'sc_meta_values', 'sc_add_shipping_meta' );