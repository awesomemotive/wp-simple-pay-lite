<?php
/**
 * Usage tracking.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Admin\Usage_Tracking;

use function SimplePay\Core\Admin\Usage_Tracking\checkin_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tracker.
 */
class Tracker {

	/**
	 * Tracking data.
	 *
	 * @since 3.6.0
	 *
	 * @type array
	 */
	protected $data = array();

	/**
	 * Retrieve a value from the tracked data.
	 *
	 * @since 3.6.0
	 *
	 * @param string $key Key to retrieve.
	 */
	public function __get( $key ) {
		return $this->data[ $key ];
	}

	/**
	 * Sets a value in the tracked data.
	 *
	 * @since 3.6.0
	 *
	 * @param string $key Key of data to track.
	 * @param string $value Value of data to track.
	 */
	public function __set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Setup with default data.
	 *
	 * @since 3.6.0
	 */
	public function __construct() {
		$this->setup_default_data();
	}

	/**
	 * Check a site in and record tracking information.
	 *
	 * @since 3.6.0
	 */
	public function checkin() {
		/**
		 * Filters the checkin data.
		 *
		 * @since 3.6.0
		 *
		 * @param object $this->data Checkin data.
		 */
		$data = apply_filters( 'simpay_usage_tracking_checkin_data', $this->data );

		$checkin = wp_remote_post(
			checkin_url(),
			array(
				'timeout'     => 8,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => false,
				'body'        => array_merge(
					array(
						'edd_action' => 'usage_tracking',
					),
					$data
				),
				'user-agent'  => 'WPSP/' . SIMPLE_PAY_VERSION . '; ' . get_bloginfo( 'url' ),
			)
		);

		// Log this checkin internally.
		update_option( 'simpay_usage_tracking_last_checkin', time() );
	}

	/**
	 * Get default tracking data.
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	private function setup_default_data() {
		// URL.
		$this->data['url'] = get_bloginfo( 'url' );

		// WPSP version.
		$this->data['wpsp_version'] = SIMPLE_PAY_VERSION;

		// Pro.
		$this->data['pro'] = defined( 'SIMPLE_PAY_ITEM_ID' );

		// License type.
		$license_data = get_option( 'simpay_license_data', new \stdClass() );
		$this->data['license'] = isset( $license_data->price_id ) ? $license_data->price_id : null;

		// Stripe Connect.
		$this->data['stripe_connect']= simpay_get_account_id() ? true : null;

		// Test mode.
		$this->data['test_mode'] = simpay_is_test_mode() ? true : null;

		// PHP version.
		$this->data['php_version'] = phpversion();

		// Theme.
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;

		$this->data['theme'] = $theme;

		// WP Version.
		$this->data['wp_version'] = get_bloginfo( 'version' );

		// Server.
		$this->data['server'] = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : null;

		// Install date.
		$confirmation_page = simpay_get_global_setting( 'success_page' );

		$this->data['install_date'] = $confirmation_page ? get_post_field( 'post_date', $confirmation_page ) : null;

		// Multsite.
		$this->data['multisite'] = is_multisite();

		// Locale.
		$this->data['locale'] = get_locale();

		// Plugins.
		if( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins = array_keys( get_plugins() );

		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				unset( $plugins[ $key ] );
			}
		}

		$this->data['active_plugins']   = $active_plugins;
		$this->data['inactive_plugins'] = $plugins;
	}

}
