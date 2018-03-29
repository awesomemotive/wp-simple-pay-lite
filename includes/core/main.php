<?php

namespace SimplePay\Core;

use SimplePay\Core\Forms\Preview;
use SimplePay\Core\Payments\Stripe_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core SimplePay Class
 */
final class SimplePay {

	/**
	 * Objects factory
	 */
	public $objects = null;

	/**
	 * Session object
	 *
	 * @var Session object
	 */
	public $session;

	/**
	 * The single instance of this class
	 */
	protected static $_instance = null;

	/**
	 * Main Simple Pay instance
	 *
	 * Ensures only one instance of Simple Pay is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'stripe' ), '3.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'stripe' ), '3.0' );
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->load();

		register_activation_hook( SIMPLE_PAY_MAIN_FILE, array( 'SimplePay\Core\Installation', 'activate' ) );
		register_deactivation_hook( SIMPLE_PAY_MAIN_FILE, array( 'SimplePay\Core\Installation', 'deactivate' ) );

		add_action( 'init', array( $this, 'setup_preview_form' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ), 5 );

		add_action( 'in_plugin_update_message-' . plugin_basename( SIMPLE_PAY_MAIN_FILE ), array(
			$this,
			'in_plugin_update_message',
		), 10, 2 );

		do_action( 'simpay_loaded' );
	}

	/**
	 * Load the preview class.
	 */
	public function setup_preview_form() {

		if ( ! isset( $_GET['simpay-preview'] ) ) {
			return '';
		}

		new Preview();
	}

	/**
	 * Load the plugin.
	 */
	public function load() {

		// Load core shared back-end & front-end functions.
		require_once( SIMPLE_PAY_INC . 'core/functions/shared.php' );

		// TODO Don't load sessions in admin after Pro multi-plan setup fee set/get is refactored.
		$this->session = new Session();

		$this->objects = new Objects();

		new Errors();
		new Payments\Setup();
		new Post_Types();
		new Shortcodes();
		new Stripe_API();

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->load_admin();
		} else {
			Assets::get_instance();
			new Cache_Helper();
		}
	}

	/**
	 * Load the plugin admin.
	 */
	public function load_admin() {

		// Load core back-end only functions.
		require_once( SIMPLE_PAY_INC . 'core/functions/admin.php' );

		new Admin\Assets();
		new Admin\Menus();
		new Admin\Notices();
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) && ! defined( 'DOING_AJAX' ) ) {
			$settings = new Admin\Pages();
			$settings->register_settings( $settings->get_settings() );
		}
	}

	/**
	 * Get common URLs.
	 */
	public function get_url( $case ) {

		switch ( $case ) {
			case 'docs' :
				$url = 'https://docs.wpsimplepay.com/';
				break;
			case 'upgrade':
				$url = 'https://wpsimplepay.com/lite-vs-pro/';
				break;
			case 'home' :
			default :
				$url = SIMPLE_PAY_STORE_URL;
		}

		return esc_url( apply_filters( 'simpay_get_url', $url, $case ) );
	}

	/**
	 * Show inline plugin update message from remote readme.txt `== Upgrade Notice ==` section.
	 * Code adapted from W3 Total Cache & WooCommerce.
	 *
	 * @param array  $args     Unused parameter.
	 * @param object $response Plugin update response.
	 */
	public function in_plugin_update_message( $args, $response ) {

		$new_version = $response->new_version;
		$upgrade_notice = $this->get_upgrade_notice( $new_version );

		echo apply_filters( 'simpay_in_plugin_update_message', $upgrade_notice ? '</p>' . wp_kses_post( $upgrade_notice ) . '<p class="dummy">' : '' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the upgrade notice from hosted readme.txt file.
	 *
	 * @param  string $version Plugin's new version.
	 *
	 * @return string
	 */
	protected function get_upgrade_notice( $version ) {

		$transient_name = 'simpay_upgrade_notice_' . $version;
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {

			// Pro readme.txt from wpsimplepay.com
			//$response = wp_safe_remote_get( 'https://wpsimplepay.com/readmes/pro3/readme.txt' );

			// Lite readme.txt from wordpress.org
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/stripe/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'], $version );

				// TODO set_transient( $transient_name, $upgrade_notice, HOUR_IN_SECONDS * 12 );

				// Expire transient quickly for testing.
				set_transient( $transient_name, $upgrade_notice, 1 );
			}
		}

		return $upgrade_notice;
	}

	/**
	 * Parse update notice from readme.txt file.
	 *
	 * @param  string $content readme.txt file content.
	 * @param  string $new_version Plugin's new version.
	 * @return string
	 */
	private function parse_update_notice( $content, $new_version ) {

		$notice_regexp          = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';
		$upgrade_notice         = '';
		$upgrade_notice_version = '';

		if ( version_compare( SIMPLE_PAY_VERSION, $new_version, '>' ) ) {
			return '';
		}

		$matches = null;

		if ( preg_match( $notice_regexp, $content, $matches ) ) {
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			$upgrade_notice_version = trim( $matches[1] );

			if ( version_compare( SIMPLE_PAY_VERSION, $upgrade_notice_version, '<' ) ) {
				$upgrade_notice .= '<p class="simpay_plugin_upgrade_notice">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
				}

				$upgrade_notice .= '</p>';
			}
		}

		return wp_kses_post( $upgrade_notice );
	}

}

/**
 * Start WP Simple Pay.
 */
function SimplePay() {
	return SimplePay::instance();
}

SimplePay();
