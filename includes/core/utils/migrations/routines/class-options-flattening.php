<?php
/**
 * Routines: Options flattening.
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Utils\Migrations\Routines;

use SimplePay\Core\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options_Flattening class
 *
 * @since 4.0.0
 */
class Options_Flattening extends Migrations\Bulk_Migration {

	/**
	 * Runs the migration.
	 *
	 * @since 4.0.0
	 */
	public function run() {
		// Ensure shims are not added before migration is run.
		remove_filter(
			'option_simpay_settings_keys',
			'SimplePay\Core\Settings\Compat\option_simpay_settings_keys',
			10,
			3
		);

		$general = get_option( 'simpay_settings_general', array() );
		$keys    = get_option( 'simpay_settings_keys', array() );
		$display = get_option( 'simpay_settings_display', array() );

		add_filter(
			'option_simpay_settings_keys',
			'SimplePay\Core\Settings\Compat\option_simpay_settings_keys',
			10,
			3
		);

		if ( empty( $general ) && empty( $keys ) && empty( $display ) ) {
			return $this->complete();
		}

		$settings_sections = array_merge( $general, $keys, $display );
		$flat              = array();

		foreach ( $settings_sections as $subsection_id => $subsection_settings ) {
			foreach ( $subsection_settings as $key => $value ) {
				// Rename some keys to be more specific.
				switch ( $key ) {
					case 'country';
						$key = 'account_country';
						break;
					case 'site';
						$key = 'recaptcha_site_key';
						break;
					case 'threshold';
						$key = 'recaptcha_score_threshold';
						break;
					case 'secret';
						$key = 'recaptcha_secret_key';
						break;
					case 'locale':
						$key = 'stripe_checkout_locale';
						break;
					case 'elements_locale':
						$key = 'stripe_elements_locale';
						break;
					case 'separator':
						// A fresh < 4.0 install prepopulates the database with an empty
						// value instead of 'yes' or no key.
						if ( empty( $value ) ) {
							$value = 'no';
						}

						break;
					case 'endpoint_secret':
						if ( 'test_keys' === $subsection_id ) {
							$key = 'test_webhook_endpoint_secret';
						} else {
							$key = 'live_webhook_endpoint_secret';
						}
						break;
					case 'secret_key':
						if ( 'test_keys' === $subsection_id ) {
							$key = 'test_secret_key';
						} else {
							$key = 'live_secret_key';
						}
						break;
					case 'publishable_key':
						if ( 'live_keys' === $subsection_id ) {
							$key = 'live_publishable_key';
						} else {
							$key = 'test_publishable_key';
						}
						break;
				}

				$flat[ $key ] = $value;
			}
		}

		unset( $flat['setup'] );
		unset( $flat['license_key'] );

		update_option( 'simpay_settings', $flat );

		return $this->complete();
	}

}
