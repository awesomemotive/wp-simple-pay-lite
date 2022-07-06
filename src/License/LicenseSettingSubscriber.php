<?php
/**
 * License: Setting
 *
 * Handles the UI for activating and deactivating a license key and the side effects of that.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\License;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Settings;

/**
 * LicenseSettingSubscriber class.
 *
 * @since 4.4.5
 */
class LicenseSettingSubscriber implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'simpay_register_settings_subsections' => 'register_settings_subsection',
			'simpay_register_settings'             => 'register_setting',
		);
	}

	/**
	 * Registers settings subsections.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\Settings\Subsection_Collection $subsections Subsections collection.
	 * @return void
	 */
	function register_settings_subsection( $subsections ) {
		// License/License.
		$subsections->add(
			new Settings\Subsection(
				array(
					'id'       => 'license',
					'section'  => 'general',
					'label'    => esc_html_x(
						'License',
						'settings subsection label',
						'stripe'
					),
					'priority' => 1,
				)
			)
		);
	}

	/**
	 * Registers the settings.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 * @return void
	 */
	function register_setting( $settings ) {
		$settings->add(
			new Settings\Setting(
				array(
					'id'         => 'license',
					'section'    => 'general',
					'subsection' => 'license',
					'label'      => esc_html__( 'License Key', 'stripe' ),
					'output'     => function() {
						return $this->get_setting_ui();
					}
				)
			)
		);
	}

	/**
	 * Returns the UI for the license setting.
	 *
	 * @since 4.4.5
	 *
	 * @return string|bool
	 */
	private function get_setting_ui() {
		// Disable submit button.
		add_filter( 'simpay_admin_page_settings_general_submit', '__return_false' );

		$settings_url = Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'license',
			)
		);

		$has_config  = defined( 'SIMPLE_PAY_LICENSE_KEY' );
		$license     = $this->license;
		$feedback    = $this->get_license_feedback();
		$nonce       = wp_create_nonce( 'simpay-manage-license' );
		$refresh_url = add_query_arg(
			array(
				'simpay-action'          => 'simpay-activate-license',
				'simpay-license-nonce'   => $nonce,
				'simpay-license-key'     => $license->get_key(),
				'simpay-license-refresh' => true,
			)
		);

		ob_start();

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-setting-license.php'; // @phpstan-ignore-line

		/**
		 * Allows additional output after the license field.
		 *
		 * @since 4.4.0
		 */
		do_action( '__unstable_simpay_license_field' );

		return ob_get_clean();
	}

	/**
	 * Get license feedback, based on a specific status.
	 *
	 * @since 4.4.5
	 *
	 * @return string
	 */
	private function get_license_feedback() {
		if ( empty( $this->license->get_key() ) ) {
			return '';
		}

		switch ( $this->license->get_status() ) {
			case 'valid':
				return wp_kses(
					sprintf(
						/* translators: %1$s License level. */
						__( 'Your license level is %1$s.', 'stripe' ),
						'<strong>' . ucfirst( $this->license->get_level() ) . '</strong>'
					),
					array(
						'strong' => array()
					)
				);
			case 'expired':
				/** @var string $date_format */
				$date_format = get_option( 'date_format' );

				/** @var string $expiration */
				$expiration = $this->license->get_expiration();
				/** @var int $expiration */
				$expiration = strtotime( $expiration );

				return sprintf(
					/* translators: License expiration date. */
					__( 'Your license key expired on %1$s', 'stripe' ),
					date( $date_format, $expiration )
				);
			case 'inactive':
			case 'deactivated':
			case 'site_inactive':
			case 'empty':
			case 'disabled':
			case 'revoked':
			case 'invalid':
			default:
				return esc_html__( 'Please enter a valid license key.', 'stripe' );
		}
	}

}
