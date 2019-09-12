<?php
/**
 * Stripe Checkout Session functionality.
 *
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
 * Create a Checkout Session.
 *
 * @since 3.6.0
 *
 * @param array $session_args Arguments used to create a Checkout Session.
 * @return \Stripe\Checkout\Session
 */
function create( $session_args = array() ) {
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

	$session = Payments\Stripe_API::request( 'Checkout\Session', 'create', $session_args );

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
