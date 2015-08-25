<?php

/**
 * Main class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout' ) ) {
	class Stripe_Checkout {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		public $version = '1.4.3';

		/**
		 * Unique identifier for your plugin.
		 *
		 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
		 * match the Text Domain file header in the main plugin file.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		public $plugin_slug = 'stripe-checkout';

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;
		
		/**
		 * Initialize the plugin
		 *
		 * @since     1.0.0
		 */
		private function __construct() {
			
			// Load plugin text domain
			add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
			
			// Include all necessary files
			$this->includes();
			
			add_action( 'init', array( $this, 'register_settings' ), 0 );
			
			// Load all instances
			add_action( 'init', array( $this, 'init' ), 1 );
		}
		
		/**
		 * Register the settings and load settings class
		 */
		public function register_settings() {
			global $sc_options;

			// We load the extended class here so that it will load all of the class functions all the way back to the base
			$sc_options = new Stripe_Checkout_Settings_Extended( 'sc_settings' );
			
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function plugin_textdomain() {
			load_plugin_textdomain(
				'sc',
				false,
				SC_DIR_PATH . 'languages/'
			);

		}

		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {
			// Add value to indicate that we should show admin install notice.
			update_option( 'sc_show_admin_install_notice', 1 );
		}

		/**
		 * Include required files (admin and frontend).
		 *
		 * @since     1.0.0
		 */
		public function includes() {
			
			include_once( SC_DIR_PATH . 'classes/class-mm-settings.php' );
			include_once( SC_DIR_PATH . 'classes/class-mm-settings-output.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-settings-extended.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-scripts.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-shortcodes.php' );
			
			// Admin side
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-admin.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-upgrade-link.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-notices.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-system-status.php' );
				
			// Public side
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-functions.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-misc.php' );
			
			// Old functions after everything else
			include_once( SC_DIR_PATH . 'includes/old-functions.php' );
		}
		
		/**
		 * Get the instance for all the included classes
		 */
		public function init() {
			Stripe_Checkout_Scripts::get_instance();
			Stripe_Checkout_Shortcodes::get_instance();
			
			if ( is_admin() ) {
				Stripe_Checkout_Admin::get_instance();
				Stripe_Checkout_Upgrade_Link::get_instance();
				Stripe_Checkout_Notices::get_instance();
				Stripe_Checkout_System_Status::get_instance();
			} else {
				Stripe_Checkout_Misc::get_instance();
				Stripe_Checkout_Functions::get_instance();
			}
		}

		/**
		 * Return localized base plugin title.
		 *
		 * @since     1.0.0
		 *
		 * @return    string
		 */
		public static function get_plugin_title() {
			return __( 'WP Simple Pay Lite for Stripe', 'sc' );
		}
		
		
		public static function get_pro_plugin_title() {
			return __( 'WP Simple Pay Pro for Stripe', 'sc' );
		}

		public static function get_plugin_menu_title() {
			return __( 'Simple Pay Lite', 'sc' );
		}
	}
}
