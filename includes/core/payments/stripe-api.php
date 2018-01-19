<?php

namespace SimplePay\Core\Payments;

use SimplePay\Core\Errors;
use Stripe\Error;
use Stripe\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Stripe_API
 *
 * @package SimplePay\Payments
 *
 * Wrapper class to allow us to make Stripe API requests all in one place and not scattered throughout the code.
 */
class Stripe_API {

	// Variable to make sure the API keys are set
	public static $api_set = false;

	/**
	 * Stripe_API constructor
	 */
	public function __construct() {

		// Send our plugin info over with the API request
		Stripe::setAppInfo( SIMPLE_PAY_PLUGIN_NAME, SIMPLE_PAY_VERSION, SIMPLE_PAY_STORE_URL );

		// Send the API info over
		Stripe::setApiVersion( SIMPLE_PAY_STRIPE_API_VERSION );

	}

	/**
	 * Set the API Keys
	 */
	public static function set_api_key() {

		// Set the API keys if they exist
		if ( simpay_check_keys_exist() ) {

			$key = simpay_get_secret_key();

			Stripe::setApiKey( $key );

			self::$api_set = true;

		} else {
			// TODO: Need some sort of error class or a way to handle errors that need to be output.
		}
	}

	/**
	 * Function we use to create a Stripe API request
	 *
	 * @param $class    - The Stripe API class we need to use
	 * @param $function - The function of the $class we need
	 * @param $args     - The arguments to pass into the $function
	 *
	 * @return mixed Stripe API object if successful
	 */
	public static function request( $class, $function, $args ) {

		/**
		 * https://stripe.com/docs/api/php#errors
		 * https://stripe.com/docs/api/php#error_handling
		 */

		// If the API has not been set already we need to set the key here
		if ( ! self::$api_set ) {
			self::set_api_key();
		}

		try {
			// Call the Stripe API request from our parameters
			$retval = call_user_func( array( '\Stripe\\' . $class, $function ), $args );
			return $retval;

		} catch ( Error\Card $e ) {
			// Card declined
			return self::error_handler( 'card_error', esc_html__( 'Card Error', 'stripe' ) . ': ' . $e->getMessage() );

		} catch ( Error\RateLimit $e ) {
			// Too many requests made to the API too quickly
			return self::error_handler( 'rate_limit', esc_html__( 'Rate Limit Error', 'stripe' ) . ': ' . $e->getMessage() );

		} catch ( Error\InvalidRequest $e ) {
			// Invalid parameters were supplied to Stripe's API
			return self::error_handler( 'invalid_request', esc_html__( 'Invalid Request Error', 'stripe' ) . ': ' . $e->getMessage() );

		} catch ( Error\Authentication $e ) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			return self::error_handler( 'authentication', esc_html__( 'Authentication Error', 'stripe' ) . ': ' . $e->getMessage() );

		} catch ( Error\ApiConnection $e ) {
			// Network communication with Stripe failed
			return self::error_handler( 'api_connection', esc_html__( 'Stripe API Connection Error', 'stripe' ) . ': ' . $e->getMessage() );

		} catch ( Error\Base $e ) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			return self::error_handler( 'generic', esc_html__( 'Stripe Error', 'stripe' ) . ': ' . $e->getMessage() );

		} catch ( \Exception $e ) {
			// Something else happened, completely unrelated to Stripe
			return self::error_handler( 'non_stripe', esc_html__( 'General Error', 'stripe' ) . ': ' . $e->getMessage() );
		}
	}

	/**
	 * Generic Stripe API error handler
	 */
	private static function error_handler( $error_id, $error_message ) {

		global $simpay_form;

		// TODO Fallback for users running < WP 4.7. Maybe just use wp_doing_ajax() eventually.
		if ( function_exists( 'wp_doing_ajax' ) ) {
			$simpay_doing_ajax = wp_doing_ajax();
		} else {
			$simpay_doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
		}

		// Don't save error to session if calling via ajax (i.e. coupon codes) or in admin.
		if ( ! is_admin() && ! $simpay_doing_ajax ) {
			Errors::set( $error_id, $error_message );
			wp_redirect( $simpay_form->payment_failure_page );
			exit;
		} else {
			return false;
		}
	}
}
