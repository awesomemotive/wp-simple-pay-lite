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
	 * Stripe_API constructor.
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

		global $simpay_form;

		// If the API has not been set already we need to set the key here
		if ( ! self::$api_set ) {
			self::set_api_key();
		}

		try {

			// Call the Stripe API request from our parameters.
			return call_user_func( array( '\Stripe\\' . $class, $function ), $args );

		} catch ( Error\Card $e ) {

			// Too many requests made to the API too quickly
			Errors::set( 'card_error', 'Card Error: ' . $e->getMessage() );

			if ( ! is_admin() ) {
				wp_redirect( $simpay_form->payment_failure_page );
				exit;
			} else {
				return false;
			}

		} catch ( Error\RateLimit $e ) {

			// Too many requests made to the API too quickly
			Errors::set( 'rate_limit', 'Rate Limit Error: ' . $e->getMessage() );

			if ( ! is_admin() ) {
				wp_redirect( $simpay_form->payment_failure_page );
				exit;
			} else {
				return false;
			}

		} catch ( Error\InvalidRequest $e ) {

			// Invalid parameters were supplied to Stripe's API
			Errors::set( 'invalid_request', 'Invalid Request Error: ' . $e->getMessage() );

			if ( ! is_admin() ) {
				wp_redirect( $simpay_form->payment_failure_page );
				exit;
			} else {
				return false;
			}

		} catch ( Error\Authentication $e ) {

			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			Errors::set( 'authentication', 'Authentication Error: ' . $e->getMessage() );

			if ( ! is_admin() ) {
				wp_redirect( $simpay_form->payment_failure_page );
				exit;
			} else {
				return false;
			}

		} catch ( Error\ApiConnection $e ) {

			// Network communication with Stripe failed
			Errors::set( 'api_connection', 'API Connection Error: ' . $e->getMessage() );

			if ( ! is_admin() ) {
				wp_redirect( $simpay_form->payment_failure_page );
				exit;
			} else {
				return false;
			}

		} catch ( Error\Base $e ) {

			// Display a very generic error to the user, and maybe send
			// yourself an email
			Errors::set( 'generic', 'Error: ' . $e->getMessage() );

			if ( ! is_admin() ) {
				wp_redirect( $simpay_form->payment_failure_page );
				exit;
			} else {
				return false;
			}

		} catch ( \Exception $e ) {

			// Something else happened, completely unrelated to Stripe
			Errors::set( 'non_stripe', 'Non-Stripe Error: ' . $e->getMessage() );

			if ( ! is_admin() ) {
				wp_redirect( $simpay_form->payment_failure_page );
				exit;
			} else {
				return false;
			}
		}
	}
}
