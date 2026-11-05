<?php
/**
 * Emails: Payment Failure Notification
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.6
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Pro\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PaymentFailureNotificationEmail class.
 *
 * @since 4.17.6
 */
class PaymentFailureNotificationEmail extends AbstractEmail {

	use FailureSmartTagsTrait;
	use RateLimitedEmailTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'payment-failure-notification';
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
		return __( 'Payment Failure Notification', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send a notification email to the site admin when a payment fails',
			'stripe'
		);
	}

	/**
	 * Returns the email address(es) to send the email to.
	 *
	 * @since 4.17.6
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
	 * @since 4.17.6
	 *
	 * @return string
	 */
	public function get_subject() {
		/** @var string $subject */
		$subject = simpay_get_setting(
			sprintf( 'email_%s_subject', $this->get_id() ),
			sprintf(
				/* translators: %s Site name */
				__( 'Payment Failed on %s', 'stripe' ),
				get_bloginfo( 'name' )
			)
		);

		return $subject;
	}

	/**
	 * Returns the body (content) of the email.
	 *
	 * @since 4.17.6
	 *
	 * @return string
	 */
	public function get_body() {
		return Settings\Emails\PaymentFailureNotification\get_body_setting_or_default();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_failure_reason_fallback() {
		return esc_html__( 'Unknown', 'stripe' );
	}
}
