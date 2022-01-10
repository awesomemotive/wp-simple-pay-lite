<?php
/**
 * API: Products
 *
 * @package SimplePay\Core\API\Products
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\API\Products;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Product.
 *
 * @since 4.1.0
 *
 * @param string|array $product Product ID or {
 *   Arguments used to retrieve a Product.
 *
 *   @type string $id Product ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Product
 */
function retrieve( $product, $api_request_args = array() ) {
	if ( false === is_array( $product ) ) {
		$product_args = array(
			'id' => $product,
		);
	} else {
		$product_args = $product;
	}

	return Stripe_API::request(
		'Product',
		'retrieve',
		$product_args,
		$api_request_args
	);
}

/**
 * Creates a Product.
 *
 * @since 4.1.0
 *
 * @param array $product_args Arguments used to create a Product.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Product
 */
function create( $product_args, $api_request_args = array() ) {
	$defaults     = array();
	$product_args = wp_parse_args( $product_args, $defaults );

	// Create PaymentIntent.
	$product = Stripe_API::request(
		'Product',
		'create',
		$product_args,
		$api_request_args
	);

	return $product;
}

/**
 * Updates a Product.
 *
 * @since 4.1.0
 *
 * @param string $product_id ID of the Product to update.
 * @param array  $product_args Data to update Product with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Product $product Stripe Price.
 */
function update( $product_id, $product_args, $api_request_args = array() ) {
	return Stripe_API::request(
		'Product',
		'update',
		$product_id,
		$product_args,
		$api_request_args
	);
}
