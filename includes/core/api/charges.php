<?php
/**
 * API: Charges
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\API\Charges;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Charge.
 *
 * @since 3.9.0
 *
 * @param string|array $charge Charge ID or {
 *   Arguments used to retrieve a Charge.
 *
 *   @type string $id Charge ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Charge
 */
function retrieve( $charge, $api_request_args = array() ) {
	return Stripe_API::request(
		'Charge',
		'retrieve',
		$charge,
		$api_request_args
	);
}

/**
 * Retrieves Charges.
 *
 * @since 3.9.0
 *
 * @param array $charges Optional arguments used when listing Charges.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Charge[]
 */
function all( $charges = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'Charge',
		'all',
		$charges,
		$api_request_args
	);
}

/**
 * Creates a Charge.
 *
 * @since 3.9.0
 *
 * @param array $charge_args Arguments used to create a Charge.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Charge
 */
function create( $charge_args, $api_request_args = array() ) {
	$defaults    = array();
	$charge_args = wp_parse_args( $charge_args, $defaults );

	/**
	 * Filter the arguments used to generate a Charge.
	 *
	 * @since 3.9.0
	 *
	 * @param array $charge_args Arguemnts used to generate a Charge.
	 */
	$charge_args = apply_filters( 'simpay_create_charge_args', $charge_args );

	/**
	 * Allows processing before a PaymentIntenet is created.
	 *
	 * @since 3.9.0
	 *
	 * @param array $charge_args Arguments used to create a Charge.
	 */
	do_action( 'simpay_before_charge_created', $charge_args );

	// Create Charge.
	$charge = Stripe_API::request(
		'Charge',
		'create',
		$charge_args,
		$api_request_args
	);

	/**
	 * Allows further processing after a Charge has been created.
	 *
	 * @since 3.9.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Charge $charge Charge.
	 */
	do_action( 'simpay_after_charge_created', $charge );

	return $charge;
}
