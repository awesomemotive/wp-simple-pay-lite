<?php
/**
 * Setup Wizard: Marketing
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Admin\SetupWizard;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * SetupWizardMarketing class.
 *
 * @since 4.4.2
 */
class SetupWizardMarketing implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'wp_ajax_simpay_setup_wizard_subscribe_email' => 'subscribe_email',
		);
	}

	/**
	 * Subscribes an email address to the WP Simple Pay newsletter.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function subscribe_email() {
		check_ajax_referer( 'simpay-setup-wizard-subscribe', 'nonce' );

		$email = ! empty( $_POST['email'] )
			? filter_var( wp_unslash( $_POST['email'] ), FILTER_VALIDATE_EMAIL )
			: '';

		if ( empty( $email ) ) {
			wp_send_json_error();
		}

		$request = wp_remote_post(
			'https://connect.wpsimplepay.com/',
			array(
				'sslverify' => false,
				'blocking'  => false,
				'body' => array(
					'action'   => 'setup-wizard-subscription',
					'email'    => base64_encode( $email ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'license'  => $this->get_license_name(),
					'site_url' => home_url(),
				),
			)
		);

		$response = wp_remote_retrieve_response_code( $request );

		if ( 200 === $response ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Returns the license type name.
	 *
	 * @since 4.4.2
	 *
	 * @return string
	 */
	private function get_license_name() {
		if ( $this->license->is_lite() ) {
			return 'lite';
		}

		switch ( $this->license->get_price_id() ) {
			case '0':
				return 'personal';
			case '1':
				return 'plus';
			case '2':
				return 'professional';
			case '3':
				return 'ultimate';
			default:
				return '';
		}
	}

}
