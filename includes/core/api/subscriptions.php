<?php
/**
 * API: Subscriptions
 *
 * @package SimplePay\Core\Payments\Subscriptions
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.4
 */

namespace SimplePay\Core\API\Subscriptions;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Subscription.
 *
 * @since 4.6.4
 *
 * @param string|array $subscription Subscription ID or {
 *   Arguments used to retrieve a Subscription.
 *
 *   @type string $id Subscription ID.
 * }
 * @param array        $api_request_args {
 *         Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Subscription
 */
function retrieve( $subscription, $api_request_args = array() ) {
	if ( false === is_array( $subscription ) ) {
		$subscription_args = array(
			'id' => $subscription,
		);
	} else {
		$subscription_args = $subscription;
	}

	return Stripe_API::request(
		'Subscription',
		'retrieve',
		$subscription_args,
		$api_request_args
	);
}

/**
 * Lists all Subscriptions for given criteria.
 *
 * @since 4.6.4
 *
 * @param array $subscriptions Optional arguments used when listing Subscriptions.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return object
 */
function all( $subscriptions = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'Subscription',
		'all',
		$subscriptions,
		$api_request_args
	);
}

/**
 * Creates a Subscription.
 *
 * @since 4.6.4
 *
 * @param array $subscription_args Optional arguments used to create a subscription.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Subscription
 */
function create( $subscription_args = array(), $api_request_args = array() ) {
	$defaults          = array();
	$subscription_args = wp_parse_args( $subscription_args, $defaults );

	/**
	 * Filter the arguments passed to Subscription creation in Stripe.
	 *
	 * @since 3.6.0
	 *
	 * @param array $subscription_args Arguments passed to Subscription creation in Stripe.
	 */
	$subscription_args = apply_filters( 'simpay_create_stripe_subscription_args', $subscription_args );

	/**
	 * Allows processing before a Subscription is created.
	 *
	 * @since 3.6.0
	 *
	 * @param array $subscription_args Optional arguments used to create a Subscription.
	 */
	do_action( 'simpay_before_subscription_created', $subscription_args );

	$subscription = Stripe_API::request(
		'Subscription',
		'create',
		$subscription_args,
		$api_request_args
	);

	/**
	 * Allows further processing after a Subscription has been created.
	 *
	 * @since 3.6.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Subscription.
	 */
	do_action( 'simpay_after_subscription_created', $subscription );

	return $subscription;
}

/**
 * Updates a Subscription.
 *
 * @since 4.6.4
 *
 * @param string $subscription_id ID of the Customer to update.
 * @param array  $subscription_args Data to update Customer with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Customer $subscription Stripe Customer.
 */
function update( $subscription_id, $subscription_args, $api_request_args = array() ) {
	/**
	 * Filters the arguments passed to Subscription creation in Stripe.
	 *
	 * @since 3.7.0
	 *
	 * @param array $subscription_args Arguments passed to subscription creation in Stripe.
	 * @param string $subscription_id ID of the Customer to update.
	 */
	$subscription_args = apply_filters(
		'simpay_update_subscription_args',
		$subscription_args,
		$subscription_id
	);

	$subscription = Stripe_API::request(
		'Subscription',
		'update',
		$subscription_id,
		$subscription_args,
		$api_request_args
	);

	/**
	 * Allows further processing after a Subscription has been update.
	 *
	 * @since 3.7.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 */
	do_action( 'simpay_after_subscription_created', $subscription );

	return $subscription;
}

/**
 * Cancels a Stripe Subscription.
 *
 * @since 4.6.4
 *
 * @param string $subscription_id Stripe Subscription ID.
 * @param string $schedule When to cancel the Subscription. at_period_end or immediately.
 *                                       Default immediately.
 * @param array  $api_request_args {
 *                 Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Subscription
 */
function cancel( $subscription_id, $schedule = 'immediately', $api_request_args = array() ) {
	// Cancel immediately.
	if ( 'immediately' === $schedule ) {
		$subscription = retrieve( $subscription_id, $api_request_args );
		$subscription->cancel();

		return $subscription;
		// Cancel at the end of billing period.
	} else {
		return update(
			$subscription_id,
			array(
				'cancel_at_period_end' => true,
			),
			$api_request_args
		);
	}
}
