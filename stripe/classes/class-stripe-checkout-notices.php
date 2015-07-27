<?php

/**
 * Notices class file
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Notices' ) ) {
	class Stripe_Checkout_Notices {
		
		// Class instance variable
		public static $instance = null;
		
		/*
		 * Class constructor
		 */
		private function __construct() {
			
			// Add admin notice after plugin activation. Also check if should be hidden.
			add_action( 'admin_notices', array( $this, 'admin_install_notice' ) );
			
			// Check WP version
			add_action( 'admin_init', array( $this, 'check_wp_version' ) );
		}
		
		/**
		 * Show notice after plugin install/activate in admin dashboard.
		 * Hide after first viewing.
		 *
		 * @since   1.0.0
		 */
		public function admin_install_notice() {
			// Exit all of this is stored value is false/0 or not set.
			if ( false == get_option( 'sc_show_admin_install_notice' ) ) {
				return;
			}

			// Delete stored value if "hide" button click detected (custom querystring value set to 1).
			if ( ! empty( $_REQUEST['sc-dismiss-install-nag'] ) || Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {
				delete_option( 'sc_show_admin_install_notice' );
				return;
			}

			// At this point show install notice. Show it only on the plugin screen.
			if( 'plugins' == get_current_screen()->id ) {
				include_once( SC_DIR_PATH . 'views/admin-notice-install.php' );
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