<?php
/**
 * Emails: Payment Processing Confirmation
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
 * PaymentProcessingConfirmationEmail class
 *
 * @since 4.10.0
 */
class PaymentProcessingConfirmationEmail extends AbstractEmail {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'payment-processing-confirmation';
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
		return __( 'Payment Processing Confirmation', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send a payment processing confirmation email to the customer upon successful payment',
			'stripe'
		);
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
			esc_html(
				sprintf(
					/* translators: %s Site name */
					__( 'Payment Processing Confirmation for %s', 'stripe' ),
					get_bloginfo( 'name' )
				)
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
		return Settings\Emails\PaymentProcessingConfirmation\get_body_setting_or_default();
	}
}
