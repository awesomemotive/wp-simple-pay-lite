<?php

/**
 * Scripts class file
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Scripts' ) ) {
	
	class Stripe_Checkout_Scripts {
		
		// class instance variable
		public static $instance = null;

		private $min = null;
		
		/*
		 * Class constructor
		 */
		private function __construct() {

			$this->min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		
			// Load scripts when posts load so we know if we need to include them or not
			add_filter( 'the_posts', array( $this, 'load_scripts' ) );
			
			// Add public CSS
			add_action( 'init', array( $this, 'enqueue_public_styles' ) );
			
			// Enqueue admin styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		}
		
		/**
		 * Function that will actually determine if the scripts should be used or not
		 * 
		 * @since 1.0.0
		 */
		public function load_scripts( $posts ){

			global $sc_options, $base_class;

			if ( empty( $posts ) ) {
				return $posts;
			}

			foreach ( $posts as $post ) {
				if ( ( false !== strpos( $post->post_content, '[stripe' ) ) || ( null !== $sc_options->get_setting_value( 'always_enqueue' ) ) ) {
					// Load CSS
					wp_enqueue_style( $base_class->plugin_slug . '-public' );

					break;
				}
			}

			return $posts;
		}
		
		/**
		 * Load public facing CSS
		 * 
		 * @since 1.0.0
		 */
		public function enqueue_public_styles() {

			global $sc_options, $base_class;

			if ( null === $sc_options->get_setting_value( 'disable_css' ) ) {
				wp_register_style( $base_class->plugin_slug . '-public', SC_DIR_URL . 'assets/css/public-main' . $this->min . '.css', array(), $base_class->version );
			}
		}

		/**
		 * Enqueue admin-specific style sheets for this plugin's admin pages only.
		 *
		 * @since     1.0.0
		 */
		public function enqueue_admin_styles() {
			
			global $base_class;
			
			if ( Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {
				wp_enqueue_style( $base_class->plugin_slug .'-toggle-switch', SC_DIR_URL . 'assets/css/vendor/toggle-switch' . $this->min . '.css', array(), $base_class->version );
				wp_enqueue_style( $base_class->plugin_slug .'-admin-styles', SC_DIR_URL . 'assets/css/admin-main' . $this->min . '.css', array( $base_class->plugin_slug .'-toggle-switch' ), $base_class->version );
			}

			wp_enqueue_script( $base_class->plugin_slug . '-admin', SC_DIR_URL . 'assets/js/admin-main' . $this->min . '.js', array( 'jquery' ), $base_class->version, true );
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
