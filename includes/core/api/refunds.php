<?php
/**
 * API: Refunds
 *
 * @package SimplePay
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\API\Refunds;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a Refund.
 *
 * @since 4.17.4
 *
 * @param array $refund_args Arguments used to create a Refund.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Refund
 */
function create( $refund_args, $api_request_args = array() ) {
	$defaults    = array();
	$refund_args = wp_parse_args( $refund_args, $defaults );

	/**
	 * Filter the arguments used to generate a Refund.
	 *
	 * @since 4.17.4
	 *
	 * @param array $refund_args Arguments used to generate a Refund.
	 */
	$refund_args = apply_filters( 'simpay_create_refund_args', $refund_args );

	/**
	 * Allows processing before a Refund is created.
	 *
	 * @since 4.17.4
	 *
	 * @param array $refund_args Arguments used to create a Refund.
	 */
	do_action( 'simpay_before_refund_created', $refund_args );

	// Create Refund.
	$refund = Stripe_API::request(
		'Refund',
		'create',
		$refund_args,
		$api_request_args
	);

	/**
	 * Allows further processing after a Refund has been created.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Refund $refund Refund.
	 */
	do_action( 'simpay_after_refund_created', $refund );

	return $refund;
}

/**
 * Retrieves a Refund.
 *
 * @since 4.17.4
 *
 * @param string|array $refund Refund ID or {
 *   Arguments used to retrieve a Refund.
 *
 *   @type string $id Refund ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Refund
 */
function retrieve( $refund, $api_request_args = array() ) {
	return Stripe_API::request(
		'Refund',
		'retrieve',
		$refund,
		$api_request_args
	);
}
