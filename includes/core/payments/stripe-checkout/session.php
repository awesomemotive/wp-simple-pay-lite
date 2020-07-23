<?php
/**
 * Stripe Checkout: Session
 *
 * @package SimplePay\Core\Payments\Stripe_Checkout\Sesssion
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Stripe_Checkout\Session;

use SimplePay\Core\Payments;
use SimplePay\Core\Legacy;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Checkout Session.
 *
 * @since 3.9.0
 *
 * @param string|array $session Checkout Session ID or {
 *   Arguments used to retrieve a Checkout Session.
 *
 *   @type string $id Checkout Session ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \Stripe\Checkout\Session
 * @throws \Stripe\Exception
 */
function retrieve( $session, $api_request_args = array() ) {
	if ( false === is_array( $session ) ) {
		$session_args = array(
			'id' => $session,
		);
	} else {
		$session_args = $session;
	}

	return Payments\Stripe_API::request(
		'Checkout\Session',
		'retrieve',
		$session_args,
		$api_request_args
	);
}

/**
 * Create a Checkout Session.
 *
 * @since 3.6.0
 *
 * @param array $session_args Arguments used to create a Checkout Session.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \Stripe\Checkout\Session
 */
function create( $session_args = array(), $api_request_args ) {
	$defaults = array(
		'payment_method_types' => Payments\Stripe_Checkout\get_available_payment_method_types(),
	);

	$session_args = wp_parse_args( $session_args, $defaults );

	/**
	 * Filter the arguments used to create a Checkout Session.
	 *
	 * @since 3.6.0
	 *
	 * @param array $session_args Arguments used to construct the session.
	 */
	$session_args = apply_filters( 'simpay_create_stripe_checkout_session_args', $session_args );

	/**
	 * Allows processing before a Checkout\Session is created.
	 *
	 * @since 3.6.0
	 *
	 * @param array $session_args Arguments used to create a Checkout\Session.
	 */
	do_action( 'simpay_before_checkout_session_created', $session_args );

	$session = Payments\Stripe_API::request(
		'Checkout\Session',
		'create',
		$session_args,
		$api_request_args
	);

	/**
	 * Allows further processing after a Checkout\Session has been created.
	 *
	 * @since 3.6.0
	 *
	 * @param \Stripe\Checkout\Session $session Checkout Session.
	 */
	do_action( 'simpay_after_checkout_session_created', $session );

	return $session;
}
