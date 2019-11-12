<?php
/**
 * Shim legacy hooks.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Legacy\Hooks;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Payments\Payment;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle legacy `simpay_stripe_customer_args` filter.
 *
 * Maps $_POST to $form_values
 *
 * @since 3.6.0
 *
 * @param array                         $customer_args Arguments for Customer.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_values POSTed form values that can be shimmed in to $_POST.
 * @return array
 */
function simpay_stripe_customer_args( $customer_args, $form, $form_values ) {
	if ( ! has_filter( 'simpay_stripe_customer_args' ) ) {
		return $customer_args;
	}

	// Save old $_POST.
	$post_vars = $_POST;

	// Shim $_POST so existing actions have access to the same values.
	$_POST = $form_values;

	// Make form available.
	global $simpay_form;
	$simpay_form = $form;

	$customer = new \stdClass();

	/**
	 * Filters the arguments passed to customer creation in Stripe.
	 *
	 * @since 3.5.0
	 * @since 3.6.0 $customer object is empty.
	 *
	 * @param array $customer_args Arguments passed to customer creation in Stripe.
	 * @param object $customer
	 */
	$customer_args = apply_filters( 'simpay_stripe_customer_args', $customer_args, $customer );

	// Reset.
	$_POST = $post_vars;
	unset( $simpay_form );
	unset( $post_vars );

	return $customer_args;
}

/**
 * Handle legacy `simpay_stripe_charge_args` filter.
 *
 * Charges are not created anymore, use PaymentIntent arguments.
 *
 * @since 3.6.0
 *
 * @param array                         $paymentintent_args Arguments for PaymentIntent.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_values POSTed form values that can be shimmed in to $_POST.
 * @return array
 */
function simpay_stripe_charge_args( $paymentintent_args, $form, $form_values ) {
	if ( ! has_filter( 'simpay_stripe_charge_args' ) ) {
		return $paymentintent_args;
	}

	$charge          = new \stdClass();
	$charge->payment = new Payment( $form );

	// Save old $_POST.
	$post_vars = $_POST;

	// Shim $_POST so existing actions have access to the same values.
	$_POST = $form_values;

	// Make form available.
	global $simpay_form;
	$simpay_form = $form;

	/**
	 * Filter the arguments passed to charge creation in Stripe.
	 *
	 * @since 3.5.0
	 *
	 * @param array   $charge_args Arguments passed to charge creation in Stripe.
	 * @param Payment $charge Payment object.
	 */
	$charge_args = apply_filters( 'simpay_stripe_charge_args', $paymentintent_args, $charge );

	$paymentintent_args = wp_parse_args( $charge_args, $paymentintent_args );

	// Reset.
	$_POST = $post_vars;
	unset( $simpay_form );
	unset( $post_vars );

	return $paymentintent_args;
}

/**
 * Handle legacy `simpay_charge_created` hook.
 *
 * Finds the first charge in the PaymentIntent to mock the previous arguments.
 *
 * @since 3.6.0
 *
 * @param \Stripe\PaymentIntent         $paymentintent Stripe PaymentIntent.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_values POSTed form values that can be shimmed in to $_POST.
 */
function simpay_charge_created( $paymentintent, $form, $form_values ) {
	if ( ! has_action( 'simpay_charge_created' ) ) {
		return;
	}

	// Retrieve the first charge so the action can be called to maintain compatibility.
	$charges = $paymentintent->charges;
	$charge  = current( $charges->data );

	// Save old $_POST.
	$post_vars = $_POST;

	// Shim $_POST so existing actions have access to the same values.
	$_POST = $form_values;

	// Make form available.
	global $simpay_form;
	$simpay_form = $form;

	/**
	 * Allow processing to happen when a charge is created.
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 *
	 * @param Stripe\Charge $charge First charge (now associated with a PaymentIntent).
	 * @param array         $metadata
	 */
	do_action( 'simpay_charge_created', $charge, $paymentintent->metadata );

	// Reset.
	$_POST = $post_vars;
	unset( $simpay_form );
	unset( $post_vars );
}

/**
 * Accesses a payment confirmation's data to run the legacy `simpay_charge_created` hook.
 *
 * New implementations should use Webhooks to verify that action is only taken
 * when an object reaches the proper status.
 *
 * @since 3.6.0
 *
 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
 */
function _transform_payment_confirmation_for_legacy_charge( $payment_confirmation_data, $form, $form_values ) {
	if ( ! has_action( 'simpay_charge_created' ) ) {
		return;
	}

	if ( ! isset( $payment_confirmation_data['paymentintents'] ) ) {
		return;
	}

	if ( $form->is_subscription() ) {
		return;
	}

	$payment_intent = current( $payment_confirmation_data['paymentintents'] );

	if ( $payment_intent ) {
		simpay_charge_created( $payment_intent, $form, $form_values );
	}
}
add_action( '_simpay_payment_confirmation', __NAMESPACE__ . '\\_transform_payment_confirmation_for_legacy_charge', 10, 3 );

/**
 * Handle legacy `simpay_payment_metadata` filter.
 *
 * @since 3.6.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @param string                        $customer_id Stripe Customer ID.
 * @return array Empty array.
 */
function simpay_payment_metadata( $form, $form_data, $form_values, $customer_id ) {
	$metadata = array();

	if ( ! has_filter( 'simpay_payment_metadata' ) ) {
		return $metadata;
	}

	/**
	 * Filter the metadata attached to a Paymen.
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 *
	 * @param array $metadata Current metadata.
	 */
	$metadata = apply_filters( 'simpay_payment_metadata', $metadata );

	return $metadata;
}

/**
 * Handle legacy `simpay_payment_description` filter.
 *
 * @since 3.6.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @return string
 */
function simpay_payment_description( $form, $form_data, $form_values, $customer_id ) {
	$description = $form->item_description;

	// Remove blank values.
	if ( '' === $description ) {
		$description = null;
	}

	if ( ! has_filter( 'simpay_payment_description' ) ) {
		return $description;
	}

	/**
	 * Filter the PaymentIntent description.
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 *
	 * @param array $description Current description.
	 */
	$description = apply_filters( 'simpay_payment_description', $description );

	// Remove blank values.
	if ( '' === $description ) {
		$description = null;
	}

	return $description;
}

/**
 * Handle legacy `simpay_pre_process_form` hook.
 *
 * Called in REST API requests to mimic the original form processing.
 * Avoid using `do_action_deprecated` to avoid notices breaking AJAX response.
 *
 * @since 3.6.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @param string                        $customer_id Stripe Customer ID.
 */
function simpay_pre_process_form( $form, $form_data, $form_values ) {
	if ( ! has_action( 'simpay_pre_process_form' ) ) {
		return;
	}

	$post_vars = $_POST;

	// Shim $_POST so existing actions have access to the same values.
	$_POST = $form_values;

	global $simpay_form;
	$simpay_form = $form;

	/**
	 * Allow further action before a form is processed.
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 */
	do_action( 'simpay_pre_process_form' );

	// Reset.
	$_POST = $post_vars;
	unset( $simpay_form );
	unset( $post_vars );
}

/**
 * Handle legacy `simpay_process_form` hook.
 *
 * Called in REST API requests to mimic the original form processing.
 * Avoid using `do_action_deprecated` to avoid notices breaking AJAX response.
 *
 * @since 3.6.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @param string                        $customer_id Stripe Customer ID.
 */
function simpay_process_form( $form, $form_data, $form_values, $customer_id ) {
	if ( ! has_action( 'simpay_process_form' ) ) {
		return;
	}

	$customer = Stripe_API::request( 'Customer', 'retrieve', $customer_id );

	$amount = isset( $form_values['simpay_amount'] )
		? $form_values['simpay_amount']
		: simpay_convert_amount_to_cents( $form->amount );

	$payment              = new Payment( $form );
	$payment->customer    = $customer;
	$payment->customer_id = $customer->id;
	$payment->email       = $customer->email;
	$payment->email       = $customer->email;
	$payment->amount      = $amount;

	/**
	 * Allow further processing when a Payment Form is processing.
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 *
	 * @param \SimplePay\Core\Payments\Payment
	 */
	do_action( 'simpay_process_form', $payment );

	// Handle metadata that might be added here.
	// @link https://github.com/wpsimplepay/WP-Simple-Pay-Snippet-Library/blob/master/add-payment-metadata.php
	if ( $payment->metadata && is_array( $payment->metadata ) ) {
		$type = $form->is_subscription() || isset( $form_data['isRecurring'] ) ? 'subscription' : 'paymentintent';

		/** This filter is documented in includes/core/payments/paymentintent.php */
		add_filter( "simpay_get_{$type}_args_from_payment_form_request", function( $object_args ) use ( $payment ) {
			$object_args['metadata'] = array_merge( $object_args['metadata'], $payment->metadata );

			return $object_args;
		} );
	}
}
