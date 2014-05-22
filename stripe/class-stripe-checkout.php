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

class Stripe_Checkout {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.1.1';

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
	protected $plugin_slug = 'stripe-checkout';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		
		// Load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
		
		if( ! get_option( 'sc_upgrade_has_run' ) ) {
			add_action( 'init', array( $this, 'upgrade_plugin' ), 0 );
		}
		
		// Include required files.
		add_action( 'init', array( $this, 'includes' ), 1 );
		
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 2 );

		// Enqueue admin styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		
		// Enqueue admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add admin notice after plugin activation. Also check if should be hidden.
		add_action( 'admin_notices', array( $this, 'admin_install_notice' ) );

		// Add plugin listing "Settings" action link.
		add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( __FILE__ ) . $this->plugin_slug . '.php' ), array( $this, 'settings_link' ) );
		
		// Set our plugin constants
		add_action( 'init', array( $this, 'setup_constants' ) );
		
		// Check WP version
		add_action( 'admin_init', array( $this, 'check_wp_version' ) );
		
		// Add public JS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
		
		// Add public CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
		
		// Filters to add the settings page titles
		add_filter( 'sc_settings_keys_title', array( $this, 'sc_settings_keys_title' ) );
		add_filter( 'sc_settings_default_title', array( $this, 'sc_settings_default_title' ) );
		
		// Hook into wp_footer so we can localize our script AFTER all the shortcodes have been processed
		add_action( 'wp_footer', array( $this, 'localize_shortcode_script' ) );
	}
	
	
	function upgrade_plugin() {

		//require_once( 'includes/upgrade.php' );
		
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
	
	
	function sc_settings_keys_title( $title ) {
		return __( 'Stripe Keys', 'sc' );
	}
	
	function sc_settings_default_title( $title ) {
		return __( 'Default Settings', 'sc' );
	}
	
	/**
	 * Load public facing CSS
	 * 
	 * @since 1.0.0
	 */
	function enqueue_public_styles() {
		
		if( sc_has_shortcode() ) {
			// Load Stripe CSS for button
			wp_enqueue_style( 'stripe-checkout-css', 'https://checkout.stripe.com/v3/checkout/button.css', array(), null );
		}
		
		// Only load after the user has clicked to pay
		if( isset( $_GET['payment'] ) ) {
			wp_enqueue_style( $this->plugin_slug . '-public', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
		}
	}
	
	/**
	 * Load public facing JS
	 * 
	 * @since 1.0.0
	 */
	function enqueue_public_scripts() {

		if( sc_has_shortcode() ) {
			wp_enqueue_script( 'jquery' );
		
			wp_enqueue_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', array(), null, true );
		
			wp_enqueue_script( $this->plugin_slug . '-public', plugins_url( 'js/public.js', __FILE__ ), array(), $this->version, true );
		}
		
	}
	
	/*
	 * Function to localize the script variables being sent from the shortcodes
	 * 
	 * @since 1.1.1
	 */
	function localize_shortcode_script() {
		
		global $script_vars;
		
		wp_localize_script( SC_PLUGIN_SLUG . '-public', 'sc_script', $script_vars );
		
		// clear it out after we use it
		$script_vars = array();
	}
	
	public function enqueue_admin_scripts() {
		
		if( $this->viewing_this_plugin() ) {
			wp_enqueue_script( $this->plugin_slug . '-bootstrap-switch', plugins_url( 'js/bootstrap-switch.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_slug . '-admin', plugins_url( 'js/admin.js', __FILE__ ), array(), $this->version, true );
		}
	}

	/**
	 * Enqueue admin-specific style sheets for this plugin's admin pages only.
	 *
	 * @since     1.0.0
	 */
	public function enqueue_admin_styles() {

		if ( $this->viewing_this_plugin() ) {
			wp_enqueue_style( $this->plugin_slug .'-bootstrap-switch', plugins_url( 'css/bootstrap-switch.min.css', __FILE__ ), array(), $this->version );
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}
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
			deactivate_plugins( SC_MAIN_FILE ); 
			wp_die( sprintf( __( $this->get_plugin_title() . ' requires WordPress version <strong>' . $required_wp_version . '</strong> to run properly. ' .
				'Please update WordPress before reactivating this plugin. <a href="%s">Return to Plugins</a>.', 'sc' ), get_admin_url( '', 'plugins.php' ) ) );
		}
	}
	
	/**
	 * Setup any plugin constants we need 
	 *
	 * @since    1.0.0
	 */
	public function setup_constants() {
		// Plugin slug.
		if ( ! defined( 'SC_PLUGIN_SLUG' ) ) {
			define( 'SC_PLUGIN_SLUG', $this->plugin_slug );
		}

		// Plugin folder URL.
		if ( ! defined( 'SC_PLUGIN_URL' ) ) {
			define( 'SC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
		
		// Plugin directory
		if ( ! defined( 'SC_PLUGIN_DIR' ) ) {
			define( 'SC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}
		
		// Plugin version
		if ( ! defined( 'SC_PLUGIN_VERSION' ) ) {
			define( 'SC_PLUGIN_VERSION', $this->version );
		}
	}
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function plugin_textdomain() {
		// Set filter for plugin's languages directory
		$sc_lang_dir = dirname( plugin_basename( SC_MAIN_FILE ) ) . '/languages/';
		$sc_lang_dir = apply_filters( 'sc_languages_directory', $sc_lang_dir );

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'sc' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'sc', $locale );

		// Setup paths to current locale file
		$mofile_local  = $sc_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/sc/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			load_textdomain( 'sc', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			load_textdomain( 'sc', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'sc', false, $sc_lang_dir );
		}

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
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate() {
		// Add value to indicate that we should show admin install notice.
		update_option( 'sc_show_admin_install_notice', 1 );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix[] = add_menu_page(
			$this->get_plugin_title() . ' ' . __( 'Settings', 'sc' ),
			$this->get_plugin_title(),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
		
		// Change the first option to text "Settings" instead of "Stripe Checkout"
		$this->plugin_screen_hook_suffix[] = add_submenu_page(
			$this->plugin_slug,
			$this->get_plugin_title() . ' ' . __( 'Settings', 'sc' ),
			__( 'Settings', 'sc' ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
		
		// Add Help submenu page
		$this->plugin_screen_hook_suffix[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Help', 'sc' ),
			__( 'Help', 'sc' ),
			'manage_options',
			$this->plugin_slug . '_help',
			array( $this, 'display_admin_help_page' )
		);
		
		// Add Add-Ons submenu page
		$this->plugin_screen_hook_suffix[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Add-ons', 'sc' ),
			__( 'Add-ons', 'sc' ),
			'manage_options',
			$this->plugin_slug . '_addons',
			array( $this, 'display_admin_addons_page' )
		);
	}
	
	function display_admin_addons_page() {
		echo 'hello world';
	}
	
	function display_admin_help_page() {
		include_once( 'views/admin-help.php' );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}
	
	/**
	 * Include required files (admin and frontend).
	 *
	 * @since     1.0.0
	 */
	public function includes() {
		
		global $sc_options;
		
		if( ! class_exists( 'Stripe' ) ) {
			require_once( 'stripe-php/Stripe.php' );
		}
		
		
		// Include any necessary functions
		include_once( 'includes/misc-functions.php' );
		
		// Include shortcode functions
		include_once( 'includes/shortcodes.php' );
		
		// Hooks examples
		//include_once( 'includes/hooks-examples.php' );
		//
		
		include_once( 'includes/register-settings.php' );
		
		//$sc_options = sc_get_settings();
		sc_set_defaults();
		
		$sc_options = sc_get_settings();

		// Upgrade
		
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
	 * Add Settings action link to left of existing action links on plugin listing page.
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $links  Default plugin action links
	 * @return  array  $links  Amended plugin action links
	 */
	public function settings_link( $links ) {

		$setting_link = sprintf( '<a href="%s">%s</a>', add_query_arg( 'page', $this->plugin_slug, admin_url( 'options-general.php' ) ), __( 'Settings', 'sc' ) );
		array_unshift( $links, $setting_link );

		return $links;
	}

	/**
	 * Check if viewing this plugin's admin page.
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	private function viewing_this_plugin() {
		/*if ( ! isset( $this->plugin_screen_hook_suffix ) )
			return false;*/

		$screen = get_current_screen();

		/*if ( $screen->id == $this->plugin_screen_hook_suffix )
			return true;
		else
			return false;*/
		
		if( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
			return true;
		}
		
		
		return false;
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
		// or if on a PIB admin page. Then exit.
		if ( ! empty( $_REQUEST['sc-dismiss-install-nag'] ) || $this->viewing_this_plugin() ) {
			delete_option( 'sc_show_admin_install_notice' );
			return;
		}

		// At this point show install notice. Show it only on the plugin screen.
		if( get_current_screen()->id == 'plugins' ) {
			include_once( 'views/admin-install-notice.php' );
		}
	}
}
