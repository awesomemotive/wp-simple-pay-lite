<?php

/**
 * Upgrade class - SP Lite
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Upgrade' ) ) {
	class Stripe_Checkout_Upgrade {
		
		/**
		 * Class instance variable
		 */
		protected static $instance = null;
		
		/**
		 * Class constructor.
		 */
		private function __construct() {
			
			global $base_class;
			
			$old_version = get_option( 'sc_version' );
	
			if( ! empty( $old_version ) ) {

				if( version_compare( $old_version, '1.4.0', '<' ) ) {
					add_action( 'admin_init', array( $this, 'v140_upgrade' ), 11 );
				}
			}

			if ( version_compare( $old_version, '1.5.4', '<' ) ) {
				add_action( 'admin_init', array( $this, 'v154_upgrade' ), 12 );
			}

			$new_version = $base_class->version;

			update_option( 'sc_version', $new_version );
			add_option( 'sc_upgrade_has_run', 1 );
		}

		public function v154_upgrade() {

			global $sc_options;

			$test_sec = $sc_options->get_setting_value( 'test_secret_key_temp' );
			$test_pub = $sc_options->get_setting_value( 'test_publishable_key_temp' );
			$live_sec = $sc_options->get_setting_value( 'live_secret_key_temp' );
			$live_pub = $sc_options->get_setting_value( 'live_publishable_key_temp' );

			if ( isset( $test_sec ) || isset( $test_pub ) || isset( $live_sec ) || isset( $live_pub ) ) {

				// Delete the old options out
				$sc_options->delete_setting( 'live_secret_key_temp' );
				$sc_options->delete_setting( 'test_secret_key_temp' );
				$sc_options->delete_setting( 'live_publishable_key_temp' );
				$sc_options->delete_setting( 'test_publishable_key_temp' );

				add_option( 'sc_show_api_notice', 1 );
			}
		}
		
		/**
		 * Run upgrade routine for version 1.4.0
		 */
		public function v140_upgrade() {
	
			global $sc_options;

			// sc_settings_master holds a merge of all settings arrays tied to the Stripe plugin. This includes any settings that are implemented by users.
			$master = get_option( 'sc_settings_master' );

			// Loop through the old settings and add them to the new structure
			foreach ( $master as $option => $value ) {
				$sc_options->add_setting( $option, $value );
			}
			
			add_option( 'sc_had_upgrade', 1 );
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
	
	Stripe_Checkout_Upgrade::get_instance();
}
