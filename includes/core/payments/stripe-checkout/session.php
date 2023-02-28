<?php
/**
 * Stripe Checkout: Session
 *
 * @package SimplePay\Core\Payments\Stripe_Checkout\Sesssion
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Stripe_Checkout\Session;

use SimplePay\Core\API\CheckoutSessions;

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
 * @return \SimplePay\Vendor\Stripe\Checkout\Session
 */
function retrieve( $session, $api_request_args = array() ) {
	_deprecated_function(
		__FUNCTION__,
		'4.1.0',
		'\SimplePay\Core\API\CheckoutSessions\retrieve'
	);

	return CheckoutSessions\retrieve( $session, $api_request_args );
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
 * @return \SimplePay\Vendor\Stripe\Checkout\Session
 */
function create( $session_args = array(), $api_request_args = array() ) {
	_deprecated_function(
		__FUNCTION__,
		'4.7.0',
		'\SimplePay\Core\API\CheckoutSessions\create'
	);

	return CheckoutSessions\retrieve( $session_args, $api_request_args );
}
