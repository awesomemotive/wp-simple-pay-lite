<?php
/**
 * API: Invoices
 *
 * @package SimplePay\Core\Payments\Invoices
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.11.0
 */

namespace SimplePay\Core\API\Invoices;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a Invoice.
 *
 * @since 4.11.0
 *
 * @param array $invoice_args Optional arguments used to create a invoice.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Invoice
 */
function create( $invoice_args, $api_request_args = array() ) {
	return Stripe_API::request(
		'Invoice',
		'create',
		$invoice_args,
		$api_request_args
	);
}

/**
 * Retrieves a Invoice.
 *
 * @since 4.11.0
 *
 * @param string|array{id: string} $invoice_id ID of the Invoice to retrieve.
 * @param array                    $api_request_args {
 *                        Additional request arguments to send to the Stripe API when making a request.
 *      @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Invoice
 */
function retrieve( $invoice_id, $api_request_args = array() ) {
	return Stripe_API::request(
		'Invoice',
		'retrieve',
		$invoice_id,
		$api_request_args
	);
}

/**
 * Retrieves all Invoices.
 *
 * @since 4.11.0
 *
 * @param array $args {
 *  Arguments used to retrieve all Invoices.
 *  @type int    $limit  Number of Invoices to retrieve.
 *  @type string $ending_before ID of the Invoice to retrieve Invoices before.
 *  @type string $starting_after ID of the Invoice to retrieve Invoices after.
 *  @type string $customer ID of the Customer to retrieve Invoices for.
 *  @type string $subscription ID of the Subscription to retrieve Invoices for.
 *  @type string $subscription_plan ID of the Subscription Plan to retrieve Invoices for.
 *  @type string $subscription_status Status of the Subscription to retrieve Invoices for.
 *  @type string $status Status of the Invoice to retrieve.
 *  @type string $collection_method Collection method of the Invoice to retrieve.
 *  @type string $created Date the Invoice was created.
 *  @type string $due_date Date the Invoice is due.
 *  @type string $paid Date the Invoice was paid.
 *  @type string $period_end Date the Invoice period ended.
 *  @type string $period_start Date the Invoice period started.
 *  @type string $subscription_proration_behavior Proration behavior of the Subscription to retrieve Invoices for.
 * }
 * @param array $api_request_args {
 * Additional request arguments to send to the Stripe API when making a request.
 * @type string $api_key API Secret Key to use.
 * }
 * @param array $opts Per-request options, default empty.
 * @return \SimplePay\Vendor\Stripe\Collection
 */
function all( $args, $api_request_args = array(), $opts = array() ) {
	return Stripe_API::request(
		'Invoice',
		'all',
		$args,
		$api_request_args,
		$opts
	);
}
