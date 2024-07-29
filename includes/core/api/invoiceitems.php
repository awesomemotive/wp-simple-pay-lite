<?php
/**
 * API: Invoice Items
 *
 * @package SimplePay\Core\Payments\Invoices
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.11.0
 */

namespace SimplePay\Core\API\InvoiceItems;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a InvoiceItem.
 *
 * @since 4.11.0
 *
 * @param array $invoice_items Optional arguments used to create a invoice item.
 * @param array $api_request_args {
 *  Additional request arguments to send to the Stripe API when making a request.
 *
 *  @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\InvoiceItem
 */
function create( $invoice_items, $api_request_args = array() ) {
	return Stripe_API::request(
		'InvoiceItem',
		'create',
		$invoice_items,
		$api_request_args
	);
}

/**
 * Retrieves a InvoiceItem.
 *
 * @since 4.11.0
 *
 * @param string|array $invoice_item Invoice Item ID or {
 *   Arguments used to retrieve a InvoiceItem.
 *
 *   @type string $id Invoice Item ID.
 * }
 * @param array        $api_request_args {
 *  Additional request arguments to send to the Stripe API when making a request.
 *
 *  @type string $api_key API Secret Key to use.
 * }
 * @param array<mixed> $opts Per-request options, default empty.
 * @return \SimplePay\Vendor\Stripe\InvoiceItem
 */
function retrieve( $invoice_item, $api_request_args = array(), $opts = array() ) {
	if ( false === is_array( $invoice_item ) ) {
		$invoice_item_args = array(
			'id' => $invoice_item,
		);
	} else {
		$invoice_item_args = $invoice_item;
	}

	return Stripe_API::request(
		'InvoiceItem',
		'retrieve',
		$invoice_item_args,
		$api_request_args,
		$opts
	);
}
