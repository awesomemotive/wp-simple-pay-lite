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
	 * @since uknown
	 * @since 3.6.0 No longer used.
	 *
	 * @var Session object
	 */
	public $session = null;

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
		require_once( SIMPLE_PAY_INC . 'core/functions/template.php' );
		require_once( SIMPLE_PAY_INC . 'core/functions/shared.php' );
		require_once( SIMPLE_PAY_INC . 'core/functions/countries.php' );

		// Payments/Purchase Flow.
		require_once( SIMPLE_PAY_INC . 'core/payments/customer.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/paymentintent.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/payment-confirmation-template-tags.php' );

		// REST API.
		new REST_API();
		require_once( SIMPLE_PAY_INC . 'core/rest-api/functions.php' );

		// Stripe Checkout functionality.
		require_once( SIMPLE_PAY_INC . 'core/payments/stripe-checkout/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/stripe-checkout/session.php' );

		// Stripe Connect functionality.
		require_once( SIMPLE_PAY_INC . 'core/stripe-connect/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/stripe-connect/admin.php' );

		// Legacy.
		require_once( SIMPLE_PAY_INC . 'core/legacy/hooks.php' );
		require_once( SIMPLE_PAY_INC . 'core/legacy/class-payment-form.php' );

		// Cron functionality.
		$cron = new Cron();
		$cron->init();
		$cron->schedule_events();

		$this->objects = new Objects();

		new Post_Types();
		new Shortcodes();

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
		require_once( SIMPLE_PAY_INC . 'core/admin/functions/notices.php' );
		require_once( SIMPLE_PAY_INC . 'core/admin/functions/plugin-upgrade-notice.php' );

		// Promos (Lite-only).
		require_once( SIMPLE_PAY_INC . 'core/admin/notices/promos.php' );

		// Usage tracking functionality.
		require_once( SIMPLE_PAY_INC . 'core/admin/usage-tracking/functions.php' );

		new Admin\Assets();
		new Admin\Menus();
		new Admin\Notice_Manager();
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
}

/**
 * Start WP Simple Pay.
 */
function SimplePay() {
	return SimplePay::instance();
}

SimplePay();
