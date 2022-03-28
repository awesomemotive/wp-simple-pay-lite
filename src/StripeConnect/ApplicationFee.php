<?php
/**
 * Stripe Connect: Application fee
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\StripeConnect;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * ApplicationFee class.
 *
 * @since 4.4.1
 */
class ApplicationFee implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( false === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'__unstable_simpay_stripe_connect_account_message'        =>
				'maybe_show_application_fee',
			'simpay_get_paymentintent_args_from_payment_form_request' =>
				'maybe_add_application_fee',
		);
	}

	/**
	 * Appends a note about additional fees being applied when using a new Lite install.
	 *
	 * @since 4.4.1
	 *
	 * @param string $message Stripe Connect account message.
	 * @return string
	 */
	public function maybe_show_application_fee( $message ) {
		if ( false === $this->has_application_fee() ) {
			return $message;
		}

		$upgrade_url = simpay_pro_upgrade_url( 'stripe-account-settings' );

		$message .= '<br/ ><br />' . sprintf(
			/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
			__(
				'%1$sPay as you go pricing%2$s: 2%% fee per-transaction + Stripe fees. %3$sUpgrade to Pro%4$s for no added fees and priority support.',
				'stripe'
			),
			'<strong>',
			'</strong>',
			'<a href="' . esc_url( $upgrade_url ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);

		return $message;
	}

	/**
	 * Adds an application fee to Stripe Checkout Session arguments.
	 *
	 * @since 4.4.1
	 *
	 * @param array<mixed> $payment_intent_args Payment intent arguments.
	 * @return array<mixed> Payment intent arguments, maybe with an application fee amount.
	 */
	public function maybe_add_application_fee( $payment_intent_args ) {
		if ( false === $this->has_application_fee() ) {
			return $payment_intent_args;
		}

		$payment_intent_args['application_fee_amount'] = round(
			$payment_intent_args['amount'] * 0.02,
			0
		);

		return $payment_intent_args;
	}

	/**
	 * Determines if an application fee is being added to payments.
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	public function has_application_fee() {
		// Double check license again.
		// Currently the Stripe Connect flag is not updated until a connection is reinitated.
		// Ensure any direct usage of this function (bypassing subscriber) checks the license.
		if ( false === $this->license->is_lite() ) {
			return false;
		}

		// Not a new Lite connection, do not add a fee.
		if ( false === $this->is_new_lite_connection() ) {
			return false;
		}

		// Account country does not suppport application fees, do not add a fee.
		if ( false === $this->is_account_country_supported() ) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the latest Stripe Connect connection is "new" (i.e reconnected after 4.4.1).
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	private function is_new_lite_connection() {
		$connect_account_type = get_option(
			'simpay_stripe_connect_type',
			''
		);

		// Lite has not been reconnected yet, do not add a fee.
		return (
			! empty( $connect_account_type ) &&
			'lite' === $connect_account_type
		);
	}

	/**
	 * Determines if the Stripe account country can use application fees.
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	private function is_account_country_supported() {
		/** @var string $account_country */
		$account_country = simpay_get_setting( 'account_country', 'US' );

		return ! in_array(
			strtolower( $account_country ),
			$this->get_unavailable_country_codes(),
			true
		);
	}

	/**
	 * Returns a list of country that are not compatible with application fees.
	 *
	 * @since 4.4.1
	 *
	 * @return array<string>
	 */
	private function get_unavailable_country_codes() {
		return array(
			'br',
		);
	}

}
