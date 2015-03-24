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
			// TODO: Leave in this main class
			add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
			
			// TODO: Move to admin class?
			if( ! get_option( 'sc_upgrade_has_run' ) ) {
				add_action( 'init', array( $this, 'upgrade_plugin' ), 0 );
			}

			// Include required files.
			// TODO: Do we still need this?
			$this->setup_constants();
			
			// TODO: Leave in main class and put into one function
			//add_action( 'init', array( $this, 'load_classes' ), 0 );
			//add_action( 'init', array( $this, 'includes' ), 1 );
			$this->includes();

			
			

			

			// Add admin notice after plugin activation. Also check if should be hidden.
			// TODO: Create and add to admin notice class
			add_action( 'admin_notices', array( $this, 'admin_install_notice' ) );

			

			

			

			// Check WP version
			// TODO: Add to admin notices class
			add_action( 'admin_init', array( $this, 'check_wp_version' ) );

			

			// Filters to add the settings page titles
			// TODO: Remove these since settings are changed now?
			add_filter( 'sc_settings_keys_title', array( $this, 'sc_settings_keys_title' ) );
			add_filter( 'sc_settings_default_title', array( $this, 'sc_settings_default_title' ) );

			
			
			// TODO: leave here or add to a base settings class?
			add_action( 'init', array( $this, 'register_settings' ), 1 );
		}
		
		/*function init() {
			$this->load_classes();
		}*/

		function register_settings() {
			global $sc_options;

			//include_once( SC_PATH . 'includes/abstract-register-settings.php' );
			//include_once( SC_PATH . 'includes/class-mm-settings.php' );
			//include_once( SC_PATH . 'includes/class-mm-settings-callbacks.php' );
			//include_once( SC_PATH . 'includes/class-sc-settings-extension.php' );

			//include_once( SC_PATH . 'includes/settings.php' );

			//$mm_settings = new MM_Settings( 'sc', $sc_settings );


			//$sc_options = $mm_settings->get_settings();
			include_once( SC_CLASS_PATH . 'class-mm-settings.php' );
			include_once( SC_CLASS_PATH . 'class-mm-settings-output.php' );
			include_once( SC_CLASS_PATH . 'class-mm-settings-extended.php' );

			global $settings;

			// We load the exteded class here so that it will load all of the class functions all the way back to the base
			$settings = new MM_Settings_Extended( 'sc_settings' );

		}

		

		

		/**
		 * Function to smoothly upgrade from version 1.1.0 to 1.1.1 of the plugin
		 * 
		 * @since 1.1.1
		 */
		function upgrade_plugin() {

			$keys_options = get_option( 'sc_settings_general' );

			// Check if test mode was enabled
			if( isset( $keys_options['enable_test_key'] ) && $keys_options['enable_test_key'] == 1 ) {
				// if it was then we remove it because we are now checking if live is enabled, not test
				unset( $keys_options['enable_test_key'] );
			} else {

				// If was not in test mode then we need to set our new value to true
				$keys_options['enable_live_key'] = 1;
			}

			// Delete old option settings from old version of SC
			delete_option( 'sc_settings_general' );

			// Update our new settings options
			update_option( 'sc_settings_keys', $keys_options );

			// Update version number option for future upgrades
			update_option( 'sc_version', $this->version );

			// Let us know that we ran the upgrade
			add_option( 'sc_upgrade_has_run', 1 );
		}

		/**
		 * Set the title of the 'Stripe Keys' tab
		 * 
		 * @since 1.1.1
		 */
		function sc_settings_keys_title( $title ) {
			return __( 'Stripe Keys', 'sc' );
		}

		/**
		 * Set the title of the 'Default Settings' tab
		 * 
		 * @since 1.1.1
		 */
		function sc_settings_default_title( $title ) {
			return __( 'Site-wide Default Settings', 'sc' );
		}

		

		/**
		 * Make sure user has the minimum required version of WordPress installed to use the plugin
		 * 
		 * @since 1.0.0
		 */
		public function check_wp_version() {
			global $wp_version;
			$required_wp_version = '3.6.1';

			if ( version_compare( $wp_version, $required_wp_version, '<' ) ) {
				//deactivate_plugins( SC_MAIN_FILE ); 
				//wp_die( sprintf( __( $this->get_plugin_title() . ' requires WordPress version <strong>' . $required_wp_version . '</strong> to run properly. ' .
				//	'Please update WordPress before reactivating this plugin. <a href="%s">Return to Plugins</a>.', 'sc' ), get_admin_url( '', 'plugins.php' ) ) );
			}
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
				SC_LANGUAGES_PATH
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
			include_once( SC_CLASS_PATH . 'class-stripe-checkout-admin.php' );
			include_once( SC_CLASS_PATH . 'class-mm-settings.php' );
			include_once( SC_CLASS_PATH . 'class-mm-settings-output.php' );
			include_once( SC_CLASS_PATH . 'class-mm-settings-extended.php' );
			include_once( SC_CLASS_PATH . 'class-stripe-checkout-functions.php' );
			include_once( SC_CLASS_PATH . 'class-stripe-checkout-misc.php' );
			include_once( SC_CLASS_PATH . 'class-stripe-checkout-scripts.php' );
			include_once( SC_CLASS_PATH . 'class-upgrade-link.php' );
			
			// Include shortcode functions
			include_once( SC_INCLUDES_PATH . 'shortcodes.php' );
			
			
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


		

		

		/**
		 * Show notice after plugin install/activate in admin dashboard.
		 * Hide after first viewing.
		 *
		 * @since   1.0.0
		 */
		public function admin_install_notice() {
			// Exit all of this is stored value is false/0 or not set.
			if ( false == get_option( 'sc_show_admin_install_notice' ) )
				return;

			// Delete stored value if "hide" button click detected (custom querystring value set to 1).
			if ( ! empty( $_REQUEST['sc-dismiss-install-nag'] ) || $this->viewing_this_plugin() ) {
				delete_option( 'sc_show_admin_install_notice' );
				return;
			}

			// At this point show install notice. Show it only on the plugin screen.
			if( get_current_screen()->id == 'plugins' ) {
				include_once( SC_VIEWS_PATH . 'admin-install-notice.php' );
			}
		}
	}
	
	// Create the class instance
	Stripe_Checkout::get_instance();
}
