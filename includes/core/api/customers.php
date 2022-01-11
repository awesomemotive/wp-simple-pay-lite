<?php
/**
 * API: Customers
 *
 * @package SimplePay\Core\API\Customers
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\API\Customers;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Customer.
 *
 * @since 4.1.0
 *
 * @param string|array $customer Customer ID or {
 *   Arguments used to retrieve a Customer.
 *
 *   @type string $id Customer ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Customer
 */
function retrieve( $customer, $api_request_args = array() ) {
	if ( false === is_array( $customer ) ) {
		$customer_args = array(
			'id' => $customer,
		);
	} else {
		$customer_args = $customer;
	}

	return Stripe_API::request(
		'Customer',
		'retrieve',
		$customer_args,
		$api_request_args
	);
}

/**
 * Creates a Customer record.
 *
 * @since 4.1.0
 *
 * @param array $customer_args Optional arguments used to create a customer.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Customer
 */
function create( $customer_args = array(), $api_request_args = array() ) {
	$defaults      = array();
	$customer_args = wp_parse_args( $customer_args, $defaults );

	/**
	 * Filters the arguments passed to customer creation in Stripe.
	 *
	 * @since 3.6.0
	 *
	 * @param array $customer_args Arguments passed to customer creation in Stripe.
	 */
	$customer_args = apply_filters( 'simpay_create_customer_args', $customer_args );

	/**
	 * Allows processing before a Customer is created.
	 *
	 * @since 3.6.0
	 *
	 * @param array $customer_args Arguments used to create a Customer.
	 */
	do_action( 'simpay_before_customer_created', $customer_args );

	/**
	 * Filters any existing \SimplePay\Vendor\Stripe\Customer record, allowing for
	 * creation to be overridden or stopped.
	 *
	 * @since 3.6.6
	 *
	 * @param null  $customer Existing \SimplePay\Vendor\Stripe\Customer record.
	 * @param array $customer_args Arguments used to create a Customer.
	 * @param array $api_request_args {
	 *   Additional request arguments to send to the Stripe API when making a request.
	 *
	 *   @type string $api_key API Secret Key to use.
	 * }
	 */
	$customer = apply_filters(
		'simpay_customer_create',
		null,
		$customer_args,
		$api_request_args
	);

	if ( false === is_a( $customer, '\SimplePay\Vendor\Stripe\Customer' ) ) {
		$customer = Stripe_API::request(
			'Customer',
			'create',
			$customer_args,
			$api_request_args
		);
	}

	/**
	 * Allows further processing after a Customer has been created.
	 *
	 * @since 3.6.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Customer $customer Customer.
	 */
	do_action( 'simpay_after_customer_created', $customer );

	return $customer;
}

/**
 * Updates a Customer record.
 *
 * @since 4.1.0
 *
 * @param string $customer_id ID of the Customer to update.
 * @param array  $customer_args Data to update Customer with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Customer $customer Stripe Customer.
 */
function update( $customer_id, $customer_args, $api_request_args = array() ) {
	/**
	 * Filters the arguments passed to customer creation in Stripe.
	 *
	 * @since 3.7.0
	 *
	 * @param array $customer_args Arguments passed to customer creation in Stripe.
	 * @param string $customer_id ID of the Customer to update.
	 */
	$customer_args = apply_filters(
		'simpay_update_customer_args',
		$customer_args,
		$customer_id
	);

	$customer = Stripe_API::request(
		'Customer',
		'update',
		$customer_id,
		$customer_args,
		$api_request_args
	);

	/**
	 * Allows further processing after a Customer has been updated.
	 *
	 * @since 3.7.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Customer $customer Customer.
	 */
	do_action( 'simpay_after_customer_updated', $customer );

	return $customer;
}

/**
 * Deletes a Customer record.
 *
 * @since 4.1.0
 *
 * @param string $customer_id ID of the Customer to update.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Customer $customer Stripe Customer.
 */
function delete( $customer_id, $api_request_args = array() ) {
	$customer = retrieve( $customer_id, $api_request_args );
	$customer = $customer->delete();

	/**
	 * Allows further processing after a Customer has been deleted.
	 *
	 * @since 3.8.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Customer $customer Customer.
	 */
	do_action( 'simpay_after_customer_deleted', $customer );

	return $customer;
}

/**
 * Retrieves Customers.
 *
 * @since 4.1.0
 *
 * @param array $args Optional arguments used when listing Customers.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return array
 */
function all( $args = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'Customer',
		'all',
		$args,
		$api_request_args
	);
}
