<?php
/**
 * Stripe Customer
 *
 * @package SimplePay\Core\Payments\Customer
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Core\Payments\Customer;

use SimplePay\Core\Legacy;
use SimplePay\Core\Payments\Stripe_API;

use function SimplePay\Core\SimplePay;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Customer.
 *
 * @since 3.7.0
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
 * @return \Stripe\Customer
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
 * @since 3.6.0
 *
 * @param array $customer_args Optional arguments used to create a customer.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \Stripe\Customer
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
	 * Filters any existing \Stripe\Customer record, allowing for
	 * creation to be overridden or stopped.
	 *
	 * @since 3.6.6
	 *
	 * @param null  $customer Existing \Stripe\Customer record.
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

	if ( false === is_a( $customer, '\Stripe\Customer' ) ) {
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
	 * @param \Stripe\Customer $customer Customer.
	 */
	do_action( 'simpay_after_customer_created', $customer );

	return $customer;
}

/**
 * Updates a Customer record.
 *
 * @since 3.7.0
 *
 * @param string $customer_id ID of the Customer to update.
 * @param array  $customer_args Data to update Customer with.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \Stripe\Customer $customer Stripe Customer.
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
	 * @param \Stripe\Customer $customer Customer.
	 */
	do_action( 'simpay_after_customer_updated', $customer );

	return $customer;
}

/**
 * Deletes a Customer record.
 *
 * @since 3.8.0
 *
 * @param string $customer_id ID of the Customer to update.
 * @param array  $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \Stripe\Customer $customer Stripe Customer.
 */
function delete( $customer_id, $api_request_args = array() ) {
	$customer = retrieve( $customer_id, $api_request_args );
	$customer = $customer->delete();

	/**
	 * Allows further processing after a Customer has been deleted.
	 *
	 * @since 3.8.0
	 *
	 * @param \Stripe\Customer $customer Customer.
	 */
	do_action( 'simpay_after_customer_deleted', $customer );

	return $customer;
}

/**
 * Retrieves Customers.
 *
 * @since 3.9.0
 *
 * @param array $args Optional arguments used when listing Customers.
 * @param array $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \Stripe\Customers[]
 */
function all( $args = array(), $api_request_args = array() ) {
	return Stripe_API::request(
		'Customer',
		'all',
		$args,
		$api_request_args
	);
}

/**
 * Uses the current payment form request to generate arguments for a Customer.
 *
 * @since 3.6.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @return array
 */
function get_args_from_payment_form_request( $form, $form_data, $form_values ) {
	$customer_args = array();

	$defaults = array(
		'name'     => null,
		'phone'    => null,
		'email'    => null,
		'metadata' => null,
	);

	$customer_args = wp_parse_args( $customer_args, $defaults );

	// Attach coupon to metadata.
	if ( isset( $form_values['simpay_coupon'] ) && ! empty( $form_values['simpay_coupon'] ) ) {
		$customer_args['coupon'] = sanitize_text_field( $form_values['simpay_coupon'] );
	}

	// Attach email.
	if ( isset( $form_values['simpay_email'] ) ) {
		if ( is_array( $form_values['simpay_email'] ) ) {
			$form_values['simpay_email'] = end( $form_values['simpay_email'] );
		}

		$customer_args['email'] = sanitize_text_field( $form_values['simpay_email'] );
	}

	// Attach name.
	if ( isset( $form_values['simpay_customer_name'] ) ) {
		if ( is_array( $form_values['simpay_customer_name'] ) ) {
			$form_values['simpay_customer_name'] = end( $form_values['simpay_customer_name'] );
		}

		$customer_args['name'] = sanitize_text_field( $form_values['simpay_customer_name'] );
	}

	// Attach phone number.
	if ( isset( $form_values['simpay_telephone'] ) ) {
		$customer_args['phone'] = sanitize_text_field( $form_values['simpay_telephone'] );
	}

	// Attach billing address.
	$billing_address = simpay_get_form_address_data( 'billing', $form_values );

	if ( $billing_address && ! empty( $billing_address ) ) {
		$customer_args['address'] = $billing_address;
	}

	// Remove null values, Stripe doesn't like them.
	// Do this before Shipping, because we need a value for Shipping Name.
	$customer_args = array_filter(
		$customer_args,
		function( $var ) {
			return ! is_null( $var );
		}
	);

	// Attach shipping address.
	$field            = isset( $form_values['simpay_same_billing_shipping'] ) ? 'billing' : 'shipping';
	$shipping_address = simpay_get_form_address_data( $field, $form_values );

	if ( $shipping_address && ! empty( $shipping_address ) ) {
		$customer_args['shipping']['address'] = $shipping_address;
		$customer_args['shipping']['name']    = isset( $customer_args['name'] ) ? $customer_args['name'] : '';
		$customer_args['shipping']['phone']   = isset( $customer_args['phone'] ) ? $customer_args['phone'] : '';
	}

	// Handle legacy filter.
	$customer_args = Legacy\Hooks\simpay_stripe_customer_args( $customer_args, $form, $form_values );

	/**
	 * Filters arguments used to create a Customer from a payment form request.
	 *
	 * @since 3.6.0
	 *
	 * @param array                         $customer_args
	 * @param SimplePay\Core\Abstracts\Form $form Form instance.
	 * @param array                         $form_data Form data generated by the client.
	 * @param array                         $form_values Values of named fields in the payment form.
	 * @return array
	 */
	$customer_args = apply_filters(
		'simpay_get_customer_args_from_payment_form_request',
		$customer_args,
		$form,
		$form_data,
		$form_values
	);

	return $customer_args;
}
