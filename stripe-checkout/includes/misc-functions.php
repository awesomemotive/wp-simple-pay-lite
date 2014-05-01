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
	
		if( empty( $sc_options['enable_test_key'] ) ) {
			$key = ( ! empty( $sc_options['live_secret_key'] ) ? $sc_options['live_secret_key'] : '' );
		} else {
			$key = ( ! empty( $sc_options['test_secret_key'] ) ? $sc_options['test_secret_key'] : '' );
		}

		// Set your secret key: remember to change this to your live secret key in production
		Stripe::setApiKey( $key );
		
		// Create new customer 
		$new_customer = Stripe_Customer::create( array( 
				'email' => $_POST['stripeEmail'],
				'card'  => $token
			));
			
		// Create the charge on Stripe's servers - this will charge the user's default card
		try {
			$charge = Stripe_Charge::create( array(
					'amount'      => $amount, // amount in cents, again
					'currency'    => $currency,
					'customer'    => $new_customer['id'],
					'description' => $description
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
			$payment_details_html .= ( ! empty( $sc_payment_details['amount'] ) ? '<br/><strong>' . __( 'Total Paid: ', 'sc' ) . sc_convert_amount( $sc_payment_details['amount'], $sc_payment_details['currency'] ) . $sc_payment_details['currency'] . '</strong>' . "\n" : '' );
			
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


/***** FILTERS FOR THE FORM ******/

/*
 * Adds the opening tag for the form
 * 
 * @since 1.1.1
 */
function sc_form_open( $form_open ) {
	
	$form_open = '<form action="" method="POST">';
	
	return $form_open;
}
add_filter( 'sc_form_open', 'sc_form_open' );

/*
 * Adds the necessary script tag for the Simple checkout version
 * 
 * @since 1.1.1
 */
function sc_form_script( $form_script, $script_options ) {
	
	$script_options = sc_get_script_options_string( $script_options );
	
	$form_script = '<script src="https://checkout.stripe.com/checkout.js" class="stripe-button" ' . $script_options . '></script>';
	
	return $form_script;
}
add_filter( 'sc_form_script', 'sc_form_script', 10, 2 );


/*
 * Adds code for the hidden input fields
 * 
 * @since 1.1.1
 */
function sc_form_fields( $form_fields, $script_options ) {
	
	/* Thinking of making this chunk of code into its own function
	 * something like get_default_form_fields()
	 * just so it isn't redundant if you want to make your own filter but also include these
	 */
	$name                 = $script_options['script']['name'];
	$description          = $script_options['script']['description'];
	$amount               = $script_options['script']['amount'];
	$success_redirect_url = $script_options['other']['success-redirect-url'];
	$currency             = $script_options['script']['currency'];
	
	$form_fields = '<input type="hidden" name="sc-name" value="' . esc_attr( $name ) . '" />
					<input type="hidden" name="sc-description" value="' . esc_attr( $description ) . '" />
					<input type="hidden" name="sc-amount" value="' . esc_attr( $amount ) . '" />
					<input type="hidden" name="sc-redirect" value="' . esc_attr( ( ! empty( $success_redirect_url ) ? $success_redirect_url : get_permalink() ) ) . '" />
					<input type="hidden" name="sc-currency" value="' .esc_attr( $currency ) . '" />';
	
	return $form_fields;
}
add_filter( 'sc_form_fields', 'sc_form_fields', 10, 2 );

/*
 * Adds code for closing the form
 * 
 * @since 1.1.1
 */
function sc_form_close( $form_close ) {
	
	$form_close = '</form>';
	
	return $form_close;
}
add_filter( 'sc_form_close', 'sc_form_close' );

/*
 * Commenting out for now as I may need it for reference later

function sc_settings_test( $settings ) {
	$settings['test'] = array( 
		'test_option_1' => array(
				'id'   => 'test_option_1',
				'name' => __( 'Test Option', 'sc' ),
				'desc' => __( 'Test option description.', 'sc' ),
				'type' => 'checkbox'
			),
			'test_option_2' => array(
				'id'   => 'test_option_2',
				'name' => __( 'Test Option 2', 'sc' ),
				'desc' => __( 'Test Option 2 Description' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			)
	);
	
	
	return $settings;
}
add_filter( 'sc_settings', 'sc_settings_test' );
*/


