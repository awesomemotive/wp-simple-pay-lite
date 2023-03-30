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
		return ( '<h1>🎉 Congrats! You just received {total-amount}</h1>

A payment from <strong>{customer-email}</strong> for <strong>{form-title}</strong> has been received on <strong>{charge-date}</strong>.

<ul>
 	<li><a href="{payment-url}">View {total-amount} payment in Stripe →</a></li>
 	<li><a href="{customer-url}">View {customer-email} in Stripe →</a></li>
</ul>

{custom-fields}' );
	}

}
