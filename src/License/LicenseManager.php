<?php
/**
 * License: Manager
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\License;

/**
 * LicenseManager class.
 *
 * @since 4.4.5
 */
class LicenseManager {

	/**
	 * Activate a license key.
	 *
	 * Note: This method does no permission or security checks. Those should be done by
	 * the service or subscriber consuming this service.
	 *
	 * @since 4.4.5
	 *
	 * @param string $license License key to activate.
	 * @return bool|\SimplePay\Core\License\License False on failure, or License.
	 */
	function activate( $license ) {
		// Retrieve license key from form field.
		$key = sanitize_key( trim( $license ) );

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'activate_license',
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

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		// No error, so let's proceed.

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Update saved license key & data.
		update_option( 'simpay_license_key', $key );
		update_option( 'simpay_license_data', $license_data );

		return new License( $key );
	}

	/**
	 * Deactivate a license by key.
	 *
	 * Note: This method does no permission or security checks. Those should be done by
	 * the service or subscriber consuming this service.
	 *
	 * @since 4.4.5
	 *
	 * @param string $license License key to deactivate.
	 * @return bool|\SimplePay\Core\License\License False on failure, or License.
	 */
	public function deactivate( $license ) {
		$key = sanitize_key( trim( $license ) );

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'deactivate_license',
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

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		// No error, so let's proceed.

		// Decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		/** @var \stdClass $license_data */

		// $license_data->license will be either "deactivated" or "failed".
		if ( $license_data->license == 'deactivated' ) {

			// Remove saved license data, key & next check options.
			delete_option( 'simpay_license_data' );
			delete_option( 'simpay_license_key' );
			delete_option( 'simpay_license_next_check' );
		}

		return new License( '' );
	}

}
