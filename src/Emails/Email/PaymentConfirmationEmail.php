<?php
/**
 * Emails: Payment Confirmation
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Pro\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PaymentConfirmationEmail class
 *
 * @since 4.7.3
 */
class PaymentConfirmationEmail extends AbstractEmail {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'payment-confirmation';
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
		return __( 'Payment Confirmation', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send a payment receipt email to the customer upon successful payment',
			'stripe'
		);
	}

	/**
	 * Returns the subject of the email.
	 *
	 * @since 4.7.3
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
					__( 'Payment Receipt for %s', 'stripe' ),
					get_bloginfo( 'name' )
				)
			)
		);

		return $subject;
	}

	/**
	 * Returns the body (content) of the email.
	 *
	 * @since 4.7.3
	 *
	 * @param string $type The type (one-time, subscription, or trial) of the email content.
	 * @return string
	 */
	public function get_body( $type ) {
		return Settings\Emails\PaymentConfirmation\get_body_setting_or_default(
			$type
		);
	}

}
