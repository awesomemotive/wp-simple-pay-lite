<?php

/**
 * Misc class - Shared between SP Lite & Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Misc' ) ) {
	class Stripe_Checkout_Misc {

		// Class instance variable
		protected static $instance = null;
		
		/**
		 * Class constructor.
		 */
		private function __construct() {
			// Add filter to fix shortcode spacing issues
			add_filter( 'the_content', array( $this, 'shortcode_fix' ) );
		}
		
		/**
		 * Function to turn amount into a readable number with decimal places.
		 */
		public static function to_decimal_amount( $amount, $currency ) { 

			if ( ! self::is_zero_decimal_currency( $currency ) ) {
				// Always round to 2 decimals.
				$amount = round( $amount / 100, 2 );
			}

			return $amount;
		}
		
		/**
		 * Format the amount.
		 */
		public static function to_formatted_amount( $amount, $currency ) { 

			// First convert to decimal if needed.
			$amount = self::to_decimal_amount( $amount, $currency );

			// Use 2 decimals unless zero-decimal currency.
			$formatted_amount = number_format_i18n( $amount, ( self::is_zero_decimal_currency( $currency ) ? 0 : 2 ) );

			return $formatted_amount;
		}
		
		/**
		 * Function to find out if there is a shortcode on the page.
		 */
		public static function has_shortcode() {
			global $post;

			// Currently ( 5/8/2014 ) the has_shortcode() function will not find a 
			// nested shortcode. This seems to do the trick currently, will switch if 
			// has_shortcode() gets updated. -NY
			if ( false !== strpos( $post->post_content, '[stripe' ) ) {
				return true;
			}

			return false;
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
				']<br />' => ']',
			);

			return strtr( $content, $array );
		}

		/**
		 * Check if currency is a zero decimal currency or not.
		 */
		private static function is_zero_decimal_currency( $currency ) { 
			return in_array( strtoupper( $currency ), self::zero_decimal_currencies() );
		}

		/**
		 * List of zero-decimal currencies according to Stripe.
		 * Needed for PHP and JS.
		 * See: https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
		 *
		 * @since 2.0.0
		 */
		public static function zero_decimal_currencies() {
			return array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' );
		}

		/**
		 * Return instance of this class.
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
