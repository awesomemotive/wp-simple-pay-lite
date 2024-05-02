<?php
/**
 * Emails: Subscription Cancellation Notification
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.10.0
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Pro\Settings;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SubscriptionCancellationConfirmation class
 *
 * @since 4.10.0
 */
class SubscriptionCancellationConfirmation extends AbstractEmail {
	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'subscription-cancel-confirmation';
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
	public function get_licenses() {
		return array(
			'professional',
			'ultimate',
			'elite',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Subscription Cancellation Confirmation', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Email sent to users when their subscription is cancelled.',
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
					__( 'Subscription Cancelled for %s', 'stripe' ),
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
		return Settings\Emails\SubscriptionCancellationConfirmation\get_body_setting_or_default();
	}
}
