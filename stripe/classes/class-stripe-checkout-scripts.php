<?php

/**
 * Stripe Checkout Scripts class file
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Stripe_Checkout_Scripts' ) ) {
	
	class Stripe_Checkout_Scripts {
		
		public static $instance = null;
		
		//public $version = null;
		//public $plugin_slug = null;
		public $base = null;
		
		public function __construct() {
			
			//$this->version = Stripe_Checkout::get_instance()->version;
			//$this->plugin_slug = Stripe_Checkout::get_instance()->plugin_slug;
			
			$this->base = Stripe_Checkout::get_instance();
			
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

			global $sc_options;

			if ( empty( $posts ) ) {
				return $posts;
			}

			foreach ( $posts as $post ){
				if ( ( strpos( $post->post_content, '[stripe' ) !== false ) || ( $sc_options->get_setting_value( 'always_enqueue' ) !== null ) ) {
					// Load CSS
					wp_enqueue_style( $this->base->plugin_slug . '-public' );

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

			global $sc_options;

			if( $sc_options->get_setting_value( 'disable_css' ) === null ) {
				wp_register_style( $this->base->plugin_slug . '-public', SC_DIR_URL . 'assets/css/public-main.css', array(), $this->base->version );
			}
		}

		/**
		 * Enqueue admin-specific style sheets for this plugin's admin pages only.
		 *
		 * @since     1.0.0
		 */
		public function enqueue_admin_styles() {

			if ( Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {
				wp_enqueue_style( $this->base->plugin_slug .'-toggle-switch', SC_DIR_URL . 'assets/css/toggle-switch.css', array(), $this->base->version );
				wp_enqueue_style( $this->base->plugin_slug .'-admin-styles', SC_DIR_URL . 'assets/css/admin-main.css', array( $this->base->plugin_slug .'-toggle-switch' ), $this->base->version );
			}

			wp_enqueue_script( $this->base->plugin_slug . '-admin', SC_DIR_URL . 'assets/js/admin-main.js', array( 'jquery' ), $this->base->version, true );
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
	
	Stripe_Checkout_Scripts::get_instance();
}
