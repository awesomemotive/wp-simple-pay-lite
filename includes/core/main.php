<?php

namespace SimplePay\Core;

use SimplePay\Core\Forms\Preview;
use SimplePay\Core\Payments\Setup;
use SimplePay\Core\Payments\Stripe_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class SimplePay {

	/**
	 * Plugin version.
	 *
	 * @access public
	 * @var string
	 */
	public static $version = SIMPLE_PAY_VERSION;

	/**
	 * Plugin homepage.
	 *
	 * @access public
	 * @var string
	 */
	protected static $homepage = SIMPLE_PAY_STORE_URL;

	/**
	 * Locale.
	 *
	 * @access public
	 * @var string
	 */
	public $locale = 'en_US';

	/**
	 * Objects factory.
	 *
	 * @access public
	 * @var object
	 */
	public $objects = null;

	/**
	 * The single instance of this class.
	 *
	 * @access protected
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * Main Simple Pay instance.
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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'stripe' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'stripe' ), '2.1' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Load plugin.
		$this->locale = apply_filters( 'plugin_locale', get_locale(), 'simple-pay' );
		$this->load();

		// Installation hooks.
		register_activation_hook( SIMPLE_PAY_MAIN_FILE, array( 'SimplePay\Core\Installation', 'activate' ) );
		register_deactivation_hook( SIMPLE_PAY_MAIN_FILE, array( 'SimplePay\Core\Installation', 'deactivate' ) );

		// Load plugin settings
		add_action( 'admin_init', array( $this, 'register_settings' ), 5 );

		$this->load_payment_class();

		// Upon plugin loaded action hook.
		do_action( 'simpay_loaded' );

		add_action( 'init', array( $this, 'setup_preview_form' ) );
	}

	/**
	 * Load the preview class if we need to
	 *
	 * @return string
	 */
	public function setup_preview_form() {

		if ( ! isset( $_GET['simpay-preview'] ) ) {
			return '';
		}

		// Create the preview form we will use to store preview data
		$preview_form_id = get_option( 'simpay_preview_form_id' );

		if ( ! $preview_form_id ) {
			$form = wp_insert_post( array(
				'post_type'   => 'simple-pay',
				'post_status' => 'private',
			) );

			if ( $form ) {
				update_option( 'simpay_preview_form_id', $form );
			} else {
				wp_die( 'An error occurred with preview.' );
			}
		}

		new Preview();
	}

	/**
	 * Load the payment class
	 */
	public function load_payment_class() {
		new Setup();
	}

	/**
	 * Load the plugin
	 */
	public function load() {

		// Load core shared back-end & front-end functions.
		require_once( 'functions/shared.php' );

		new Stripe_API();

		// Include WP Native PHP Sessions plugin code by Pantheon so we can use $_SESSION the native way.
		// This includes session_start() just like non-WP PHP apps would.
		require_once( 'libraries/wp-native-php-sessions/pantheon-sessions.php' );
		require_once( 'session.php' );

		// Check for session already started.
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}

		$this->objects = new Objects();

		if ( is_admin() ) {
			$this->load_admin();
		} else {
			Assets::get_instance();
			new Cache_Helper();
		}

		new Post_Types();

		new Shortcodes();
	}

	/**
	 * Load the plugin admin
	 */
	public function load_admin() {

		// Load core back-end only functions.
		require_once( 'functions/admin.php' );

		new Admin\Assets();

		new Admin\Menus();

		new Admin\Notices();
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 3.0.0
	 */
	public function register_settings() {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$settings = new Admin\Pages();
			$settings->register_settings( $settings->get_settings() );
		}
	}

	/**
	 * Get URL.
	 *
	 * @since  3.0.0
	 *
	 * @param  string $case Requested url.
	 *
	 * @return string
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
				$url = self::$homepage;
		}

		return esc_url( apply_filters( 'simpay_get_url', $url, $case ) );
	}
}

/**
 * Simple Pay
 *
 * @return SimplePay
 */
function SimplePay() {
	return SimplePay::instance();
}

SimplePay();
