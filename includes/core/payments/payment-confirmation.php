<?php
/**
 * Payment confirmation
 *
 * @package SimplePay\Core\Payments\Payment_Confirmation
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Payment_Confirmation;

use SimplePay\Core\Forms\Default_Form;
use SimplePay\Core\Payments\Stripe_Checkout\Session;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Payments\Customer;
use SimplePay\Core\Payments\PaymentIntent;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves relevant payment confirmation data from a \Stripe\Checkout\Session
 * or \Stripe\Customer ID.
 *
 * @since 3.6.6
 *
 * @param bool|string $customer_id Customer ID to retrieve. Default false.
 * @param bool|string $session_id Session ID to retrieve. Default false.
 * @return array $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type \Stripe\Subscription[]         $subscriptions Subscriptions associated with the Customer.
 *   @type \Stripe\PaymentIntent[]        $paymentintents PaymentIntents associated with the Customer.
 * }
 */
function get_confirmation_data( $customer_id = false, $session_id = false ) {
	$payment_confirmation_data = array();

	// Find the used Payment Form via the URL
	$form_id = isset( $_GET['form_id'] )
		? absint( $_GET['form_id'] )
		: false;

	/**
	 * Filters the ID of the form to retrieve.
	 *
	 * @since 3.6.2
	 *
	 * @param int   $form_id ID of the form the payment was created from.
	 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
	 */
	$form_id = apply_filters( 'simpay_payment_confirmation_form_id', $form_id, $payment_confirmation_data );

	if ( false === $form_id ) {
		return $payment_confirmation_data;
	}

	/** This filter is documented in includes/core/shortcodes.php */
	$form = apply_filters( 'simpay_form_view', '', $form_id );

	if ( empty( $form ) ) {
		$form = new Default_Form( $form_id );
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

	// Using the available identifier, find the relevant customer.
	if ( $session_id ) {
		$session = Session\retrieve(
			array(
				'id'     => $session_id,
				'expand' => array(
					'customer',
				),
			),
			$form->get_api_request_args()
		);

		$customer = $session->customer;
	} else {
		$customer = Customer\retrieve(
			$customer_id,
			$form->get_api_request_args()
		);
	}

	if ( $customer ) {
		$payment_confirmation_data['customer'] = $customer;
	}

	// Retrieve the PaymentIntent the Customer is linked to.
	$paymentintents = PaymentIntent\all(
		array(
			'customer' => $customer->id,
			'limit'    => 1,
			'expand'   => array(
				'data.payment_method',
			),
		),
		$form->get_api_request_args()
	);

	if ( $paymentintents ) {
		$payment_confirmation_data['paymentintents'] = $paymentintents->data;
	}

	/**
	 * Filters the payment confirmation data.
	 *
	 * @since 3.6.0
	 *
	 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
	 */
	$payment_confirmation_data = apply_filters( 'simpay_payment_confirmation_data', $payment_confirmation_data );

	return $payment_confirmation_data;
}

/**
 * Retrieves the default confirmation content set in Settings > Payment Confirmation.
 *
 * @since 3.6.0
 *
 * @param string $confirmation_type The type of confirmation content to retrieve.
 * @return string
 */
function get_content() {
	$display_options = get_option( 'simpay_settings_display' );
	$content         = simpay_get_editor_default( 'one_time' );

	if ( ! $display_options ) {
		return $content;
	}

	$content = isset( $display_options['payment_confirmation_messages']['one_time_payment_details'] ) ?
		$display_options['payment_confirmation_messages']['one_time_payment_details'] :
		$content;

	/**
	 * @deprecated 3.6.0
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
