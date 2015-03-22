<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Stripe_Checkout_Misc' ) ) {
	class Stripe_Checkout_Misc {

		// Class variables

		protected static $instance = null;

		private static $zero_decimal_currencies = array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VUV', 'XAF', 'XOF', 'XPF' );

		public function __construct() {
			// class constructor
			// TODO: load filters/hooks here?

			add_filter( 'the_content', array( $this, 'shortcode_fix' ) );
		}

		public static function to_decimal_amount( $amount, $currency ) { 

			if ( ! self::is_zero_decimal_currency( $currency) ) {
				// Always round to 2 decimals.
				$amount = round( $amount / 100, 2 );
			}

			return $amount;
		}

		public static function to_formatted_amount( $amount, $currency ) { 

			// First convert to decimal if needed.
			$amount = self::to_decimal_amount( $amount, $currency );

			// Use 2 decimals unless zero-decimal currency.
			$formatted_amount = number_format_i18n( $amount, ( self::is_zero_decimal_currency( $currency ) ? 0 : 2 ) );

			return $formatted_amount;
		}

		public static function has_shortcode() {
			global $post;

			// Currently ( 5/8/2014 ) the has_shortcode() function will not find a 
			// nested shortcode. This seems to do the trick currently, will switch if 
			// has_shortcode() gets updated. -NY
			if ( strpos( $post->post_content, '[stripe' ) !== false ) {
				return true;
			}

			return false;
		}

		/**
		 * Google Analytics campaign URL.
		 *
		 * @since   1.1.1
		 *
		 * @param   string  $base_url Plain URL to navigate to
		 * @param   string  $source   GA "source" tracking value
		 * @param   string  $medium   GA "medium" tracking value
		 * @param   string  $campaign GA "campaign" tracking value
		 * @return  string  $url      Full Google Analytics campaign URL
		 */
		public static function ga_campaign_url( $base_url, $source, $medium, $campaign ) { 
			// $medium examples: 'sidebar_link', 'banner_image'

			$url = add_query_arg( array(
				'utm_source'   => $source,
				'utm_medium'   => $medium,
				'utm_campaign' => $campaign
			), $base_url );

			return $url;
		}

		public static function disable_seo_og() { 

			if ( $sc_payment_details['show'] == true ) {
				remove_action( 'template_redirect', 'wpseo_frontend_head_init', 999 );
			}
		}

		/**
		 * Filters the content to remove any extra paragraph or break tags
		 * caused by shortcodes.
		 *
		 * @since 1.0.0
		 *
		 * @param string $content  String of HTML content.
		 * @return string $content Amended string of HTML content.
		 * 
		 * REF: https://thomasgriffin.io/remove-empty-paragraph-tags-shortcodes-wordpress/
		 */
		public static function shortcode_fix( $content ) { 
			$array = array(
				'<p>['    => '[',
				']</p>'   => ']',
				']<br />' => ']'
			);

			return strtr( $content, $array );
		}

		// Private functions
		private static function is_zero_decimal_currency( $currency ) { 
			return in_array( strtoupper( $currency ), self::$zero_decimal_currencies );
		}


		// Return instance of this class
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
	
	Stripe_Checkout_Misc::get_instance();
}