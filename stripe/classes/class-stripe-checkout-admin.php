<?php

/**
 * Stripe Checkout Admin class file
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Admin' ) ) {
	class Stripe_Checkout_Admin {
		
		public static $instance = null;
		
		public $base;
		
		/**
		 * Slug of the plugin screen.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		public $plugin_screen_hook_suffix = null;
		
		private function __construct() {
			$this->base = Stripe_Checkout::get_instance();
			
			add_action( 'init', array( $this, 'upgrade_plugin' ) , 2 );
			
			add_action( 'init', array( $this, 'set_default_settings' ) );
			
			add_action( 'admin_init', array( $this, 'set_admin_tabs' ) );
			
			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 2 );
			
			// Add plugin listing "Settings" action link.
			add_filter( 'plugin_action_links_' . plugin_basename( SC_DIR_PATH . $this->base->plugin_slug . '.php' ), array( $this, 'settings_link' ) );
			
			// Add upgrade link (if not already in Pro).
			if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
				add_filter( 'plugin_action_links_' . plugin_basename( SC_DIR_PATH . $this->base->plugin_slug . '.php' ), array( $this, 'purchase_pro_link' ) );
			}
			
			// Add "Upgrade to Pro" submenu link
			add_action( 'init', array( $this, 'admin_upgrade_link' ) );
		}
		
		public function set_default_settings() {
			global $sc_options;
			
			$defaults = array(
				$sc_options->get_setting_id( 'enable_remember' )         => 1,
				$sc_options->get_setting_id( 'uninstall_save_settings' ) => 1,
			);
			
			$sc_options->set_defaults( $defaults );
		}
		
		/**
		 * Function to smoothly upgrade from version 1.1.0 to 1.1.1 of the plugin
		 * 
		 * @since 1.1.1
		 */
		public function upgrade_plugin() {
			global $sc_options;
			
			if ( null === $sc_options->get_setting_value( 'upgrade_has_run' ) ) {
				// We need to check for the super old option also here.
				if ( version_compare( $this->base->version, '1.3.1', '>=' ) ) {
					$super_old_version = get_option( 'sc_version' );
				}

				if ( false === $super_old_version ) {
					if ( null === $sc_options->get_setting_value( 'sc_version' ) ) {
						$sc_options->add_setting( 'sc_version', $this->base->version );
					} else {
						$old = $sc_options->get_setting_value( 'sc_version' );
						
						$sc_options->add_setting( 'old_version', $old );

						if ( version_compare( $old, $this->base->version, '<' ) ) {
							include_once( SC_DIR_PATH . 'upgrade.php' );
						}
					}
				} else {
					$sc_options->add_setting( 'old_version', $super_old_version );
					include_once( SC_DIR_PATH . 'upgrade.php' );
				}
			}
		}
		
		public function set_admin_tabs() {
			global $sc_options;
			
			$sc_options->set_tabs( array(
				'keys'    => __( 'Stripe Keys', 'sc' ),
				'default' => __( 'Default Settings', 'sc' ),
			) );
		}
		
		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function add_plugin_admin_menu() {
			
			$this->plugin_screen_hook_suffix[] = add_menu_page(
				$this->base->get_plugin_title() . ' ' . __( 'Settings', 'sc' ),
				$this->base->get_plugin_title(),
				'manage_options',
				$this->base->plugin_slug,
				array( $this, 'display_plugin_admin_page' ),
				SC_DIR_URL . 'assets/img/icon-16x16.png'
			);
		}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page() {
			include_once( SC_DIR_PATH . 'views/admin-page.php' );
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

			$setting_link = sprintf( '<a href="%s">%s</a>', add_query_arg( 'page', $this->base->plugin_slug, admin_url( 'admin.php' ) ), __( 'Settings', 'sc' ) );
			array_unshift( $links, $setting_link );

			return $links;
		}

		public function purchase_pro_link( $links ) {
			$pro_link = sprintf( '<a href="%s">%s</a>', Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'plugin_listing', 'pro_upgrade' ), __( 'Purchase Pro', 'sc' ) );
			array_push( $links, $pro_link );

			return $links;
		}
		
		/**
		 * Add "Upgrade to Pro" submenu link
		 * 
		 * @since 1.2.5
		 */
		function admin_upgrade_link() {
			if ( is_admin() ) {
				include_once( SC_DIR_PATH . 'classes/class-stripe-checkout-upgrade-link.php' );

				//Stripe_Checkout_Upgrade_Link::get_instance();
			}
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
			
			//echo '<pre>' . print_r( $this->plugin_screen_hook_suffix, true ) . '</pre>';

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
		public static function ga_campaign_url( $base_url, $source, $medium, $campaign ) { 
			// $medium examples: 'sidebar_link', 'banner_image'

			$url = add_query_arg( array(
				'utm_source'   => $source,
				'utm_medium'   => $medium,
				'utm_campaign' => $campaign
			), $base_url );

			return $url;
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
	
	Stripe_Checkout_Admin::get_instance();
}