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

			$new_version = $base_class->version;

			// TODO This option update is not always getting run.
			update_option( 'sc_version', $new_version );
			add_option( 'sc_upgrade_has_run', 1 );
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
