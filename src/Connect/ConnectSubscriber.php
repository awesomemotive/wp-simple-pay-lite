<?php
/**
 * Connect: Subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.2
 */

namespace SimplePay\Core\Connect;

use Plugin_Upgrader;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Settings;
use WP_Ajax_Upgrader_Skin;

/**
 * ConnectSubscriber class.
 *
 * @since 4.5.2
 */
class ConnectSubscriber implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * License management.
	 *
	 * @since 4.5.2
	 * @var \SimplePay\Core\License\LicenseManager
	 */
	private $license_manager;

	/**
	 * ConnectSubscriber.
	 *
	 * @since 4.5.2
	 *
	 * @param \SimplePay\Core\License\LicenseManager $license_manager License manager.
	 * @return void
	 */
	public function __construct( $license_manager ) {
		$this->license_manager = $license_manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( false === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'wp_ajax_simpay_get_connect_url'        => 'generate_connect_url',
			'wp_ajax_nopriv_simpay_connect_process' => 'process',
		);
	}

	/**
	 * Generates the connect URL.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function generate_connect_url() {
		check_ajax_referer( 'simpay-connect-url', 'nonce' );

		// Check for permissions.
		if ( ! simpay_can_install_plugins() ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Oops! You are not allowed to install plugins. Please contact your site administrator.',
						'stripe'
					),
				)
			);
		}

		$key = ! empty( $_POST['key'] )
			? sanitize_text_field( wp_unslash( $_POST['key'] ) )
			: ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $key ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Please enter your license key to connect.',
						'stripe'
					),
				)
			);
		}

		// Verify Pro is not already installed.
		$active = activate_plugin(
			'wp-simple-pay-pro-3/simple-pay.php',
			'',
			false,
			true
		);

		// ... it is, activate that instead, and set the license data.
		if ( ! is_wp_error( $active ) ) {
			// Deactivate Lite.
			deactivate_plugins(
				plugin_basename( 'stripe/stripe-checkout.php' ),
				false,
				false
			);

			// Set license key, and activate it.
			update_option( 'simpay_license_key', $key );
			$this->license_manager->activate( $key );
			update_option( 'simpay_connect_upgraded', time() );

			wp_send_json_success(
				array(
					'message' => esc_html__(
						'You already have WP Simple Pay Pro installed! Activating it now.',
						'stripe'
					),
					'reload'  => true,
				)
			);
		}

		// Do not attempt on a local/development environment that is like not accessible from the internet.
		if ( simpay_is_dev_url() ) {
			wp_send_json_success(
				array(
					'url' => untrailingslashit(
						simpay_docs_link(
							'Manual Upgrade - Dev URL',
							'upgrading-wp-simple-pay-lite-to-pro#manual-installation',
							'connect-upgrade',
							true
						)
					),
				)
			);
		}

		// Set license key.
		update_option( 'simpay_license_key', $key );

		// Set OTH.
		$oth        = hash( 'sha512', (string) wp_rand() );
		$hashed_oth = hash_hmac( 'sha512', $oth, wp_salt() );

		update_option( 'simpay_connect_token', $oth );

		$version  = SIMPLE_PAY_VERSION; // @phpstan-ignore-line
		$siteurl  = admin_url();
		$endpoint = admin_url( 'admin-ajax.php' );
		$redirect = Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'license',
			)
		);

		$url = add_query_arg(
			array(
				'key'      => $key,
				'oth'      => $hashed_oth,
				'endpoint' => $endpoint,
				'version'  => $version,
				'siteurl'  => $siteurl,
				'homeurl'  => home_url(),
				'redirect' => rawurldecode( base64_encode( $redirect ) ),
				'v'        => 1,
			),
			'https://upgrade.wpsimplepay.com/'
		);

		wp_send_json_success(
			array(
				'url' => $url,
			)
		);
	}

	/**
	 * Processes the connection when the connect site pings back.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function process() {
		$error = wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'Oops! We could not automatically install an upgrade. Please install manually by visiting %1$swpsimplepay.com%2$s.',
					'stripe'
				),
				'<a target="_blank" href="' . esc_url( untrailingslashit( simpay_docs_link( 'Manual Upgrade - Error', 'upgrading-wp-simple-pay-lite-to-pro/#manual-installation', 'connect-upgrade', true ) ) ) . '">',
				'</a>'
			),
			array(
				'a' => array(
					'target' => true,
					'href'   => true,
				),
			)
		);

		// Verify params are present (oth & download link).
		$post_oth = ! empty( $_REQUEST['oth'] )
			? sanitize_text_field( $_REQUEST['oth'] )
			: '';

		$post_url = ! empty( $_REQUEST['file'] )
			? $_REQUEST['file']
			: '';

		/** @var string $license */
		$license = get_option( 'simpay_license_key', '' );
		$network = is_multisite();

		if ( empty( $post_oth ) || empty( $post_url ) ) {
			wp_send_json_error(
				array(
					'message' => $error,
				)
			);
		}

		// Retrieve OTH.
		/** @var string $oth */
		$oth = get_option( 'simpay_connect_token', '' );

		// Delete so cannot replay.
		delete_option( 'simpay_connect_token' );

		// Verify OTH.
		if ( empty( $oth ) ) {
			wp_send_json_error(
				array(
					'message' => $error,
				)
			);
		}

		if ( hash_hmac( 'sha512', $oth, wp_salt() ) !== $post_oth ) {
			wp_send_json_error(
				array(
					'message' => $error,
				)
			);
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'simpay_page_simpay_settings' );

		// Prepare variables.
		$url = esc_url_raw(
			Settings\get_url(
				array(
					'section'    => 'general',
					'subsection' => 'license',
				)
			)
		);

		// Verify Pro is not already activated.
		if ( 'wp-simple-pay-pro-3/simple-pay.php' === plugin_basename( SIMPLE_PAY_MAIN_FILE ) ) { // @phpstan-ignore-line
			wp_send_json_success(
				array(
					'message' => esc_html__(
						'Plugin installed & activated.',
						'stripe'
					),
				)
			);
		}

		// Verify Pro is not already installed.
		$active = activate_plugin(
			'wp-simple-pay-pro-3/simple-pay.php',
			$url,
			$network,
			true
		);

		if ( ! is_wp_error( $active ) ) {
			// If so, deactivate Lite (current) plugin.
			deactivate_plugins(
				'stripe/stripe-checkout.php',
				false,
				$network
			);

			wp_send_json_success(
				array(
					'message' => esc_html__(
						'Plugin installed & activated.',
						'stripe'
					),
				)
			);
		}

		// Check for file system permissions.
		/** @var array<string, string>|false $creds */
		$creds = request_filesystem_credentials( $url, '', false, '', null );

		if ( false === $creds ) {
			wp_send_json_error(
				array(
					'message' => $error,
				)
			);
		}

		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error(
				array(
					'message' => $error,
				)
			);
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action(
			'upgrader_process_complete',
			array( 'Language_Pack_Upgrader', 'async_upgrade' ),
			20
		);

		$upgrader = ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		if ( ! file_exists( $upgrader ) ) {
			wp_send_json_error(
				array(
					'message' => $error,
				)
			);
		}

		require_once $upgrader;

		$installer = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error(
				array(
					'message' => $error,
				)
			);
		}

		// Check license key.
		if ( empty( $license ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'You are not licensed.',
						'stripe'
					),
				)
			);
		}

		// Activate the license so the download URL is available.
		$this->license_manager->activate( $license );

		$installer->install( $post_url ); // phpcs:ignore

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $installer->plugin_info() ) {
			$plugin_basename = $installer->plugin_info();

			// Deactivate the Lite version first.
			deactivate_plugins(
				'stripe/stripe-checkout.php',
				false,
				$network
			);

			// Activate the plugin silently.
			$activated = activate_plugin(
				$plugin_basename,
				'',
				$network,
				true
			);

			if ( ! is_wp_error( $activated ) ) {
				update_option( 'simpay_connect_upgraded', time() );

				wp_send_json_success(
					array(
						'message' => esc_html__(
							'Plugin installed & activated.',
							'stripe'
						),
						'url'     => add_query_arg(
							'is_upgraded',
							1,
							$url
						),
					)
				);
			} else {
				// Reactivate the Lite plugin if Pro activation failed.
				activate_plugin(
					'stripe/stripe-checkout.php',
					'',
					$network,
					true
				);

				wp_send_json_error(
					array(
						'message' => esc_html__(
							'Please activate WP Simple Pay Pro from your WordPress plugins page.',
							'stripe'
						),
					)
				);
			}
		}

		// Deactivate license if something went wrong.
		$this->license_manager->deactivate( $license );

		wp_send_json_error(
			array(
				'message' => $error,
			)
		);
	}

}
