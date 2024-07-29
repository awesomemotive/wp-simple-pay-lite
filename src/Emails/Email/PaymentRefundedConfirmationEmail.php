<?php
/**
 * Emails: Payment Refunded Confirmation
 *
 * @package SimplePay
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.10.0
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Pro\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PaymentRefundedConfirmationEmail class.
 *
 * @since 4.10.0
 */
class PaymentRefundedConfirmationEmail extends AbstractEmail {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'payment-refunded-confirmation';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return AbstractEmail::EXTERNAL_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Payment Refunded Confirmation', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send a confirmation email for a payment refund',
			'stripe'
		);
	}

	/**
	 * Returns the email address(es) to send the email to.
	 *
	 * @since 4.10.0
	 *
	 * @return string
	 */
	public function get_to() {
		/** @var string $to_address */
		$to_address = simpay_get_setting(
			sprintf( 'email_%s_to', $this->get_id() ),
			get_bloginfo( 'admin_email' )
		);

		return $to_address;
	}

	/**
	 * Returns the subject of the email.
	 *
	 * @since 4.10.0
	 *
	 * @return string
	 */
	public function get_subject() {
		/** @var string $subject */
		$subject = simpay_get_setting(
			sprintf( 'email_%s_subject', $this->get_id() ),
			sprintf(
				/* translators: %s Site name */
				__( 'Payment refunded on %s', 'stripe' ),
				get_bloginfo( 'name' )
			)
		);

		return $subject;
	}

	/**
	 * Returns the body (content) of the email.
	 *
	 * @since 4.10.0
	 *
	 * @return string
	 */
	public function get_body() {
		return Settings\Emails\PaymentRefundedConfirmation\get_body_setting_or_default();
	}

	/**
	 * Register smart tags.
	 *
	 * @since 4.11.0
	 *
	 * @return void
	 */
	public function register_smart_tags() {
		add_filter(
			'simpay_payment_confirmation_template_tag_refund-amount',
			array( $this, 'refund_amount' ),
			10,
			2
		);

		add_filter(
			'simpay_payment_confirmation_template_tag_refund-date',
			array( $this, 'refund_date' ),
			10,
			2
		);
	}

	/**
	 * Replaces the {refund-amount}.
	 *
	 * @since 4.11.0
	 *
	 * @param string                                                                                                                                                                                                               $default Template tag value.
	 * @param array{customer: \SimplePay\Vendor\Stripe\Customer, form: \SimplePay\Core\Abstracts\Form, subscriptions: array<\SimplePay\Vendor\Stripe\Subscription>, paymentintents: array<\SimplePay\Vendor\Stripe\PaymentIntent>} $payment_confirmation_data Contextual information about this payment confirmation.
	 * @return string
	 */
	public function refund_amount( $default, $payment_confirmation_data ) {
		// Get the currency.
		if ( ! empty( $payment_confirmation_data['subscriptions'] ) ) {
			$object   = current( $payment_confirmation_data['subscriptions'] );
			$currency = $object['currency'];
		} else {
			$object   = current( $payment_confirmation_data['paymentintents'] );
			$currency = $object['currency']; // @phpstan-ignore-line
		}

		// Check if the charge has been refunded.
		if ( isset( $payment_confirmation_data['charge'] ) && isset( $payment_confirmation_data['charge']['amount_refunded'] ) ) {
			return simpay_format_currency(
				$payment_confirmation_data['charge']['amount_refunded'],
				$currency
			);
		}

		// No refund amount.
		return simpay_format_currency(
			0,
			$currency
		);
	}

	/**
	 * Replaces the {refund-date}.
	 *
	 * @since 4.11.0
	 *
	 * @param string                                                                                                                                                                                                               $default Template tag value.
	 * @param array{customer: \SimplePay\Vendor\Stripe\Customer, form: \SimplePay\Core\Abstracts\Form, subscriptions: array<\SimplePay\Vendor\Stripe\Subscription>, paymentintents: array<\SimplePay\Vendor\Stripe\PaymentIntent>} $payment_confirmation_data Contextual information about this payment confirmation.
	 * @return string
	 */
	public function refund_date( $default, $payment_confirmation_data ) {
		$date = '';

		// Check if the charge has been refunded.
		if ( isset( $payment_confirmation_data['charge'] ) && isset( $payment_confirmation_data['charge']['amount_refunded'] ) ) {
			// Localize to current timezone and formatting.
			$date = get_date_from_gmt(
				gmdate( 'Y-m-d H:i:s', $payment_confirmation_data['charge']->created ),
				'U'
			);
			$date = date_i18n( get_option( 'date_format' ), $date ); // @phpstan-ignore-line
		}

		return esc_html( $date );
	}
}
