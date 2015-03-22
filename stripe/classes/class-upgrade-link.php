<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Stripe_Checkout_Upgrade_Link' ) ) {
	class Stripe_Checkout_Upgrade_Link {

		protected static $instance = null;

		public $plugin_slug;

		public $version;

		public function __construct() {

			$this->plugin_slug = Stripe_Checkout::get_instance()->plugin_slug;
			$this->version     = Stripe_Checkout::get_instance()->version;

			add_action( 'admin_footer', array( $this, 'load_scripts' ) );
			add_action( 'admin_menu', array( $this, 'upgrade_link' ) );
		}

		public function upgrade_link() {
			$page_hook = add_submenu_page( 
					SC_PLUGIN_SLUG, 
					__( 'Upgrade to Pro', 'sc' ), 
					__( 'Upgrade to Pro', 'sc' ), 
					'manage_options', 
					SC_PLUGIN_SLUG . '-upgrade', 
					array( $this, 'redirect' )
				);

			add_action( 'load-' . $page_hook , array( $this, 'upgrade_ob_start' ) );
		}

		public function upgrade_ob_start() {
			ob_start();
		}

		public function redirect() {
			wp_redirect( Stripe_Checkout_Misc::ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'plugin_menu', 'pro_upgrade' ), 301 );
			exit();
		}

		public function load_scripts() {
			wp_enqueue_style( $this->plugin_slug .'-upgrade-link', SC_CSS_PATH . 'admin-upgrade-link.css', array(), $this->version );
			wp_enqueue_script( $this->plugin_slug . '-upgrade-link', SC_JS_PATH. 'admin-upgrade-link.js', array( 'jquery' ), $this->version, true );
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
