<?php

/**
 * Notices class - Shared between SP Lite & Pro
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

			// Add API notice after plugin upgrade (version 1.5.4)
			add_action( 'admin_notices', array( $this, 'admin_api_notice' ) );
		}

		public function admin_api_notice() {
			// Exit all of this is stored value is false/0 or not set.
			if ( false == get_option( 'sc_show_api_notice' ) ) {
				return;
			}

			// Delete stored value if "hide" button click detected (custom querystring value set to 1).
			if ( ! empty( $_REQUEST['sc-dismiss-api-nag'] ) || Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {
				delete_option( 'sc_show_api_notice' );
				return;
			}

			// At this point show install notice. Show it only on the plugin screen.
			//if( 'plugins' == get_current_screen()->id ) {
				include_once( SC_DIR_PATH . 'views/admin-shared-notice-api.php' );
			//}
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
				include_once( SC_DIR_PATH . 'views/admin-shared-notice-install.php' );
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
