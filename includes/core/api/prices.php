<?php
/**
 * API: Prices
 *
 * @package SimplePay\Core\API\Prices
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\API\Prices;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a Price.
 *
 * @since 4.1.0
 *
 * @param array $price_args Arguments used to create a Product.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Price
 */
function create( $price_args, $api_request_args = array() ) {
	$defaults   = array();
	$price_args = wp_parse_args( $price_args, $defaults );

	return Stripe_API::request(
		'Price',
		'create',
		$price_args,
		$api_request_args
	);
}

/**
 * Retrieves a Price.
 *
 * @since 4.1.0
 *
 * @param string $price Product ID.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @param array  $opts Per-request options, default empty.
 * @return \SimplePay\Vendor\Stripe\Price
 */
function retrieve( $price, $api_request_args = array(), $opts = array() ) {
	return Stripe_API::request(
		'Price',
		'retrieve',
		$price,
		$api_request_args,
		$opts
	);
}

/**
 * Updates a Price.
 *
 * @since 4.1.0
 *
 * @param string $price_id ID of the Price to update.
 * @param array  $price_args Data to update Price with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Price $price Stripe Price.
 */
function update( $price_id, $price_args, $api_request_args = array() ) {
	return Stripe_API::request(
		'Price',
		'update',
		$price_id,
		$price_args,
		$api_request_args
	);
}
