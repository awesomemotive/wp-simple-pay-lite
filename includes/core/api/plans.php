<?php
/**
 * API: Plans
 *
 * @package SimplePay\Core\API\Plans
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\API\Plans;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Plan.
 *
 * @since 4.1.0
 *
 * @param string|array $plan Plan ID or {
 *   Arguments used to retrieve a Plan.
 *
 *   @type string $id Plan ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Plan
 */
function retrieve( $plan, $api_request_args = array() ) {
	if ( false === is_array( $plan ) ) {
		$plan_args = array(
			'id' => $plan,
		);
	} else {
		$plan_args = $plan;
	}

	return Stripe_API::request(
		'Plan',
		'retrieve',
		$plan_args,
		$api_request_args
	);
}

/**
 * Lists Plans.
 *
 * @since 4.1.0
 *
 * @param array $args Optional arguments used when listing Plans.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Plan[]
 */
function all( $args = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'Plan',
		'all',
		$args,
		$api_request_args
	);
}

/**
 * Creates a Plan.
 *
 * @since 4.1.0
 *
 * @param array $plan_args Optional arguments used to create a Plan.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Plan
 */
function create( $plan_args = array(), $api_request_args = array() ) {
	$defaults = array();

	$plan_args = wp_parse_args( $plan_args, $defaults );

	// Use an existing Product ID.
	if ( is_a( $plan_args['product'], '\SimplePay\Vendor\Stripe\Product' ) ) {
		$plan_args['product'] = $plan_args['product']->id;
	}

	/**
	 * Filter the arguments passed to Plan creation in Stripe.
	 *
	 * @since 3.6.0
	 *
	 * @param array $plan_args Arguments passed to plan creation in Stripe.
	 */
	$plan_args = apply_filters( 'simpay_stripe_plan_args', $plan_args );

	return Stripe_API::request(
		'Plan',
		'create',
		$plan_args,
		$api_request_args
	);
}

/**
 * Updates a Plan.
 *
 * @since 4.1.0
 *
 * @param string $plan_id ID of the Plan to update.
 * @param array  $plan_args Data to update Plan with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Plan $plan Stripe Plan.
 */
function update( $plan_id, $plan_args, $api_request_args = array() ) {
	return Stripe_API::request(
		'Plan',
		'update',
		$plan_id,
		$plan_args,
		$api_request_args
	);
}

/**
 * Removes a Plan.
 *
 * @since 4.1.0
 *
 * @param Stripe\Plan $plan Stripe Plan.
 * @return bool If the Plan was deleted.
 */
function delete( $plan ) {
	$plan->delete();

	return $plan->deleted;
}
