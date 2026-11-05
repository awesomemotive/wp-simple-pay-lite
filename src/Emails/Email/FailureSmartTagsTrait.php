<?php
/**
 * Emails: Failure smart tags trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.6
 */

namespace SimplePay\Core\Emails\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FailureSmartTagsTrait.
 *
 * Provides the `{failure-reason}` and `{failure-code}` smart tag callbacks
 * shared by the payment failure notification and confirmation emails.
 *
 * @since 4.17.6
 */
trait FailureSmartTagsTrait {

	/**
	 * Returns the value used when no failure reason is available on the charge.
	 *
	 * @since 4.17.6
	 *
	 * @return string
	 */
	abstract protected function get_failure_reason_fallback();

	/**
	 * Determines if `{failure-reason}` may fall back to the Charge outcome's
	 * `seller_message` when no `failure_message` is available.
	 *
	 * The seller message is written by Stripe for the merchant and can contain
	 * risk/Radar wording, so customer-facing emails should return `false`.
	 *
	 * @since 4.17.6
	 *
	 * @return bool
	 */
	protected function use_seller_message_fallback() {
		return true;
	}

	/**
	 * Returns the failure smart tags and their descriptions, for display in
	 * the email settings.
	 *
	 * Keys are the tag names without braces, matching the format expected by
	 * `__unstable_print_tag_list()` (which adds the braces).
	 *
	 * @since 4.17.6
	 *
	 * @return array<string, string>
	 */
	public static function get_smart_tag_descriptions() {
		return array(
			'failure-reason' => esc_html__(
				'The reason the payment failed (e.g. "Your card was declined.")',
				'stripe'
			),
			'failure-code'   => esc_html__(
				'The Stripe failure code (e.g. "card_declined")',
				'stripe'
			),
		);
	}

	/**
	 * Registers smart tags for failure context.
	 *
	 * @since 4.17.6
	 *
	 * @return void
	 */
	public function register_smart_tags() {
		add_filter(
			'simpay_payment_confirmation_template_tag_failure-reason',
			array( $this, 'failure_reason' ),
			10,
			2
		);

		add_filter(
			'simpay_payment_confirmation_template_tag_failure-code',
			array( $this, 'failure_code' ),
			10,
			2
		);
	}

	/**
	 * Unregisters smart tags for failure context.
	 *
	 * Prevents this email's callbacks from affecting other emails parsed
	 * later in the same request.
	 *
	 * @since 4.17.6
	 *
	 * @return void
	 */
	public function unregister_smart_tags() {
		remove_filter(
			'simpay_payment_confirmation_template_tag_failure-reason',
			array( $this, 'failure_reason' ),
			10
		);

		remove_filter(
			'simpay_payment_confirmation_template_tag_failure-code',
			array( $this, 'failure_code' ),
			10
		);
	}

	/**
	 * Replaces the {failure-reason} smart tag.
	 *
	 * @since 4.17.6
	 *
	 * @param string               $value Template tag value.
	 * @param array<string, mixed> $payment_confirmation_data Payment confirmation data.
	 * @return string
	 */
	public function failure_reason( $value, $payment_confirmation_data ) {
		$charge = isset( $payment_confirmation_data['charge'] )
			? $payment_confirmation_data['charge']
			: null;

		if ( $charge instanceof \SimplePay\Vendor\Stripe\Charge ) {
			if ( ! empty( $charge->failure_message ) ) {
				return esc_html( $charge->failure_message );
			}

			if (
				$this->use_seller_message_fallback() &&
				isset( $charge->outcome->seller_message )
			) {
				return esc_html( (string) $charge->outcome->seller_message );
			}
		}

		return $this->get_failure_reason_fallback();
	}

	/**
	 * Replaces the {failure-code} smart tag.
	 *
	 * @since 4.17.6
	 *
	 * @param string               $value Template tag value.
	 * @param array<string, mixed> $payment_confirmation_data Payment confirmation data.
	 * @return string
	 */
	public function failure_code( $value, $payment_confirmation_data ) {
		$charge = isset( $payment_confirmation_data['charge'] )
			? $payment_confirmation_data['charge']
			: null;

		if (
			$charge instanceof \SimplePay\Vendor\Stripe\Charge &&
			! empty( $charge->failure_code )
		) {
			return esc_html( $charge->failure_code );
		}

		return esc_html__( 'unknown', 'stripe' );
	}
}
