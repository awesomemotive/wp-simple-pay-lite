<?php
/**
 * Emails: Payment Failure Confirmation
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
 * PaymentFailureConfirmationEmail class.
 *
 * @since 4.17.6
 */
class PaymentFailureConfirmationEmail extends AbstractEmail {

	use FailureSmartTagsTrait;
	use RateLimitedEmailTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'payment-failure-confirmation';
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
		return __( 'Payment Failure Confirmation', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send a confirmation email to the customer when a payment fails',
			'stripe'
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * Overrides the default determination so the customer-facing failure
	 * email is disabled unless explicitly enabled, matching the setting's
	 * schema default of "no".
	 *
	 * @since 4.17.6
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$enabled = 'yes' === simpay_get_setting(
			sprintf(
				'email_%s',
				$this->get_id()
			),
			'no'
		);

		return self::is_available() && $enabled;
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
			__( 'Your payment could not be processed', 'stripe' )
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
		return Settings\Emails\PaymentFailureConfirmation\get_body_setting_or_default();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_failure_reason_fallback() {
		return esc_html__( 'Your payment could not be processed.', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * The Charge outcome's `seller_message` is merchant-facing and must not be
	 * shown to customers.
	 *
	 * @since 4.17.6
	 *
	 * @return bool
	 */
	protected function use_seller_message_fallback() {
		return false;
	}
}
