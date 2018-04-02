<?php

/**
 * Admin class - Shared between SP Lite & Pro
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

			// We need to call a priority of 3 here to ensure that $sc_options has already been loaded.
			$old = get_option( 'sc_version' );

			if ( version_compare( $old, $base_class->version, '<' ) ) {
				delete_option( 'sc_upgrade_has_run' );
				delete_option( 'sc_had_upgrade' );
			}

			if ( false === get_option( 'sc_set_defaults' ) ) {
				add_action( 'admin_init', array( $this, 'set_default_settings' ), 12 );
			}

			// Set the admin tabs.
			add_action( 'admin_init', array( $this, 'set_admin_tabs' ) );

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ), 2 );

			// Add plugin listing "Settings" action link.
			add_filter( 'plugin_action_links_' . plugin_basename( SC_DIR_PATH . 'stripe-checkout.php' ), array(
				$this,
				'settings_link',
			) );

			// Add upgrade to Pro link (if not already in Pro).
			if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
				add_filter( 'plugin_action_links_' . plugin_basename( SC_DIR_PATH . 'stripe-checkout.php' ), array(
					$this,
					'purchase_pro_link',
				) );
			}

			// Run upgrade routine after plugins loaded.
			add_action( 'plugins_loaded', array( $this, 'plugin_upgrade' ) );

			add_filter( 'custom_menu_order', array( $this, 'set_menu_order' ) );

			add_filter( 'admin_footer_text', array( $this, 'add_footer_text' ) );
		}

		public function add_footer_text( $footer_text ) {

			if ( $this->viewing_this_plugin() ) {
				$footer_text = sprintf( __( 'If you like <strong>WP Simple Pay</strong> please leave us a %s rating. A huge thanks in advance!', 'stripe' ),
					'<a href="https://wordpress.org/support/plugin/stripe/reviews?rate=5#new-post" target="_blank" class="simpay-rating-link" data-rated="' .
					esc_attr__( 'Thanks :)', 'stripe' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
			}

			return $footer_text;
		}

		/**
		 * SP Lite & Pro need separate class files for the plugin upgrade processes (not upgrade purchase).
		 */
		public function plugin_upgrade() {

			// Don't proceed if upgrade flag already set.
			if ( false === get_option( 'sc_upgrade_has_run' ) ) {

				$upgrade_file = SC_DIR_PATH . 'classes/class-stripe-checkout-pro-upgrade.php';

				// Check for non-existence of Pro-only plugin class.
				if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
					$upgrade_file = SC_DIR_PATH . 'classes/class-stripe-checkout-lite-upgrade.php';
				}

				if ( file_exists( $upgrade_file ) ) {
					require_once $upgrade_file;
				}
			}
		}

		/**
		 * Setup the default settings
		 */
		public function set_default_settings() {
			global $sc_options;

			// Check if an upgrade has happened and if not then load default settings since it is a fresh install.
			if ( false === get_option( 'sc_set_defaults' ) && false === get_option( 'sc_had_upgrade' ) ) {

				$sc_options->add_setting( 'enable_remember', 1 );
				$sc_options->add_setting( 'uninstall_save_settings', 1 );

				do_action( 'sc_admin_defaults' );

				add_option( 'sc_set_defaults', 1 );
			}
		}

		/**
		 * Set the tabs in the admin area
		 */
		public function set_admin_tabs( $tabs ) {
			global $sc_options;

			$tabs = array(
				'stripe-keys' => __( 'Stripe Keys', 'stripe' ),
				'default'     => __( 'Default Settings', 'stripe' ),
			);

			$tabs = apply_filters( 'sc_admin_tabs', $tabs );

			$sc_options->set_tabs( $tabs );
		}

		/**
		 * Change menu-order
		 */
		public function set_menu_order( $menu_order ) {
			global $submenu;
			
			if ( isset( $submenu['simpay'] ) && ! empty( $submenu['simpay'] ) ) {

				$arr   = array();
				$arr[] = $submenu['simpay'][2];
				$arr[] = $submenu['simpay'][3];
				$arr[] = $submenu['simpay'][4];
				$arr[] = $submenu['simpay'][1];
				$arr[] = $submenu['simpay'][5];
				$arr[] = $submenu['simpay'][6];

				$submenu['simpay'] = $arr;
			}

			return $menu_order;
		}

		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function add_plugin_admin_menu() {

			global $base_class;

			$this->plugin_screen_hook_suffix[] = add_submenu_page( 'simpay', __( 'Legacy (1.6) Settings', 'stripe' ), __( 'Legacy (1.6) Settings', 'stripe' ), 'manage_options', $base_class->plugin_slug, array(
				$this,
			    'display_plugin_admin_page',
			) );

			remove_submenu_page( 'simpay','simpay' );
		}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page() {
			include_once( SC_DIR_PATH . 'views/admin-shared-main.php' );
		}

		/**
		 * Add Settings action link to left of existing action links on plugin listing page.
		 *
		 * @since   1.0.0
		 *
		 * @param   array $links Default plugin action links
		 *
		 * @return  array  $links  Amended plugin action links
		 */
		public function settings_link( $links ) {

			global $base_class;

			$setting_link = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( 'page', $base_class->plugin_slug, admin_url( 'admin.php' ) ) ), __( 'Settings', 'stripe' ) );
			array_unshift( $links, $setting_link );

			return $links;
		}

		/**
		 * Add settings action link for purchasing pro
		 */
		public function purchase_pro_link( $links ) {
			$pro_link = sprintf( '<a href="%s" target="_blank">%s</a>', Stripe_Checkout_Admin::ga_campaign_url( SIMPAY_PRO_UPGRADE_URL, 'settings-link', true ), __( 'Upgrade to Pro', 'stripe' ) );
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
		 * @param   string $base_url Plain URL to navigate to
		 * @param   string $content  GA "content" tracking value
		 * @param   bool   $raw      Use esc_url_raw instead (default = false)
		 *
		 * @return  string  $url        Full Google Analytics campaign URL
		 */
		public static function ga_campaign_url( $base_url, $content, $raw = false ) {
			// Set campaign var depending on if in Lite or Pro.
			$campaign = ( class_exists( 'Stripe_Checkout_Pro' ) ? 'pro-plugin' : 'free-plugin' );

			$url = add_query_arg( array(
				'utm_source' => 'inside-plugin',
				'utm_medium' => 'link',
				'utm_campaign' => $campaign,
				'utm_content' => $content // i.e. 'sidebar-link', 'settings-link'
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
