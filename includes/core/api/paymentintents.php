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
