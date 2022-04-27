<?php
/**
 * License: Validator
 *
 * Periodically check the license key to make sure it's still valid.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\License;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * LicenseValidatorSubscriber class.
 *
 * @since 4.4.5
 */
class LicenseValidatorSubscriber implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * License management.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\License\LicenseManager
	 */
	private $manager;

	/**
	 * LicenseValidatorSubscriber.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\License\LicenseManager $manager License manager.
	 * @return void
	 */
	public function __construct( $manager ) {
		$this->manager = $manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'admin_init' => 'validate_license',
		);
	}

	/**
	 * Validates an existing license key.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function validate_license() {
		$simpay_license_next_check = get_option( 'simpay_license_next_check', false );

		if (
			is_numeric( $simpay_license_next_check ) &&
			( $simpay_license_next_check > current_time( 'timestamp' ) )
		) {
			return;
		}

		$key = $this->license->get_key();

		if ( empty( $key ) ) {
			return;
		}

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $key,
			'item_id'    => SIMPLE_PAY_ITEM_ID, // @phpstan-ignore-line
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post(
			SIMPLE_PAY_STORE_URL, // @phpstan-ignore-line
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) ) {
			update_option(
				'simpay_license_next_check',
				current_time( 'timestamp' ) + ( HOUR_IN_SECONDS * 2 )
			);

			return;
		}

		// Decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		/** @var \stdClass $license_data */

		// Ensure license is activated.
		if ( isset( $license_data->license ) ) {
			$this->manager->activate( $key );
		}

		update_option(
			'simpay_license_next_check',
			current_time( 'timestamp' ) + DAY_IN_SECONDS
		);
	}

}
