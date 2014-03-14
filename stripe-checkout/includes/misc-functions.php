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
	
		if( ! empty( $sc_options['disable_test_key'] ) ) {
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
		} catch(Stripe_CardError $e) {
		  
			$redirect = add_query_arg( 'payment', 'failed', $_POST['sc-redirect'] );
		}
		
		unset( $_POST['stripeToken'] );
		
		wp_redirect( $redirect );
		
		exit;
	}
}
// We only want to run the charge if the Token is set
if( isset( $_POST['stripeToken'] ) ) {
	add_action( 'init', 'sc_charge_card' );
}
