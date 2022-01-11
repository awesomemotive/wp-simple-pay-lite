<?php
/**
 * API: PaymentMethods
 *
 * @package SimplePay\Core\API\PaymentMethods
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\API\PaymentMethods;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a PaymentMethod.
 *
 * @since 4.2.0
 *
 * @param string $payment_method_id Payment Method ID.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\PaymentMethod
 */
function retrieve( $payment_method_id, $api_request_args ) {
	return Stripe_API::request(
		'PaymentMethod',
		'retrieve',
		$payment_method_id,
		$api_request_args
	);
}

/**
 * Lists PaymentMethods.
 *
 * @since 4.1.0
 *
 * @param array $args Optional arguments used when listing PaymentMethods
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Collection
 */
function all( $args = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'PaymentMethod',
		'all',
		$args,
		$api_request_args
	);
}

/**
 * Attaches a PaymentMethod to a Customer.
 *
 * @since 4.2.0
 *
 * @param string $payment_method_id Payment Method ID.
 * @param string $customer_id Customer ID.
 * @param array  $api_request_args {
 *    Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\PaymentMethod
 */
function __experimental_attach( $payment_method_id, $customer_id, $api_request_args ) {
	$payment_method = retrieve( $payment_method_id, $api_request_args );
	$payment_method->attach(
		array(
			'customer' => $customer_id,
		)
	);

	return $payment_method;
}
