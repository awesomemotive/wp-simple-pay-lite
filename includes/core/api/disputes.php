<?php
/**
 * API: Disputes
 *
 * @package SimplePay
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\API\Disputes;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a list of Disputes.
 *
 * @since 4.17.4
 *
 * @param array $disputes Optional arguments used to retrieve a list of Disputes.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @param array $opts Per-request options.
 * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Dispute>
 */
function all( $disputes = array(), $api_request_args = array(), $opts = array() ) {
	return Stripe_API::request(
		'Dispute',
		'all',
		$disputes,
		$api_request_args,
		$opts
	);
}

/**
 * Retrieves a Dispute.
 *
 * @since 4.17.4
 *
 * @param string|array $dispute Dispute ID or {
 *   Arguments used to retrieve a Dispute.
 *
 *   @type string $id Dispute ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Dispute
 */
function retrieve( $dispute, $api_request_args = array() ) {
	return Stripe_API::request(
		'Dispute',
		'retrieve',
		$dispute,
		$api_request_args
	);
}
