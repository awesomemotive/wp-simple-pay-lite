<?php
/**
 * Emails: Payment Notification
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Pro\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PaymentNotificationEmail class.
 *
 * @since 4.7.3
 */
class PaymentNotificationEmail extends AbstractEmail {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'payment-notification';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return AbstractEmail::INTERNAL_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Payment Notification', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send a payment notification email upon successful payment',
			'stripe'
		);
	}

	/**
	 * Returns the email address(es) to send the email to.
	 *
	 * @since 4.7.3
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
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_subject() {
		/** @var string $subject */
		$subject = simpay_get_setting(
			sprintf( 'email_%s_subject', $this->get_id() ),
			sprintf(
				/* translators: %s Site name */
				__( 'New Payment on %s', 'stripe' ),
				get_bloginfo( 'name' )
			)
		);

		return $subject;
	}

	/**
	 * Returns the body (content) of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_body() {
		return Settings\Emails\PaymentNotification\get_body_setting_or_default();
	}

	/**
	 * Returns the default body content for new installs on version 4.7.3 or higher.
	 *
	 * @see https://github.com/awesomemotive/wp-simple-pay-pro/issues/2578
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public static function get_473_default_body() {
		return sprintf(
			'<h1>%s</h1>%s<ul>%s%s</ul>%s',
			esc_html__( '🎉 Congrats! You just received {total-amount}', 'stripe' ),
			sprintf(
				/* translators: %1$s: Customer email, %2$s: Form title, %3$s: Charge date */
				esc_html__( 'A payment from <strong>%1$s</strong> for <strong>%2$s</strong> has been received on <strong>%3$s</strong>.', 'stripe' ),
				'{customer-email}',
				'{form-title}',
				'{charge-date}'
			),
			sprintf(
				'<li><a href="%s">%s</a></li>',
				esc_url( '{payment-url}' ),
				sprintf(
					/* translators: %s: Total amount */
					esc_html__( 'View %s payment in Stripe →', 'stripe' ),
					'{total-amount}'
				)
			),
			sprintf(
				'<li><a href="%s">%s</a></li>',
				esc_url( '{customer-url}' ),
				sprintf(
					/* translators: %s: Customer email */
					esc_html__( 'View %s in Stripe →', 'stripe' ),
					'{customer-email}'
				)
			),
			'{custom-fields}'
		);
	}
}
