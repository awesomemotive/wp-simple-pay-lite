<?php

/**
 * Admin class file
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Admin' ) ) {
	class Stripe_Checkout_Admin {
		
		// Class instance variable
		public static $instance = null;
		
		/**
		 * Slug of the plugin screen.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		public $plugin_screen_hook_suffix = null;
		
		/**
		 * Class constructor
		 */
		private function __construct() {
			
			global $base_class;
			
			// We need to call a priority of 3 here to ensure that $sc_options has already been loaded
			$old = get_option( 'sc_version' );
		
			if( version_compare( $old, $base_class->version, '<' ) ) {
				delete_option( 'sc_upgrade_has_run' );
				delete_option( 'sc_had_upgrade' );
			} 
				
			if ( false === get_option( 'sc_set_defaults' ) ) {
				add_action( 'admin_init', array( $this, 'set_default_settings' ), 12 );
			}

			if( false === get_option( 'sc_upgrade_has_run' ) ) {
				$this->upgrade();
			}
			
			// Set the admin tabs
			add_action( 'admin_init', array( $this, 'set_admin_tabs' ) );
			
			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 2 );
			
			// Add plugin listing "Settings" action link.
			add_filter( 'plugin_action_links_' . plugin_basename( SC_DIR_PATH . 'stripe-checkout.php' ), array( $this, 'settings_link' ) );
			
			// Add upgrade link (if not already in Pro).
			if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
				add_filter( 'plugin_action_links_' . plugin_basename( SC_DIR_PATH . 'stripe-checkout.php' ), array( $this, 'purchase_pro_link' ) );
			}
		}
		
		public function upgrade() {
			require_once( SC_DIR_PATH . 'classes/class-stripe-checkout-upgrade.php' );
		}
		
		/*
		 * Setup the default settings
		 */
		public function set_default_settings() {
			global $sc_options;
		
			// Check if an upgrade has happened and if not then load default settings since it is a fresh install.
			if ( false === get_option( 'sc_set_defaults' ) && false === get_option( 'sc_had_upgrade' ) ) {
				
				$sc_options->add_setting( 'enable_remember', 1 );
				$sc_options->add_setting( 'uninstall_save_settings', 1 );
				$sc_options->add_setting( 'always_enqueue', 1 );
				
				do_action( 'sc_admin_defaults' );
				
				add_option( 'sc_set_defaults', 1 );
			}
		}
		
		/*
		 * Set the tabs in the admin area
		 */
		public function set_admin_tabs( $tabs ) {
			global $sc_options;
			
			$tabs = array(
				'stripe-keys'    => __( 'Stripe Keys', 'sc' ),
				'default'        => __( 'Default Settings', 'sc' ),
			);
			
			$tabs = apply_filters( 'sc_admin_tabs', $tabs );
			
			$sc_options->set_tabs( $tabs );
		}
		
		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function add_plugin_admin_menu() {
			
			global $base_class;
			
			$this->plugin_screen_hook_suffix[] = add_menu_page(
				$base_class->get_plugin_title() . ' ' . __( 'Settings', 'sc' ),
				$base_class->get_plugin_menu_title(),
				'manage_options',
				$base_class->plugin_slug,
				array( $this, 'display_plugin_admin_page' ),
				SC_DIR_URL . 'assets/img/icon-16x16.png'
			);

			$this->plugin_screen_hook_suffix[] = add_submenu_page(
				$base_class->plugin_slug,
				__( 'System Report', 'sc' ),
				__( 'System Report', 'sc' ),
				'manage_options',
				$base_class->plugin_slug . '-tools',
				array( 'Stripe_Checkout_System_Status', 'set_content' )
			);
		}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page() {
			include_once( SC_DIR_PATH . 'views/admin-main.php' );
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
			
			global $base_class;
			
			$setting_link = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( 'page', $base_class->plugin_slug, admin_url( 'admin.php' ) ) ), __( 'Settings', 'sc' ) );
			array_unshift( $links, $setting_link );

			return $links;
		}
		
		/**
		 * Add settings action link for purchasing pro
		 */
		public function purchase_pro_link( $links ) {
			$pro_link = sprintf( '<a href="%s">%s</a>', Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe-checkout', 'plugin-listing', 'pro-upgrade' ), __( 'Purchase Pro', 'sc' ) );
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
		public function viewing_this_plugin() {

			$screen = get_current_screen();

			if ( ! empty( $this->plugin_screen_hook_suffix ) && in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
				return true;
			}

			return false;
		}
		
		/**
		 * Google Analytics campaign URL.
		 *
		 * @since   1.1.1
		 *
		 * @param   string  $base_url Plain URL to navigate to
		 * @param   string  $source   GA "source" tracking value
		 * @param   string  $medium   GA "medium" tracking value
		 * @param   string  $campaign GA "campaign" tracking value
		 * @return  string  $url      Full Google Analytics campaign URL
		 */
		public static function ga_campaign_url( $base_url, $source, $medium, $campaign, $raw = false ) { 
			// $medium examples: 'sidebar_link', 'banner_image'

			$url = add_query_arg( array(
				'utm_source'   => $source,
				'utm_medium'   => $medium,
				'utm_campaign' => $campaign,
			), $base_url );
			
			if ( $raw ) {
				return esc_url_raw( $url );
			}
			
			return esc_url( $url );
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
	}
}
