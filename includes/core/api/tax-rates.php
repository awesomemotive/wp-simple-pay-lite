<?php
/**
 * API: Tax Rates
 *
 * @package SimplePay\Core\API\Prices
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\API\TaxRates;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a Tax Rate.
 *
 * @since 4.1.0
 *
 * @param array $tax_rate_args Arguments used to create a Tax Rate.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\TaxRate
 */
function create( $tax_rate_args, $api_request_args = array() ) {
	return Stripe_API::request(
		'TaxRate',
		'create',
		$tax_rate_args,
		$api_request_args
	);
}

/**
 * Retrieves a Tax Rate.
 *
 * @since 4.1.0
 *
 * @param string $tax_rate_id Tax Rate ID.
 * @param array  $api_request_args {
 *    Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @param array  $opts Per-request options, default empty.
 * @return \SimplePay\Vendor\Stripe\TaxRate
 */
function retrieve( $tax_rate_id, $api_request_args = array(), $opts = array() ) {
	return Stripe_API::request(
		'TaxRate',
		'retrieve',
		$tax_rate_id,
		$api_request_args,
		$opts
	);
}

/**
 * Updates a Tax Rate.
 *
 * @since 4.1.0
 *
 * @param string $tax_rate_id ID of the Tax Rate to update.
 * @param array  $tax_rate_args Data to update Tax Rate with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\TaxRate $tax_rate Stripe Tax Rate.
 */
function update( $tax_rate_id, $tax_rate_args, $api_request_args = array() ) {
	return Stripe_API::request(
		'TaxRate',
		'update',
		$tax_rate_id,
		$tax_rate_args,
		$api_request_args
	);
}
