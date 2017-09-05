<?php

/**
 * Scripts class - SP Lite
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Scripts' ) ) {

	class Stripe_Checkout_Scripts {

		// class instance variable
		public static $instance = null;

		private $min = '';

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		private function __construct() {

			$this->min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			// Front-end JS/CSS
			// https://checkout.stripe.com/checkout.js is currently loaded inline on front-end.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );

			// Admin JS/CSS
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		}

		/**
		 * Enqueue Front-end Styles
		 *
		 * @since 1.0.0
		 */
		public function enqueue_frontend_styles() {

			global $base_class, $sc_options;

			// First check for disable CSS option
			if ( null !== $sc_options->get_setting_value( 'disable_css' ) ) {
				return;
			}

			$css_dir = SC_DIR_URL . 'assets/css/';

			wp_register_style( $base_class->plugin_slug . '-public-lite', $css_dir . 'shared-public-main' . $this->min . '.css', array(), $base_class->version );
			wp_enqueue_style( $base_class->plugin_slug . '-public-lite' );
		}

		/**
		 * Enqueue Admin Scripts
		 *
		 * @since 1.0.0
		 */
		public function enqueue_admin_scripts() {

			global $base_class;

			$js_dir = SC_DIR_URL . 'assets/js/';

			if ( Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {

				// Prefix local JS libraries to prevent clashing.
				wp_register_script( $base_class->plugin_slug . '-admin-lite', $js_dir . 'shared-admin-main' . $this->min . '.js', array( 'jquery' ), $base_class->version, true );
				wp_enqueue_script( $base_class->plugin_slug . '-admin-lite' );
			}
		}

		/**
		 * Enqueue Admin Styles
		 *
		 * @since 1.0.0
		 */
		public function enqueue_admin_styles() {

			global $base_class;

			$css_dir = SC_DIR_URL . 'assets/css/';

			if ( Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {

				wp_register_style( $base_class->plugin_slug . '-admin-lite', $css_dir . 'shared-admin-main' . $this->min . '.css', array(), $base_class->version );
				wp_enqueue_style( $base_class->plugin_slug . '-admin-lite' );
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
