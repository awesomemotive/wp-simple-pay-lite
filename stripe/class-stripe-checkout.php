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
	protected $version = '1.2.8';

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
	
	public $session;

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
		$this->setup_constants();
		$this->includes();
		
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

		// Add upgrade link (if not already in Pro).
		if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
			add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( __FILE__ ) . $this->plugin_slug . '.php' ), array( $this, 'purchase_pro_link' ) );
		}
		
		// Add "Upgrade to Pro" submenu link
		add_action( 'init', array( $this, 'admin_upgrade_link' ) );
		
		// Check WP version
		add_action( 'admin_init', array( $this, 'check_wp_version' ) );
		
		// Add public CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
		
		// Filters to add the settings page titles
		add_filter( 'sc_settings_keys_title', array( $this, 'sc_settings_keys_title' ) );
		add_filter( 'sc_settings_default_title', array( $this, 'sc_settings_default_title' ) );
	}
	
	/**
	 * Add "Upgrade to Pro" submenu link
	 * 
	 * @since 1.2.5
	 */
	function admin_upgrade_link() {
		if( is_admin() ) {
			include_once( SC_PATH . 'admin/includes/upgrade-link.php' );
		}
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
	 * Load public facing CSS
	 * 
	 * @since 1.0.0
	 */
	function enqueue_public_styles() {
		
		global $sc_options;
		
		if( empty( $sc_options['disable_css'] ) ) {
			wp_enqueue_style( $this->plugin_slug . '-public', SC_URL . 'public/css/public.css', array(), $this->version );
		}
	}
	
	/**
	 * Load admin scripts
	 * 
	 * @since 1.1.1
	 */
	public function enqueue_admin_scripts() {
		
		if( $this->viewing_this_plugin() ) {
			wp_enqueue_script( 'bootstrap-switch', SC_URL . 'admin/js/bootstrap-switch.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_slug . '-admin', SC_URL . 'admin/js/admin.js', array( 'jquery', 'bootstrap-switch' ), $this->version, true );
		}
	}

	/**
	 * Enqueue admin-specific style sheets for this plugin's admin pages only.
	 *
	 * @since     1.0.0
	 */
	public function enqueue_admin_styles() {

		if ( $this->viewing_this_plugin() ) {
			wp_enqueue_style( 'bootstrap-switch', SC_URL . 'admin/css/bootstrap-switch.min.css', array(), $this->version );
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', SC_URL . 'admin/css/admin.css', array( 'bootstrap-switch' ), $this->version );
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
			dirname( plugin_basename( SC_MAIN_FILE ) ) . '/languages/'
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
			array( $this, 'display_plugin_admin_page' ),
			plugins_url( '/assets/icon-16x16.png', __FILE__ )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( SC_PATH . 'admin/views/admin.php' );
	}
	
	/**
	 * Include required files (admin and frontend).
	 *
	 * @since     1.0.0
	 */
	public function includes() {
		
		global $sc_options;
		
		if( ! class_exists( 'Stripe' ) ) {
			require_once( 'libraries/stripe-php/Stripe.php' );
		}
		
		// Include any necessary functions
		include_once( SC_PATH . 'public/includes/misc-functions.php' );
		
		// Include shortcode functions
		include_once( SC_PATH . 'includes/shortcodes.php' );
		
		include_once( SC_PATH . 'includes/register-settings.php' );
		
		//$sc_options = sc_get_settings();
		sc_set_defaults();
		
		$sc_options = sc_get_settings();
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

		$setting_link = sprintf( '<a href="%s">%s</a>', add_query_arg( 'page', $this->plugin_slug, admin_url( 'admin.php' ) ), __( 'Settings', 'sc' ) );
		array_unshift( $links, $setting_link );

		return $links;
	}
	
	public function purchase_pro_link( $links ) {
		$pro_link = sprintf( '<a href="%s">%s</a>', sc_ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'plugin_listing', 'pro_upgrade' ), __( 'Purchase Pro', 'sc' ) );
		array_push( $links, $pro_link );
		
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

		$screen = get_current_screen();
		
		if( ! empty( $this->plugin_screen_hook_suffix ) && in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
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
		if ( ! empty( $_REQUEST['sc-dismiss-install-nag'] ) || $this->viewing_this_plugin() ) {
			delete_option( 'sc_show_admin_install_notice' );
			return;
		}

		// At this point show install notice. Show it only on the plugin screen.
		if( get_current_screen()->id == 'plugins' ) {
			include_once( SC_PATH . 'admin/views/admin-install-notice.php' );
		}
	}
}
