<?php
/**
 * Admin: Addon/Plugin installer
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Addon;

use Plugin_Upgrader;
use SimplePay\Core\EventManagement\SubscriberInterface;
use WP_Ajax_Upgrader_Skin;

/**
 * AddonInstaller class.
 *
 * @since 4.4.0
 */
class AddonInstaller implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'wp_ajax_simpay_activate_addon'   => 'activate_addon',
			'wp_ajax_simpay_deactivate_addon' => 'deactivate_addon',
			'wp_ajax_simpay_install_addon'    => 'install_addon',
		);
	}

	/**
	 * Listens for addon/plugin AJAX activation requests.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function activate_addon() {
		// Run a security check.
		check_ajax_referer( 'simpay-admin', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error(
				esc_html__(
					'Plugin activation is disabled for you on this site.',
					'simple-pay'
				)
			);
		}

		$type = 'addon';

		if ( isset( $_POST['plugin'] ) ) {
			if ( ! empty( $_POST['type'] ) ) {
				$type = sanitize_key( $_POST['type'] );
			}

			/** @var string $plugin */
			$plugin   = wp_unslash( $_POST['plugin'] );
			$plugin   = sanitize_text_field( $plugin );
			$activate = activate_plugins( $plugin );

			if ( ! is_wp_error( $activate ) ) {
				if ( $type === 'plugin' ) {
					wp_send_json_success(
						esc_html__( 'Plugin activated.', 'simple-pay' )
					);
				} else {
					wp_send_json_success(
						esc_html__( 'Addon activated.', 'simple-pay' )
					);
				}
			}
		}

		if ( $type === 'plugin' ) {
			wp_send_json_error(
				esc_html__(
					'Could not activate the plugin. Please activate it on the Plugins page.',
					'simple-pay'
				)
			);
		}

		wp_send_json_error(
			esc_html__(
				'Could not activate the addon. Please activate it on the Plugins page.',
				'simple-pay'
			)
		);
	}

	/**
	 * Listens for addon/plugin AJAX deactivation requests.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function deactivate_addon() {
		// Run a security check.
		check_ajax_referer( 'simpay-admin', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'deactivate_plugins' ) ) {
			wp_send_json_error(
				esc_html__(
					'Plugin deactivation is disabled for you on this site.',
					'simple-pay'
				)
			);
		}

		$type = empty( $_POST['type'] )
			? 'addon'
			: sanitize_key( $_POST['type'] );

		if ( isset( $_POST['plugin'] ) ) {
			/** @var string $plugin */
			$plugin = wp_unslash( $_POST['plugin'] );
			$plugin = sanitize_text_field( $plugin );

			deactivate_plugins( $plugin );

			if ( $type === 'plugin' ) {
				wp_send_json_success(
					esc_html__( 'Plugin deactivated.', 'simple-pay' )
				);
			} else {
				wp_send_json_success(
					esc_html__( 'Addon deactivated.', 'simple-pay' )
				);
			}
		}

		wp_send_json_error(
			esc_html__(
				'Could not deactivate the addon. Please deactivate from the Plugins page.',
				'simple-pay'
			)
		);
	}

	/**
	 * Listens for addon/plugin AJAX installation requests.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function install_addon() {
		// Run a security check.
		check_ajax_referer( 'simpay-admin', 'nonce' );

		$generic_error = esc_html__(
			'There was an error while performing your request.',
			'simple-pay'
		);

		$type = ! empty( $_POST['type'] )
			? sanitize_key( $_POST['type'] )
			: 'addon';

		// Check if new installations are allowed.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( $generic_error );
		}

		$error = $type === 'plugin'
			? esc_html__(
				'Could not install the plugin. Please download and install it manually.',
				'simple-pay'
			)
			: esc_html__(
				'Could not install the addon. Please download it from wpforms.com and install it manually.',
				'simple-pay'
			);

		/** @var string $plugin */
		$plugin = ! empty( $_POST['plugin'] )
			? wp_unslash( $_POST['plugin'] )
			: '';

		$plugin_url = ! empty( $plugin )
			? esc_url_raw( $plugin )
			: '';

		if ( empty( $plugin_url ) ) {
			wp_send_json_error( $error );
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'simpay_page_simpay-settings' );

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				array(
					'post_type' => 'simple-pay',
					'page'      => 'simpay-about-us',
				),
				admin_url( 'edit.php' )
			)
		);

		ob_start();
		$creds = request_filesystem_credentials( $url, '', false, '', null );

		// Hide the filesystem credentials form.
		ob_end_clean();

		// Check for file system permissions.
		if ( $creds === false ) {
			wp_send_json_error( $error );
		}

		/** @var array<string, string> $creds Filesystem credentials. */
		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $error );
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		/** \Plugin_Upgrader class */
		$upgrader = ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		if ( ! file_exists( $upgrader ) ) {
			wp_send_json_error( $error );
		}

		require_once $upgrader;

		$installer = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) || empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $_POST['plugin'] ); // phpcs:ignore

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin_basename = $installer->plugin_info();

		if ( empty( $plugin_basename ) ) {
			wp_send_json_error( $error );
		}

		$result = array(
			'msg'          => $generic_error,
			'is_activated' => false,
			'basename'     => $plugin_basename,
		);

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$result['msg'] = $type === 'plugin'
				? esc_html__( 'Plugin installed.', 'simple-pay' )
				: esc_html__( 'Addon installed.', 'simple-pay' );

			wp_send_json_success( $result );
		}

		// Activate the plugin silently.
		/** @var string $plugin_basename */
		$activated = activate_plugin( $plugin_basename );

		if ( ! is_wp_error( $activated ) ) {
			$result['is_activated'] = true;
			$result['msg']          = $type === 'plugin'
				? esc_html__( 'Plugin installed & activated.', 'simple-pay' )
				: esc_html__( 'Addon installed & activated.', 'simple-pay' );

			wp_send_json_success( $result );
		}

		// Fallback error just in case.
		wp_send_json_error( $result );
	}

}
