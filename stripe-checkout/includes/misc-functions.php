<?php

/**
 * Misc plugin functions
 * 
 * @since 1.0.0
 */

/**
 * Function that will actually charge the customers credit card
 * 
 * @since 1.0.0
 */
function sc_charge_card() {
	if( isset( $_POST['stripeToken'] ) ) {
		
		include_once( 'Stripe.php' );
		
		global $sc_options;
		
		// Get the credit card details submitted by the form
		$token       = $_POST['stripeToken'];
		$amount      = $_POST['sc-amount'];
		$description = $_POST['sc-description'];
		$name        = $_POST['sc-name'];
	
		if( empty( $sc_options['enable_test_key'] ) ) {
			$key = ( ! empty( $sc_options['live_secret_key'] ) ? $sc_options['live_secret_key'] : '' );
		} else {
			$key = ( ! empty( $sc_options['test_secret_key'] ) ? $sc_options['test_secret_key'] : '' );
		}

		// Set your secret key: remember to change this to your live secret key in production
		Stripe::setApiKey( $key );


		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			$charge = Stripe_Charge::create( array(
					'amount'      => $amount, // amount in cents, again
					'currency'    => 'usd',
					'card'        => $token,
					'description' => $description
				)
			);
			
			$redirect = add_query_arg( array( 'payment' => 'success', 'amount' => $amount ), $_POST['sc-redirect'] );
			
			$failed = false;
		} catch(Stripe_CardError $e) {
		  
			$redirect = add_query_arg( 'payment', 'failed', $_POST['sc-redirect'] );
			
			$failed = true;
		}
		
		unset( $_POST['stripeToken'] );
		
		
		if( ! $failed ) {
			// Update our payment details option so we can show it at the top of the content
			$sc_payment_details['show']        = 1;
			$sc_payment_details['amount']      = $amount;
			$sc_payment_details['name']        = $name;
			$sc_payment_details['description'] = $description;

			update_option( 'sc_payment_details', $sc_payment_details );
		}
		
		wp_redirect( $redirect );
		
		exit;
	}
}
// We only want to run the charge if the Token is set
if( isset( $_POST['stripeToken'] ) ) {
	add_action( 'init', 'sc_charge_card' );
}

function sc_show_payment_details( $content ) {
	
	$sc_payment_details = get_option( 'sc_payment_details' );
	$payment_details_html = '';
	
	if( ! empty( $sc_payment_details ) ) {
		if( $sc_payment_details['show'] == 1 ) {
			$before_payment_details_html = '<div class="sc-payment-details-wrap">' . "\n";

			$payment_details_html .= '<p>Congratulations. Your payment went through!</p>' . "\n";
			$payment_details_html .= '<p>Here\'s what you bought:</p>' . "\n";
			$payment_details_html .= ( ! empty( $sc_payment_details['description'] ) ? $sc_payment_details['description'] . '<br/>' . "\n" : '' );
			$payment_details_html .= ( ! empty( $sc_payment_details['name'] ) ? 'From: ' . $sc_payment_details['name'] . '<br/>' . "\n" : '' );
			$payment_details_html .= ( ! empty( $sc_payment_details['amount'] ) ? '<br/><strong>Total Paid: $' . sc_convert_amount( $sc_payment_details['amount'] ) . '</strong>' . "\n" : '' );
			
			$after_payment_details_html = '</div>' . "\n";
			
			$before_payment_details_html = apply_filters( 'sc_before_payment_details_html', $before_payment_details_html );
			$payment_details_html        = apply_filters( 'sc_payment_details_html', $payment_details_html );
			$after_payment_details_html  = apply_filters( 'sc_after_payment_details_html', $after_payment_details_html );
			
			$content = $before_payment_details_html . $payment_details_html . $after_payment_details_html . $content;
			
			delete_option( 'sc_payment_details' );
		}
	}
	
	return $content;
}
add_filter( 'the_content', 'sc_show_payment_details' );

function sc_convert_amount( $amount ) {
	return number_format( ( $amount / 100 ), 2 );
}
