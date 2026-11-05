<?php
/**
 * API: Checkout Sessions
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\API\CheckoutSessions;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Checkout Session.
 *
 * @since 4.7.0
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
 * @return \SimplePay\Vendor\Stripe\Checkout\Session
 */
function retrieve( $session, $api_request_args = array() ) {
	if ( false === is_array( $session ) ) {
		$session_args = array(
			'id' => $session,
		);
	} else {
		$session_args = $session;
	}

	return Stripe_API::request(
		'Checkout\Session',
		'retrieve',
		$session_args,
		$api_request_args
	);
}

/**
 * Create a Checkout Session.
 *
 * @since 4.7.0
 *
 * @param array $session_args Arguments used to create a Checkout Session.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Checkout\Session
 */
function create( $session_args = array(), $api_request_args = array() ) {
	$defaults = array();

	$session_args = wp_parse_args( $session_args, $defaults );

	/**
	 * Filter the arguments used to create a Checkout Session.
	 *
	 * @since 3.6.0
	 *
	 * @param array $session_args Arguments used to construct the session.
	 */
	$session_args = apply_filters(
		'simpay_create_stripe_checkout_session_args',
		$session_args
	);

	// Optionally enable Stripe's Optimized Checkout Suite by using automatic
	// payment methods instead of an explicit payment_method_types list.
	//
	// When payment_method_types is omitted from a Checkout Session, Stripe
	// automatically determines which payment methods to show based on the
	// customer's location, transaction details, and conversion data.
	$session_args = maybe_enable_automatic_payment_methods( $session_args );

	/**
	 * Allows processing before a Checkout\Session is created.
	 *
	 * @since 3.6.0
	 *
	 * @param array $session_args Arguments used to create a Checkout\Session.
	 */
	do_action( 'simpay_before_checkout_session_created', $session_args );

	$session = Stripe_API::request(
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
	 * @param \SimplePay\Vendor\Stripe\Checkout\Session $session Checkout Session.
	 */
	do_action( 'simpay_after_checkout_session_created', $session );

	return $session;
}

/**
 * Determines if the global "Optimized Checkout" setting is enabled.
 *
 * When enabled, explicit payment method selections (including the per-form
 * "Disable Stripe Link" toggle) are not sent to Stripe for off-site Stripe
 * Checkout forms.
 *
 * @since 4.17.6
 *
 * @return bool
 */
function is_automatic_payment_methods_setting_enabled() {
	return 'yes' === simpay_get_setting(
		'stripe_checkout_automatic_payment_methods',
		'no'
	);
}

/**
 * Conditionally removes explicit payment_method_types from Checkout Session
 * arguments to enable Stripe's Optimized Checkout Suite.
 *
 * When payment_method_types is omitted, Stripe dynamically determines which
 * payment methods to display and their ordering based on the customer's
 * location, transaction history, and conversion data.
 *
 * Controlled by the "Optimized Checkout" setting in Settings → Stripe.
 * Disabled by default so the payment methods configured on each payment
 * form continue to be used unless a site explicitly opts in.
 *
 * @since 4.17.6
 *
 * @param array $session_args Checkout Session arguments.
 * @return array Modified Checkout Session arguments.
 */
function maybe_enable_automatic_payment_methods( $session_args ) {
	if ( ! use_automatic_payment_methods( $session_args ) ) {
		return $session_args;
	}

	// Remove explicit payment_method_types to let Stripe handle selection.
	if ( isset( $session_args['payment_method_types'] ) ) {
		unset( $session_args['payment_method_types'] );
	}

	return $session_args;
}

/**
 * Determines if Checkout Sessions will use Stripe's automatic payment methods
 * (Optimized Checkout Suite).
 *
 * This is the effective value: the "Optimized Checkout" setting passed through
 * the `simpay_checkout_session_use_automatic_payment_methods` filter. Use it
 * anywhere the runtime behavior is reported (form builder notices, Site
 * Health) so those reports cannot disagree with the Checkout Session sent to
 * Stripe.
 *
 * @since 4.17.6
 *
 * @param array<string, mixed> $session_args Checkout Session arguments, or the
 *                                           subset known in the current
 *                                           context (for example only the
 *                                           `metadata.simpay_form_id` in the
 *                                           form builder). Default empty.
 * @return bool
 */
function use_automatic_payment_methods( $session_args = array() ) {
	if ( ! is_array( $session_args ) ) {
		$session_args = array();
	}

	/**
	 * Filters whether to use Stripe's automatic payment methods (Optimized
	 * Checkout Suite) instead of an explicit payment_method_types list.
	 *
	 * When enabled, the explicit payment_method_types parameter is removed
	 * from the Checkout Session, allowing Stripe to dynamically select and
	 * order payment methods for optimal conversion.
	 *
	 * The filter also runs outside of Checkout Session creation to report the
	 * effective value, such as in the form builder (where `$session_args`
	 * only contains `metadata.simpay_form_id`) and Site Health (where
	 * `$session_args` is empty).
	 *
	 * @since 4.17.6
	 *
	 * @param bool  $use_automatic Whether to use automatic payment methods.
	 *                             Defaults to the "Optimized Checkout" setting.
	 * @param array $session_args  The Checkout Session arguments.
	 */
	$use_automatic = apply_filters(
		'simpay_checkout_session_use_automatic_payment_methods',
		is_automatic_payment_methods_setting_enabled(),
		$session_args
	);

	// Treat the setting's own "no" value as disabled, in addition to falsy values.
	return $use_automatic && 'no' !== $use_automatic;
}
