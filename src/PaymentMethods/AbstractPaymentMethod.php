<?php
/**
 * Abstract class for payment methods.
 *
 * @since 4.16.0
 * @package SimplePay\Core\PaymentMethods
 */

namespace SimplePay\Core\PaymentMethods;

use SimplePay\Core\Payments\Payment_Confirmation;

/**
 * Abstract class for payment methods.
 *
 * @since 4.16.0
 * @package SimplePay\Core\PaymentMethods
 */
abstract class AbstractPaymentMethod {

	/**
	 * The payment method ID.
	 *
	 * @var string
	 * @since 4.16.0
	 */
	const PAYMENT_METHOD_ID = '';

	/**
	 * Register the payment method.
	 *
	 * @since 4.16.0
	 *
	 * @param Collection $payment_methods Payment methods collection.
	 */
	abstract public function register( $payment_methods );

	/**
	 * Validates the Payment Confirmation data.
	 *
	 * If the data includes an invalid or incomplete PaymentIntent
	 * redirect to the form's failure page.
	 *
	 * @since 4.2.0
	 */
	public function redirect_failed_payment(): void {
		// Ensure we can retrieve a PaymentIntent.
		if ( ! isset( $_GET['payment_intent'] ) ) {
			return;
		}

		// Ensure we paid with the correct payment method.
		if ( ! isset( $_GET['source_type'] ) || static::PAYMENT_METHOD_ID !== $_GET['source_type'] ) {
			return;
		}

		// Ensure we have a customer so `Payment_Confirmation\get_confirmation_data()` doesn't fail.
		if ( ! isset( $_GET['customer_id'] ) ) {
			return;
		}

		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data();

		// Ensure we have a Payment Form to reference.
		if ( ! isset( $payment_confirmation_data['form'] ) ) {
			return;
		}

		$payment_intent = isset( $payment_confirmation_data['paymentintents'] )
			? current( $payment_confirmation_data['paymentintents'] )
			: false;

		$failure_page = $payment_confirmation_data['form']->payment_cancelled_page;

		// Redirect to failure if PaymentIntent cannot be found.
		if ( false === $payment_intent ) {
			wp_safe_redirect( $failure_page );
		}

		// Do nothing if the Intent has succeeded.
		if ( 'succeeded' === $payment_intent->status ) {
			return;
		}

		// Do nothing if the Intent did not have an error.
		if ( ! isset( $payment_intent->last_payment_error ) ) {
			return;
		}

		// Do nothing if the Intent is not from the correct payment method.
		if ( static::PAYMENT_METHOD_ID !== $payment_intent->last_payment_error->payment_method->type ) {
			return;
		}

		// Redirect to failure page.
		wp_safe_redirect( $failure_page );
	}
}
