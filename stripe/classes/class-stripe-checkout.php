<?php

/**
 * Main Stripe Checkout class
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Stripe_Checkout' ) ) {
	class Stripe_Checkout {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		public $version = '1.3.1';

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

		

		public $session;
		
		/**
		 * Initialize the plugin by setting localization, filters, and administration functions.
		 *
		 * @since     1.0.0
		 */
		private function __construct() {

			// Load plugin text domain
			add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
			
			// Setup constants
			$this->setup_constants();
			
			// Include all necessary files
			$this->includes();

			add_action( 'init', array( $this, 'register_settings' ), 1 );
		}

		function register_settings() {
			global $sc_options;

			// We load the exteded class here so that it will load all of the class functions all the way back to the base
			$sc_options = new MM_Settings_Extended( 'sc_settings' );

		}

		/**
		 * Setup any plugin constants we need 
		 *
		 * @since    1.0.0
		 */
		public function setup_constants() {
			
			// Website for this plugin
			if( ! defined( 'SC_WEBSITE_BASE_URL' ) ) {
				define( 'SC_WEBSITE_BASE_URL', 'http://wpstripe.net/' );
			}
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
			
			// Classes
			// Shared between public/admin side
			include_once( SC_DIR_PATH . 'classes/class-mm-settings.php' );
			include_once( SC_DIR_PATH . 'classes/class-mm-settings-output.php' );
			include_once( SC_DIR_PATH . 'classes/class-mm-settings-extended.php' );
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-scripts.php' );
			
			if( is_admin() ) {
				// Admin side only
				include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-admin.php' );
				include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-upgrade-link.php' );
				include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-notices.php' );
			} else {
				// Public side only
				include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-functions.php' );
				include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-misc.php' );
			}
			
			// Include shortcode functions
			include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-shortcodes.php' );
		}

		/**
		 * Return localized base plugin title.
		 *
		 * @since     1.0.0
		 *
		 * @return    string
		 */
		public static function get_plugin_title() {
			return __( 'Stripe Checkout', 'sc' );
		}
	}
	
	// Create the class instance
	Stripe_Checkout::get_instance();
}
