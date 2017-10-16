<?php

if ( ! class_exists( 'Promo_Loader' ) ) {
	class Promo_Loader {

		private static $instance = null;


		public function __construct() {

			// Load the promos on the metabox tabs

			// Payment options tab
			add_action( 'simpay_form_settings_meta_payment_options_panel', array( $this, 'payment_options_tab' ) );

			// General Options tab
			add_action( 'simpay_admin_after_general_options', array( $this, 'general_options_tab' ) );

			// On-Page Form Display tab
			add_action( 'simpay_form_settings_meta_form_display_panel', array( $this, 'form_display_tab' ) );

			// Checkout Overlay tab
			add_action( 'simpay_form_settings_meta_overlay_display_panel', array( $this, 'checkout_overlay_tab' ) );
		}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function payment_options_tab() {
			include( 'views/promo-under-box-header.php' );
			include( 'views/generic-tab-promo.php' );
			include( 'views/promo-under-box-footer.php' );
		}

		public function general_options_tab() {
			include( 'views/promo-under-box-header.php' );
			include( 'views/generic-tab-promo.php' );
			include( 'views/promo-under-box-footer.php' );
		}

		public function form_display_tab() {
			include( 'views/promo-under-box-header.php' );
			include( 'views/generic-tab-promo.php' );
			include( 'views/promo-under-box-footer.php' );
		}

		public function checkout_overlay_tab() {
			include( 'views/promo-under-box-header.php' );
			include( 'views/generic-tab-promo.php' );
			include( 'views/promo-under-box-footer.php' );
		}
	}
}

if ( ! function_exists( 'simpay_promo_loader' ) ) {

	function simpay_promo_loader() {
		return Promo_Loader::get_instance();
	}
}

simpay_promo_loader();
