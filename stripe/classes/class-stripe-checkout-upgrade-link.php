<?php

/**
 * Stripe Checkout Upgrade Link class file
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Stripe_Checkout_Upgrade_Link' ) ) {
	class Stripe_Checkout_Upgrade_Link {

		protected static $instance = null;
		
		public $base = null;

		private function __construct() {

			//$this->plugin_slug = Stripe_Checkout::get_instance()->plugin_slug;
			//$this->version     = Stripe_Checkout::get_instance()->version;
			
			$this->base = Stripe_Checkout::get_instance();

			add_action( 'admin_footer', array( $this, 'load_scripts' ) );
			add_action( 'admin_menu', array( $this, 'upgrade_link' ) );
		}

		public function upgrade_link() {
			$page_hook = add_submenu_page( 
					$this->base->plugin_slug, 
					__( 'Upgrade to Pro', 'sc' ), 
					__( 'Upgrade to Pro', 'sc' ), 
					'manage_options', 
					$this->base->plugin_slug . '-upgrade', 
					array( $this, 'redirect' )
				);

			add_action( 'load-' . $page_hook , array( $this, 'upgrade_ob_start' ) );
		}

		public function upgrade_ob_start() {
			ob_start();
		}

		public function redirect() {
			wp_redirect( Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'plugin_menu', 'pro_upgrade' ), 301 );
			exit();
		}

		public function load_scripts() {
			wp_enqueue_style( $this->base->plugin_slug .'-upgrade-link', SC_DIR_PATH . 'assets/css/admin-upgrade-link.css', array(), $this->base->version );
			wp_enqueue_script( $this->base->plugin_slug . '-upgrade-link', SC_DIR_PATH. 'assets/js/admin-upgrade-link.js', array( 'jquery' ), $this->base->version, true );
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
	
	Stripe_Checkout_Upgrade_Link::get_instance();
}
