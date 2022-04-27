<?php
/**
 * API: SetupIntents
 *
 * @package SimplePay\Core\API\SetupIntents
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.2.0
 */

namespace SimplePay\Core\API\SetupIntents;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a SetupIntent.
 *
 * @since 4.2.0
 *
 * @param array $setupintent_args SetupIntent arguments.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\SetupIntent
 */
function create( $setupintent_args = array(), $api_request_args = array() ) {
	$defaults         = array();
	$setupintent_args = wp_parse_args( $setupintent_args, $defaults );

	$setupintent = Stripe_API::request(
		'SetupIntent',
		'create',
		$setupintent_args,
		$api_request_args
	);

	return $setupintent;
}

/**
 * Retrieves SetupIntents.
 *
 * @since 3.8.0
 *
 * @param array $args Query arguments.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return object
 */
function all( $args = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'SetupIntent',
		'all',
		$args,
		$api_request_args
	);
}
