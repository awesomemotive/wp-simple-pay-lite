<?php
/**
 * API: Subscription Items
 *
 * @package SimplePay\Core\Payments\Invoices
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.11.0
 */

namespace SimplePay\Core\API\SubscriptionItems;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a SubscriptionItem.
 *
 * @since 4.11.0
 *
 * @param array<mixed> $subscription_item Optional arguments used to create a Subscription Item.
 * @param array<mixed> $api_request_args API request arguments.
 * @param array<mixed> $opts Per-request options, default empty.
 * @return \SimplePay\Vendor\Stripe\InvoiceItem
 */
function create( $subscription_item, $api_request_args = array(), $opts = array() ) {
	return Stripe_API::request(
		'SubscriptionItem',
		'create',
		$subscription_item,
		$api_request_args,
		$opts
	);
}

/**
 * Creates a SubscriptionItem.
 *
 * @since 4.11.0
 *
 * @param string|array $subscription_item Subscription Item ID or {
 *   Arguments used to retrieve a Subscription Item.
 *
 *   @type string $id SUbscription Item ID.
 * }
 * @param array        $api_request_args {
 *  Additional request arguments to send to the Stripe API when making a request.
 *
 *  @type string $api_key API Secret Key to use.
 * }
 * @param array<mixed> $opts Per-request options, default empty.
 * @return \SimplePay\Vendor\Stripe\SubscriptionItem
 */
function retrieve( $subscription_item, $api_request_args = array(), $opts = array() ) {
	if ( false === is_array( $subscription_item ) ) {
		$subscription_item_args = array(
			'id' => $subscription_item,
		);
	} else {
		$subscription_item_args = $subscription_item;
	}

	return Stripe_API::request(
		'SubscriptionItem',
		'retrieve',
		$subscription_item_args,
		$api_request_args,
		$opts
	);
}
