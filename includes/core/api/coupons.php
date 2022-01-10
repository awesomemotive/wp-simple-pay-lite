<?php
/**
 * API: Coupons
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Core\API\Coupons;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a Coupon.
 *
 * @since 4.3.0
 *
 * @param string $coupon_args Arguments used to create a Coupon.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Coupon
 */
function create( $coupon_args, $api_request_args = array() ) {
	$defaults    = array();
	$coupon_args = wp_parse_args( $coupon_args, $defaults );

	return Stripe_API::request(
		'Coupon',
		'create',
		$coupon_args,
		$api_request_args
	);
}

/**
 * Retrieves a Coupon.
 *
 * @since 4.3.0
 *
 * @param string $coupon_id Coupon ID.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @param array  $opts Per-request options, default empty.
 * @return \SimplePay\Vendor\Stripe\Coupon
 */
function retrieve( $coupon_id, $api_request_args = array(), $opts = array() ) {
	return Stripe_API::request(
		'Coupon',
		'retrieve',
		$coupon_id,
		$api_request_args,
		$opts
	);
}

/**
 * Updates a Coupon.
 *
 * @since 4.3.0
 *
 * @param string $coupon_id Coupon ID.
 * @param string $coupon_args Data to update the Coupon with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Coupon
 */
function update( $coupon_id, $coupon_args = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'Coupon',
		'update',
		$coupon_id,
		$coupon_args,
		$api_request_args
	);
}

/**
 * Deletes a Coupon.
 *
 * @since 4.3.0
 *
 * @param string $coupon_id Coupon ID.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Coupon
 */
function delete( $coupon_id, $api_request_args = array() ) {
	$coupon = retrieve( $coupon_id, $api_request_args );
	return $coupon->delete();
}
