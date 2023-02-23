<?php
/**
 * API: PaymentIntents
 *
 * @package SimplePay\Core\API\PaymentIntents
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.0
 */

namespace SimplePay\Core\API\PaymentIntents;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a PaymentIntent.
 *
 * @since 3.8.0
 *
 * @param string|array $payment_intent Payment Intent ID or {
 *   Arguments used to retrieve a PaymentIntent.
 *
 *   @type string $id Payment Intent ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\PaymentIntent
 */
function retrieve( $payment_intent, $api_request_args = array() ) {
	if ( false === is_array( $payment_intent ) ) {
		$payment_intent_args = array(
			'id' => $payment_intent,
		);
	} else {
		$payment_intent_args = $payment_intent;
	}

	return Stripe_API::request(
		'PaymentIntent',
		'retrieve',
		$payment_intent_args,
		$api_request_args
	);
}

/**
 * Retrieves PaymentIntents.
 *
 * @since 3.8.0
 *
 * @param array $payment_intents Optional arguments used when listing PaymentIntents.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return object
 */
function all( $payment_intents = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'PaymentIntent',
		'all',
		$payment_intents,
		$api_request_args
	);
}

/**
 * Creates a PaymentIntent.
 *
 * @since 3.6.0
 *
 * @param array $paymentintent_args Arguments used to create a PaymentIntent.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\PaymentIntent
 */
function create( $paymentintent_args, $api_request_args = array() ) {
	$defaults           = array();
	$paymentintent_args = wp_parse_args( $paymentintent_args, $defaults );

	/**
	 * Filter the arguments used to generate a PaymentIntent.
	 *
	 * @since 3.6.0
	 *
	 * @param array $payment_intent_args Arguemnts used to generate a PaymentIntent.
	 */
	$paymentintent_args = apply_filters( 'simpay_create_paymentintent_args', $paymentintent_args );

	/**
	 * Allows processing before a PaymentIntenet is created.
	 *
	 * @since 3.6.0
	 *
	 * @param array $paymentintent_args Arguments used to create a PaymentIntent.
	 */
	do_action( 'simpay_before_paymentintent_created', $paymentintent_args );

	// Create PaymentIntent.
	$paymentintent = Stripe_API::request(
		'PaymentIntent',
		'create',
		$paymentintent_args,
		$api_request_args
	);

	/**
	 * Allows further processing after a PaymentIntent has been created.
	 *
	 * @since 3.6.0
	 *
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent $paymentintent PaymentIntent.
	 */
	do_action( 'simpay_after_paymentintent_created', $paymentintent );

	return $paymentintent;
}

/**
 * Updates a PaymentIntent record.
 *
 * @since 4.6.0
 *
 * @param string $paymentintent_id ID of the PaymentIntent to update.
 * @param array  $paymentintent_args Data to update PaymentIntent with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\PaymentIntent $paymentintent Stripe PaymentIntent.
 */
function update( $paymentintent_id, $paymentintent_args, $api_request_args = array() ) {
	return Stripe_API::request(
		'PaymentIntent',
		'update',
		$paymentintent_id,
		$paymentintent_args,
		$api_request_args
	);
}
