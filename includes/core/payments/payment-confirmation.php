<?php
/**
 * Payment confirmation
 *
 * @package SimplePay\Core\Payments\Payment_Confirmation
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Payment_Confirmation;

use SimplePay\Core\API;
use SimplePay\Core\Emails;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs action(s) related to viewing a payment confirmation.
 *
 * @since 4.6.4
 *
 * @param array $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer                  $customer
 *                                                            Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form                     $form
 *                                                            Payment form.
 *   @type array<\SimplePay\Core\Vendor\Stripe\Subscription>  $subscriptions
 *                                                            The Customer's latest Subscriptions (limit 1).
 *   @type array<\SimplePay\Core\Vendor\Stripe\PaymentIntent> $paymentintents
 *                                                            The Customer's latest PaymentIntents (limit 1).
 * }
 * @return void
 */
function do_confirmation_actions( $payment_confirmation_data ) {
	/**
	 * Internal hook to allow legacy hooks that rely on "complete" payments.
	 *
	 * @since 3.6.0
	 *
	 * @param array $payment_confirmation_data {
	 *   Contextual information about this payment confirmation.
	 *
	 *   @type \SimplePay\Vendor\Stripe\Customer                  $customer
	 *                                                            Stripe Customer
	 *   @type \SimplePay\Core\Abstracts\Form                     $form
	 *                                                            Payment form.
	 *   @type array<\SimplePay\Core\Vendor\Stripe\Subscription>  $subscriptions
	 *                                                            The Customer's latest Subscriptions (limit 1).
	 *   @type array<\SimplePay\Core\Vendor\Stripe\PaymentIntent> $paymentintents
	 *                                                            The Customer's latest PaymentIntents (limit 1).
	 * }
	 * @param \SimplePay\Core\Abstracts\Form                      $form Payment form.
	 * @param array<string, mixed>                                $_GET $_GET data.
	 */
	do_action(
		'_simpay_payment_confirmation',
		$payment_confirmation_data,
		$payment_confirmation_data['form'],
		$_GET
	);

	if ( ! empty( $payment_confirmation_data['paymentintents'] ) ) {
		$objects     = $payment_confirmation_data['paymentintents'];
		$object_type = 'payment_intent';
	} else {
		$objects     = $payment_confirmation_data['subscriptions'];
		$object_type = 'subscription';
	}

	if ( empty( $objects ) ) {
		return;
	}

	// Retrieve the most current object associated with the Customer.
	//
	// @todo In the future, if multiple purchases can be associated with a single
	// Customer, this will need to pull a more specific item to check.
	$object = current( $objects );

	// Flag/action used to ensure this only runs once.
	$flag = 'simpay_payment_receipt_viewed';

	// Do nothing if the object has already been tracked as viewed.
	if ( isset( $object->metadata->$flag ) ) {
		return;
	}

	// Update the purchase object, marking it as viewed.
	$update = array(
		'metadata' => array(
			$flag => time(),
		),
	);

	switch ( $object_type ) {
		case 'payment_intent':
			API\PaymentIntents\update(
				$object->id,
				$update,
				$payment_confirmation_data['form']->get_api_request_args()
			);
			break;

		case 'subscription':
			API\Subscriptions\update(
				$object->id,
				$update,
				$payment_confirmation_data['form']->get_api_request_args()
			);
			break;
	}

	/**
	 * Performs an action the first time a payment receipt is viewed. This will
	 * not be called when a payment receipt for a specific purchase is visited again.
	 *
	 * @since 4.6.4
	 *
	 * @param array $payment_confirmation_data {
	 *   Contextual information about this payment confirmation.
	 *
	 *   @type \SimplePay\Vendor\Stripe\Customer                  $customer
	 *                                                            Stripe Customer
	 *   @type \SimplePay\Core\Abstracts\Form                     $form
	 *                                                            Payment form.
	 *   @type array<\SimplePay\Core\Vendor\Stripe\Subscription>  $subscriptions
	 *                                                            Subscriptions associated with the Customer.
	 *   @type array<\SimplePay\Core\Vendor\Stripe\PaymentIntent> $paymentintents
	 *                                                            PaymentIntents associated with the Customer.
	 * }
	 */
	do_action( $flag, $payment_confirmation_data );
}

/**
 * Retrieves relevant payment confirmation data from a \SimplePay\Vendor\Stripe\Checkout\Session
 * or \SimplePay\Vendor\Stripe\Customer ID.
 *
 * @since 3.6.6
 * @since 4.0.0 Allow a Payment Form ID to be explicitely set.
 *
 * @param bool|string $customer_id Customer ID to retrieve. Default false.
 * @param bool|string $session_id Session ID to retrieve. Default false.
 * @param bool|int    $form_id Form ID. Default false.
 * @return array {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer                  $customer
 *                                                            Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form                     $form
 *                                                            Payment form.
 *   @type array<\SimplePay\Core\Vendor\Stripe\Subscription>  $subscriptions
 *                                                            Subscriptions associated with the Customer.
 *   @type array<\SimplePay\Core\Vendor\Stripe\PaymentIntent> $paymentintents
 *                                                            PaymentIntents associated with the Customer.
 * }
 */
function get_confirmation_data( $customer_id = false, $session_id = false, $form_id = false ) {
	$payment_confirmation_data = array();

	// Find the used Payment Form via the URL.
	$form_id = isset( $_GET['form_id'] )
		? absint( $_GET['form_id'] )
		: $form_id;

	if ( false === $form_id ) {
		return $payment_confirmation_data;
	}

	/**
	 * Filters the ID of the form to retrieve.
	 *
	 * @since 3.6.2
	 *
	 * @param int   $form_id ID of the form the payment was created from.
	 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
	 */
	$form_id = apply_filters(
		'simpay_payment_confirmation_form_id',
		$form_id,
		$payment_confirmation_data
	);

	$form = simpay_get_form( $form_id );

	if ( false === $form ) {
		return $payment_confirmation_data;
	}

	// Attach form to confirmation data.
	$payment_confirmation_data['form'] = $form;

	// Check for a Checkout Session ID or Customer ID.
	// This area blurs the lines between Lite/Pro code since Lite can only use Checkout Session.
	$session_id  = isset( $_GET['session_id'] ) ? esc_attr( $_GET['session_id'] ) : $session_id;
	$customer_id = isset( $_GET['customer_id'] ) ? esc_attr( $_GET['customer_id'] ) : $customer_id;

	// Do nothing if we can't find a Checkout Session or Customer to reference.
	if ( ! ( $session_id || $customer_id ) ) {
		return $payment_confirmation_data;
	}

	$is_cache = ! isset( $_GET['subscription_key'] );

	// Using the available identifier, find the relevant customer.
	if ( $session_id ) {
		$session = API\CheckoutSessions\retrieve(
			array(
				'id'     => $session_id,
				'expand' => array(
					'customer',
					'line_items',
				),
			),
			$form->get_api_request_args(),
			array(
				'cached' => $is_cache,
			)
		);

		$payment_confirmation_data['checkout_session'] = $session;

		$customer = $session->customer;
	} else {
		$customer = API\Customers\retrieve(
			array(
				'id' => $customer_id,
			),
			$form->get_api_request_args(),
			array(
				'cached' => $is_cache,
			)
		);
	}

	if ( $customer ) {
		$payment_confirmation_data['customer'] = $customer;
	}

	// Retrieve the PaymentIntent the Customer is linked to.
	$paymentintents = API\PaymentIntents\all(
		array(
			'customer' => $customer->id,
			'limit'    => 1,
		),
		$form->get_api_request_args(),
		array(
			'cached' => $is_cache,
		)
	);

	if ( $paymentintents ) {
		$payment_confirmation_data['paymentintents'] = $paymentintents->data;
	}

	/**
	 * Filters the payment confirmation data.
	 *
	 * @since 3.6.0
	 *
	 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
	 */
	$payment_confirmation_data = apply_filters( 'simpay_payment_confirmation_data', $payment_confirmation_data );

	return $payment_confirmation_data;
}

/**
 * Returns the default Payment Confirmation message for "One-Time Amount" payments.
 *
 * @since 4.0.0
 *
 * @return string
 */
function get_one_time_amount_message_default() {
	$license   = simpay_get_license();
	$is_lite   = $license->is_lite();
	$has_email = false;

	if ( false === $is_lite ) {
		$email = new Emails\Email\PaymentConfirmationEmail();

		$has_email = $email->is_enabled();
	}

	$message = '';

	if ( true === $has_email ) {
		$message = sprintf(
			/* translators: %s: Total amount */
			esc_html__( 'Thank you. Your payment of %s has been received. Please check your email for additional information.', 'stripe' ),
			'{total-amount}'
		);
	} else {
		$message = sprintf(
			/* translators: %s: Total amount */
			esc_html__( 'Thank you. Your payment of %s has been received.', 'stripe' ),
			'{total-amount}'
		);

	}

	return $message;
}

/**
 * Retrieves the default confirmation content set in Settings > Payment Confirmation.
 *
 * @since 3.6.0
 *
 * @return string
 */
function get_content() {
	$content = simpay_get_setting(
		'one_time_payment_details',
		get_one_time_amount_message_default()
	);

	$display_options = array();

	/**
	 * Filters the Payment Confirmation editor content.
	 *
	 * @since 3.0.0
	 * @deprecated 3.6.0
	 *
	 * @param string $content Editor content.
	 * @param string $type Editor type. one_time, subscription, or trial.
	 * @param array  $display_options Content display options.
	 */
	$content = apply_filters_deprecated(
		'simpay_get_editor_content',
		array(
			$content,
			'one_time',
			$display_options,
		),
		'3.6.0',
		'simpay_payment_confirmation_content'
	);

	return $content;
}

/**
 * Creates a generic error message shown when the confirmation page is
 * reached but the relevant records are unable to be retrieved.
 *
 * @since 3.6.0
 *
 * @return string
 */
function get_error() {
	$message = wpautop( esc_html__( 'Unable to locate payment record.', 'stripe' ) );

	/**
	 * Filter the error message shown when a Payment Confirmation cannot be created.
	 *
	 * @since unknown
	 *
	 * @param string Error message.
	 */
	return apply_filters( 'simpay_charge_error_message', $message );
}
