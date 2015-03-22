<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Stripe_Checkout_Functions' ) ) {
	class Stripe_Checkout_Functions {


		protected static $instance = null;

		protected static $token = false;

		// Class constructor
		private function __construct() {

			// We only want to run the charge if the Token is set
			if( isset( $_POST['stripeToken'] ) ) {
				self::$token = true;
				add_action( 'init', array( $this, 'charge_card' ) );
			}

			add_filter( 'the_content', array( $this, 'show_payment_details' ) );
		}

		// Public functions

		/**
		 * Common method to set Stripe API key from options.
		 *
		 * @since 1.2.4
		 */
		public static function set_key( $test_mode = 'false' ) { 
			global $sc_options;

			$key = '';

			// Check first if in live or test mode.
			if( ! empty( $sc_options['enable_live_key'] ) && $sc_options['enable_live_key'] == 1 && $test_mode != 'true' ) {
				$key = ( ! empty( $sc_options['live_secret_key'] ) ? $sc_options['live_secret_key'] : '' );
			} else {
				$key = ( ! empty( $sc_options['test_secret_key'] ) ? $sc_options['test_secret_key'] : '' );
			}

			Stripe::setApiKey( $key );
		}

		/**
		 * Function that will actually charge the customers credit card
		 *
		 * @since 1.0.0
		 */
		public static function charge_card() { 
			if( self::$token ) {

				$redirect      = $_POST['sc-redirect'];
				$fail_redirect = $_POST['sc-redirect-fail'];

				// Get the credit card details submitted by the form
				$token       = $_POST['stripeToken'];
				$amount      = $_POST['sc-amount'];
				$description = $_POST['sc-description'];
				$store_name  = $_POST['sc-name'];
				$currency    = $_POST['sc-currency'];
				$test_mode   = ( isset( $_POST['sc_test_mode'] ) ? $_POST['sc_test_mode'] : 'false' );

				$charge = array();
				$query_args = array();

				$meta = array();
				$meta = apply_filters( 'sc_meta_values', $meta );

				//sc_set_stripe_key( $test_mode );
				Stripe_Checkout_Functions::set_key( $test_mode );

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
							'description' => $description,
							'metadata'    => $meta
						)
					);

					// Add Stripe charge ID to querystring.
					$query_args = array( 'charge' => $charge->id, 'store_name' => urlencode( $store_name ) );

					$failed = false;

				} catch(Stripe_CardError $e) {

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

				self::$token = false;

				wp_redirect( add_query_arg( $query_args, apply_filters( 'sc_redirect', $redirect, $failed ) ) );

				do_action( 'sc_redirect_after' );

				exit;
			}
		}

		/*
		 * Function to show the payment details after the purchase
		 * 
		 * @since 1.0.0
		 */
		public static function show_payment_details( $content ) { 
			global $sc_options;

			$html = '';

			$test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : 'false' );

			//sc_set_stripe_key( $test_mode );
			Stripe_Checkout_Functions::set_key( $test_mode );

			// Successful charge output.
			if ( isset( $_GET['charge'] ) && !isset( $_GET['charge_failed'] ) ) {

				if ( empty( $sc_options['disable_success_message'] ) ) {

					$charge_id = esc_html( $_GET['charge'] );

					// https://stripe.com/docs/api/php#charges
					$charge_response = Stripe_Charge::retrieve( $charge_id );

					$html = '<div class="sc-payment-details-wrap">' . "\n";

					$html .= '<p>' . __( 'Congratulations. Your payment went through!', 'sc' ) . '</p>' . "\n";
					$html .= '<p>' . "\n";

					if ( ! empty( $charge_response->description ) ) {
						$html .= __( "Here's what you bought:", 'sc' ) . '<br/>' . "\n";
						$html .= $charge_response->description . '<br/>' . "\n";
					}

					if ( isset( $_GET['store_name'] ) && ! empty( $_GET['store_name'] ) ) {
						$html .= 'From: ' . esc_html( $_GET['store_name'] ) . '<br/>' . "\n";
					}

					$html .= '<br/>' . "\n";
					$html .= '<strong>' . __( 'Total Paid: ', 'sc' ) . Stripe_Checkout_Misc::to_formatted_amount( $charge_response->amount, $charge_response->currency ) . ' ' .
							 strtoupper( $charge_response->currency ) . '</strong>' . "\n";

					$html .= '</p>' . "\n";

					$html .= '<p>' . sprintf( __( 'Your transaction ID is: %s', 'sc' ), $charge_id ) . '</p>' . "\n";

					$html .= '</div>' . "\n";

					return apply_filters( 'sc_payment_details', $html, $charge_response ) . $content;

				} else {

					return $content;
				}

			} elseif ( isset( $_GET['charge_failed'] ) ) {

				// LITE ONLY: Payment details error included in payment details function.

				$html  = '<div class="sc-payment-details-wrap sc-payment-details-error">' . "\n";
				$html .= '<p>' . "\n";

				$html .= __( 'Sorry, but there has been an error processing your payment.', 'sc' ) . "\n";
				$html .= __( 'If the problem persists please contact the site owner.', 'sc' ) . "\n";

				$html .= '</p>' . "\n";
				$html .= '</div>' . "\n";

				return apply_filters( 'sc_payment_details_error', $html ) . $content;
			}

			return $content;
		}

		// Return instance of this class
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
	
	Stripe_Checkout_Functions::get_instance();
}