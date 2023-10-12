<?php
/**
 * Telemetry: Plugin
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess\TelemetryData;

use SimplePay\Core\Emails\Email\PaymentConfirmationEmail;
use SimplePay\Core\Emails\Email\PaymentNotificationEmail;
use SimplePay\Core\Emails\Email\SummaryReportEmail;
use SimplePay\Core\Emails\Email\UpcomingInvoiceEmail;

/**
 * PluginTelemetryData class.
 *
 * @since 4.7.10
 */
class PluginTelemetryData extends AbstractTelemetryData {

	/**
	 * @var \SimplePay\Core\License\License
	 */
	protected $license;

	/**
	 * @param \SimplePay\Core\License\License $license License.
	 */
	public function __construct( $license ) {
		$this->license = $license;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		/** @var string $stripe_account_id */
		$stripe_account_id = get_option( 'simpay_stripe_connect_account_id', '' );

		/** @var string $country */
		$country = simpay_get_setting( 'account_country', 'US' );

		/** @var string $currency */
		$currency = simpay_get_setting( 'currency', 'usd' );
		$currency = strtolower( $currency );

		$data = array(
			'version'         => SIMPLE_PAY_VERSION, // @phpstan-ignore-line
			'license_level'   => ucfirst( $this->license->get_level() ),
			'license_status'  => $this->license->is_lite()
				? 'valid'
				: $this->license->get_status(),
			'stripe_livemode' => simpay_is_livemode(),
			'stripe_connect'  => '' !== $stripe_account_id,
			'stripe_country'  => strtolower( $country ),
			'stripe_currency' => $currency,
			'stripe_webhook'  => $this->is_webhook_working(),
			'stripe_upe'      => simpay_is_upe(),
			'fraud_captcha'   => $this->get_antispam_captcha(),
		);

		if ( $this->license->is_lite() ) {
			return $data;
		}

		/** @var string $email_verification */
		$email_verification               = simpay_get_setting( 'fraud_email_verification', 'yes' );
		$data['fraud_email_verification'] = ( 'yes' === $email_verification );

		/** @var int $fraud_email_verification_threshold */
		$fraud_email_verification_threshold         = simpay_get_setting( 'fraud_email_verification_threshold', 3 );
		$data['fraud_email_verification_threshold'] = $fraud_email_verification_threshold . ' times';

		/** @var int $fraud_email_verification_timeframe */
		$fraud_email_verification_timeframe         = simpay_get_setting( 'fraud_email_verification_timeframe', 6 );
		$data['fraud_email_verification_timeframe'] = $fraud_email_verification_timeframe . ' hours';

		/** @var string $fraud_require_authentication */
		$fraud_require_authentication         = simpay_get_setting( 'fraud_require_authentication', 'no' );
		$data['fraud_require_authentication'] = ( 'yes' === $fraud_require_authentication );

		$data['email_payment_notification'] = ( new PaymentNotificationEmail() )->is_enabled();
		$data['email_payment_confirmation'] = ( new PaymentConfirmationEmail() )->is_enabled();
		$data['email_upcoming_invoice']     = ( new UpcomingInvoiceEmail() )->is_enabled();
		$data['email_summary']              = ( new SummaryReportEmail() )->is_enabled();

		/** @var string $subscription_management */
		$subscription_management         = simpay_get_setting( 'subscription_management', 'none' );
		$data['subscription_management'] = implode(
			' ',
			array_map(
				'ucfirst',
				explode( '-', $subscription_management )
			)
		);

		return $data;
	}

	/**
	 * Looks for the most recent wpsp_transaction's date_created, and the most recent
	 * wpsp_webhook's date_created, and determines if the webhook event occured within
	 * 15 minutes of the transaction.
	 *
	 * This mimics the logic in src/Webhook/EndpointHealthCheck.php, without the dependencies.
	 *
	 * @return bool
	 */
	private function is_webhook_working() {
		global $wpdb;

		if ( $this->license->is_lite() ) {
			return true;
		}

		$latest_transaction = $wpdb->get_var(
			"SELECT date_created FROM {$wpdb->prefix}wpsp_transactions ORDER BY date_created DESC LIMIT 1"
		);

		// No transactions, so we don't know if the webhook is working.
		if ( ! $latest_transaction ) {
			return true;
		}

		$latest_webhook = $wpdb->get_var(
			"SELECT date_created FROM {$wpdb->prefix}wpsp_webhooks ORDER BY date_created DESC LIMIT 1"
		);

		if ( ! $latest_webhook ) {
			return false;
		}

		return ( strtotime( $latest_transaction ) - strtotime( $latest_webhook ) ) < 15 * MINUTE_IN_SECONDS;
	}

	/**
	 * Returns the antispam type.
	 *
	 * @return string
	 */
	private function get_antispam_captcha() {
		$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
		$default            = ! empty( $existing_recaptcha )
			? 'recaptcha-v3'
			: '';

		$type = simpay_get_setting( 'captcha_type', $default );

		switch ( $type ) {
			case 'hcaptcha':
				return 'hCaptcha';
			case 'recaptcha-v3':
				return 'reCAPTCHA v3';
			case 'cloudflare-turnstile':
				return 'Cloudflare Turnstile';
			default:
				return 'None';
		}
	}
}
