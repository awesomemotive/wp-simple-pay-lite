<?php


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
		
		public function __construct() {
			$this->base = Stripe_Checkout::get_instance();
			
			// Add the options page and menu item.
			// TODO: Move to admin class
			add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 2 );
			
			// Add plugin listing "Settings" action link.
			add_filter( 'plugin_action_links_' . plugin_basename( SC_PATH . $this->base->plugin_slug . '.php' ), array( $this, 'settings_link' ) );
			
			// Add upgrade link (if not already in Pro).
			if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
				add_filter( 'plugin_action_links_' . plugin_basename( SC_PATH . $this->base->plugin_slug . '.php' ), array( $this, 'purchase_pro_link' ) );
			}
			
			// Add "Upgrade to Pro" submenu link
			add_action( 'init', array( $this, 'admin_upgrade_link' ) );
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
				SC_IMG_PATH . 'icon-16x16.png'
			);
		}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page() {
			include_once( SC_VIEWS_PATH . 'admin-page.php' );
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
			$pro_link = sprintf( '<a href="%s">%s</a>', Stripe_Checkout_Misc::ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'plugin_listing', 'pro_upgrade' ), __( 'Purchase Pro', 'sc' ) );
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
				include_once( SC_CLASS_PATH . 'class-upgrade-link.php' );

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