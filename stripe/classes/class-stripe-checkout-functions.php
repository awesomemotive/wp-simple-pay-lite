<?php

/**
 * Functions class file
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Functions' ) ) {
	class Stripe_Checkout_Functions {

		// Class instance variable
		protected static $instance = null;
		
		// Stripe token instance
		protected static $token = false;

		// Class constructor
		private function __construct() {

			// We only want to run the charge if the Token is set
			if ( isset( $_POST['stripeToken'] ) && isset( $_POST['wp-simple-pay'] ) ) {
				self::$token = true;
				add_action( 'init', array( $this, 'charge_card' ) );
			}
			
			// Load Stripe library
			$this->load_library();
			
			// Add the filter to show the details
			add_filter( 'the_content', array( $this, 'show_payment_details' ), 11 );
		}

		/*
		 * Function to load the Stripe library.
		 * Include this plugin's Stripe library reference unless another plugin already includes it.
		 */
		public function load_library() {
			if ( ! class_exists( 'Stripe\Stripe' ) ) {
				require_once( SC_DIR_PATH . 'libraries/stripe-php/init.php' );
			}
		}

		/**
		 * Common function to set Stripe API key from options.
		 *
		 * @since 1.2.4
		 */
		public static function set_key( $test_mode = 'false' ) { 
			global $sc_options;

			$key = '';

			// Check first if in live or test mode.
			if ( $sc_options->get_setting_value( 'enable_live_key' ) == 1 && $test_mode != 'true' ) {
				if ( ! ( null === $sc_options->get_setting_value( 'live_secret_key_temp' ) ) ) {
					$key = $sc_options->get_setting_value( 'live_secret_key_temp' );
				} else {
					$key = $sc_options->get_setting_value( 'live_secret_key' );
				}
			} else {
				if ( ! ( null === $sc_options->get_setting_value( 'test_secret_key_temp' ) ) ) {
					$key = $sc_options->get_setting_value( 'test_secret_key_temp' );
				} else {
					$key = $sc_options->get_setting_value( 'test_secret_key' );
				}
			}
			
			\Stripe\Stripe::setApiKey( $key );
		}

		/**
		 * Function that will actually charge the customers credit card
		 *
		 * @since 1.0.0
		 */
		public static function charge_card() { 
			if ( self::$token && wp_verify_nonce( $_POST['wp-simple-pay-nonce'], 'charge_card' ) ) {

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

				$charge     = array();
				$query_args = array();
				
				$meta = array();
				$meta = apply_filters( 'sc_meta_values', $meta );

				Stripe_Checkout_Functions::set_key( $test_mode );

				// Create new customer
				$new_customer = \Stripe\Customer::create( array(
					'email' => $_POST['stripeEmail'],
					'card'  => $token,
				));

				// Create the charge on Stripe's servers - this will charge the user's default card
				try {
					$charge = \Stripe\Charge::create( array(
							'amount'      => $amount, // amount in cents, again
							'currency'    => $currency,
							'customer'    => $new_customer['id'],
							'description' => $description,
							'metadata'    => $meta,
						)
					);

					// Add Stripe charge ID to querystring.
					$query_args = array(
						'charge'     => $charge->id,
						'store_name' => urlencode( $store_name ),
					);

					$failed = false;

				} catch( \Stripe\Error\Card $e ) {

					// Catch Stripe errors
					$redirect = $fail_redirect;

					$e = $e->getJsonBody();

					// Add failure indicator to querystring.
					$query_args = array( 
						'charge'        => $e['error']['charge'],
						'charge_failed' => true,
					);

					$failed = true;
				}

				unset( $_POST['stripeToken'] );

				do_action( 'sc_redirect_before' );

				if( 'true' == $test_mode ) {
					$query_args['test_mode'] = 'true';
				}
				
				if ( 'below' == $details_placement ) {
					$query_args['details_placement'] = $details_placement;
				}

				self::$token = false;

				wp_redirect( esc_url_raw( add_query_arg( apply_filters( 'sc_redirect_args', $query_args, $charge ), apply_filters( 'sc_redirect', $redirect, $failed ) ) ) );

				exit;
			}
		}

		/*
		 * Function to show the payment details after the purchase
		 * 
		 * @since 1.0.0
		 */
		public static function show_payment_details( $content ) { 
			
			if( in_the_loop() && is_main_query() ) {
				global $sc_options;

				$html = '';

				$test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : 'false' );
				
				$details_placement = ( isset( $_GET['details_placement'] ) ? $_GET['details_placement'] : 'above' );
		
				// Since this is a GET query arg I reset it here in case someone tries to submit it again with their own string written in the URL. 
				// This helps ensure it can only be set to below or above.
				$details_placement = ( $details_placement == 'below' ? 'below' : 'above' );
		
				$is_above = ( $details_placement == 'below' ? 0 : 1 );

				Stripe_Checkout_Functions::set_key( $test_mode );

				// Successful charge output.
				if ( isset( $_GET['charge'] ) && ! isset( $_GET['charge_failed'] ) ) {

					if ( null === $sc_options->get_setting_value( 'disable_success_message' ) ) {

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
							$html .= 'From: ' . stripslashes( stripslashes( esc_html( $_GET['store_name'] ) ) ) . '<br/>' . "\n";
						}

						$html .= '<br/>' . "\n";
						$html .= '<strong>' . __( 'Total Paid: ', 'sc' ) . Stripe_Checkout_Misc::to_formatted_amount( $charge_response->amount, $charge_response->currency ) . ' ' .
								 strtoupper( $charge_response->currency ) . '</strong>' . "\n";

						$html .= '</p>' . "\n";

						$html .= '<p>' . sprintf( __( 'Your transaction ID is: %s', 'sc' ), $charge_id ) . '</p>' . "\n";

						$html .= '</div>' . "\n";

						if ( $is_above ) {
							$content = apply_filters( 'sc_payment_details', $html, $charge_response ) . $content;
						} else {
							$content = $content . apply_filters( 'sc_payment_details', $html, $charge_response );
						}

					}

					do_action( 'sc_after_charge', $charge_response );

				} elseif ( isset( $_GET['charge_failed'] ) ) {

					$charge_id = esc_html( $_GET['charge'] );
			
					$charge = \Stripe\Charge::retrieve( $charge_id );
					// LITE ONLY: Payment details error included in payment details function.

					$html  = '<div class="sc-payment-details-wrap sc-payment-details-error">' . "\n";
					$html .= '<p>' . __( 'Sorry, but there has been an error processing your payment.', 'sc' ) . '</p>' . "\n";
					$html .= '<p>' . $charge->failure_message . '</p>';
					$html .= '</div>' . "\n";
			
					if ( $is_above ) {
						$content = apply_filters( 'sc_payment_details_error', $html ) . $content;
					} else {
						$content = $content . apply_filters( 'sc_payment_details_error', $html );
					}

					do_action( 'sc_after_charge', $charge_response );
				}


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
}
