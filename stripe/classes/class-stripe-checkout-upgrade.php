<?php

/**
 * Stripe Checkout Upgrade class
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Upgrade' ) ) {
	class Stripe_Checkout_Upgrade {
		
		protected static $instance = null;
		
		private function __construct() {
			add_action( 'init', array( $this, 'run_all_upgrades' ) );
		}
		
		public function run_all_upgrades() {
			global $sc_options;
	
			$version = $sc_options->get_setting_value( 'old_version' );

			if ( null !== $version ) {
				if ( version_compare( $version, '1.3.1', '<' ) && null === $sc_options->get_setting_value( 'upgrade_has_run' ) ) {
					$this->sc_v113_upgrade();
				}
			}

			$new_version = Stripe_Checkout::get_instance()->version;
			$sc_options->add_setting( 'sc_version', $new_version );
		}

		// TODO: Remove old upgrade routines when new restructure upgrade in place.

		private function sc_v113_upgrade() {
	
			global $sc_options;

			// sc_settings_master holds a merge of all settings arrays tied to the Stripe plugin. This includes any settings that are implemented by users.
			$master = get_option( 'sc_settings_master' );

			// Loop through the old settings and add them to the new structure
			foreach ( $master as $option => $value ) {
				$sc_options->add_setting( $option, $value );
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
