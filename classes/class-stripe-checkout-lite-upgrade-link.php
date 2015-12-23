<?php

/**
 * Upgrade Link class - SP Lite
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Upgrade_Link' ) ) {
	class Stripe_Checkout_Upgrade_Link {
		
		// Class instance variable
		protected static $instance = null;
		
		/*
		 * Class constructor
		 */
		private function __construct() {
			
			// Load spcific scripts for upgrade link
			add_action( 'admin_footer', array( $this, 'load_scripts' ) );
			
			// Add link to the admin menu
			add_action( 'admin_menu', array( $this, 'upgrade_link' ) );
		}
		
		/*
		 * Add the link to the admin menu
		 */
		public function upgrade_link() {
			
			global $base_class;
			
			$page_hook = add_submenu_page( 
					$base_class->plugin_slug, 
					__( 'Upgrade to Pro', 'stripe' ),
					__( 'Upgrade to Pro', 'stripe' ),
					'manage_options', 
					$base_class->plugin_slug . '-upgrade', 
					array( $this, 'redirect' )
				);

			add_action( 'load-' . $page_hook , array( $this, 'upgrade_ob_start' ) );
		}
		
		/*
		 * Start output buffer
		 */
		public function upgrade_ob_start() {
			ob_start();
		}
		
		/*
		 * Set the redirect for the link when clicked
		 */
		public function redirect() {
			wp_redirect( Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL, 'plugin-submenu-link', true ), 301 );
			exit();
		}
		
		/*
		 * Load specific scripts
		 */
		public function load_scripts() {
			
			global $base_class;

			$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_style( $base_class->plugin_slug .'-upgrade-link', SC_DIR_URL . 'assets/css/lite-admin-upgrade-link' . $min . '.css', array(), $base_class->version );
			wp_enqueue_script( $base_class->plugin_slug . '-upgrade-link', SC_DIR_URL . 'assets/js/lite-admin-upgrade-link' . $min . '.js', array( 'jquery' ), $base_class->version, true );
		}

		/**
		 * Return an instance of this class.
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
