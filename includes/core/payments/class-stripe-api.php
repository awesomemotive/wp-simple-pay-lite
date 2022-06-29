<?php
/**
 * Stripe API
 *
 * @link https://github.com/stripe/stripe-php
 *
 * @package SimplePay\Core\Bootstrap
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Payments;

use SimplePay\Vendor\Stripe\Stripe;
use SimplePay\Vendor\Stripe\Util\Util;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe_API class.
 *
 * @since 3.0.0
 */
class Stripe_API {

	/**
	 * Sets application information.
	 *
	 * @since 3.6.0
	 */
	public static function set_app_info() {
		// Send our plugin info over with the API request.
		Stripe::setAppInfo(
			sprintf( 'WordPress %s', SIMPLE_PAY_PLUGIN_NAME ),
			SIMPLE_PAY_VERSION,
			SIMPLE_PAY_STORE_URL,
			SIMPLE_PAY_STRIPE_PARTNER_ID
		);

		// Send the API info over.
		Stripe::setApiVersion( SIMPLE_PAY_STRIPE_API_VERSION );
	}

	/**
	 * Sets the API Keys
	 *
	 * @since 3.0.0
	 * @since 4.3.0 Log an error if called directly.
	 */
	public static function set_api_key() {
		if ( ! simpay_check_keys_exist() ) {
			return;
		}

		Stripe::setApiKey( simpay_get_secret_key() );

		$logger = Stripe::getLogger();
		$logger->error(
			sprintf(
				/* translators: %s Stripe API function. */
				__(
					'Calling %s directly is discouraged. Please use API wrappers or pass the Stripe API key via the ::request() static method.',
					'stripe'
				),
				__METHOD__
			)
		);
	}

	/**
	 * Wraps the Stripe API PHP bindings.
	 *
	 * @since unknown
	 *
	 * @param string       $class Unqualified Stripe API PHP binding class name.
	 * @param string       $function Function to call.
	 * @param string|array $id_or_args ID of a resource to update, or arguments for request, default empty.
	 * @param array        $args Arguments for request, default empty.
	 * @param array        $opts Per-request options, default empty.
	 */
	public static function request(
		$class,
		$function,
		$id_or_args = array(),
		$args = array(),
		$opts = array()
	) {
		// Enure app information is set.
		self::set_app_info();

		// Allow retries and enable idempotency on network failures.
		Stripe::setMaxNetworkRetries( 2 );

		$default_opts = array(
			'api_key' => simpay_get_secret_key(),
		);

		// Move per request arguments up if not empty, and request arguments are.
		if ( empty( $args ) ) {
			$args = wp_parse_args( $opts, $default_opts );
		} else {
			$opts = wp_parse_args( $opts, $default_opts );
		}

		// Log an error.
		if ( empty( $opts ) ) {
			$logger = Stripe::getLogger();
			$logger->error(
				sprintf(
					/* translators: %s Stripe_API::request() arguments. */
					__( "Calling \SimplePay\Core\Payments\Stripe_API::request() without per-form request options (such as an API key) is discouraged. \n%s\n\n", 'stripe' ),
					sprintf(
						'%s\%s\%s',
						$class,
						$function,
						serialize( $id_or_args )
					)
				)
			);
		}

		$cache_id = is_array( $id_or_args ) && isset( $id_or_args['id'] )
			? $id_or_args['id']
			: $id_or_args;
		$cache_id = is_array( $cache_id ) ? serialize( $cache_id ) : $cache_id;

		$cache_key = sprintf( 'simpay_stripe_%s', $cache_id );

		// Cache if retrieving, and set.
		if (
			'retrieve' === $function &&
			isset( $opts['cached'] ) &&
			true === $opts['cached']
		) {
			$object = get_transient( $cache_key );

			if ( false === $object ) {
				$object = call_user_func(
					array( '\SimplePay\Vendor\Stripe\\' . $class, $function ),
					$id_or_args,
					$args,
					$opts
				);

				set_transient( $cache_key, $object->toArray(), DAY_IN_SECONDS );
			} else {
				$object = Util::convertToStripeObject( $object, $opts );
			}

			return $object;
		}

		// Clear cache if updating.
		if ( 'update' === $function ) {
			delete_transient( $cache_key );
		}

		return call_user_func(
			array( '\SimplePay\Vendor\Stripe\\' . $class, $function ),
			$id_or_args,
			$args,
			$opts
		);
	}
}
