<?php
/**
 * Shortcodes class - SP Lite
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Shortcodes' ) ) {
	
	class Stripe_Checkout_Shortcodes {
		
		// class instance variable
		private static $instance = null;
		
		/*
		 * class constructor
		 */
		private function __construct() {
			// Add the shortcode functionality
			add_shortcode( 'stripe', array( $this, 'stripe_shortcode' ) );
		}
		
		/**
		* Function to process the [stripe] shortcode
		* 
		* @since 1.0.0
		*/
	   function stripe_shortcode( $attr, $content = null ) {

		   global $sc_options;

		   STATIC $sc_id = 0;
		   
		   $sc_id++;

		   $attr = shortcode_atts( array(
						   'name'                      => ( null !== $sc_options->get_setting_value( 'name' ) ? $sc_options->get_setting_value( 'name' ) : get_bloginfo( 'title' ) ),
						   'description'               => '',
						   'amount'                    => '',
						   'image_url'                 => ( null !== $sc_options->get_setting_value( 'image_url' ) ? $sc_options->get_setting_value( 'image_url' ) : '' ),
						   'currency'                  => ( null !== $sc_options->get_setting_value( 'currency' ) ? $sc_options->get_setting_value( 'currency' ) : 'USD' ),
						   'checkout_button_label'     => ( null !== $sc_options->get_setting_value( 'checkout_button_label' ) ? $sc_options->get_setting_value( 'checkout_button_label' ) : '' ),
						   'billing'                   => ( null !== $sc_options->get_setting_value( 'billing' ) ? 'true' : 'false' ),    // true or false
						   'payment_button_label'      => ( null !== $sc_options->get_setting_value( 'payment_button_label' ) ? $sc_options->get_setting_value( 'payment_button_label' ) : __( 'Pay with Card', 'stripe' ) ),
						   'enable_remember'           => ( null !== $sc_options->get_setting_value( 'enable_remember' ) ? 'true' : 'false' ),    // true or false
						   'bitcoin'                   => ( null !== $sc_options->get_setting_value( 'use_bitcoin' ) ? 'true' : 'false' ),    // true or false
						   'alipay'                    => ( null !== $sc_options->get_setting_value( 'alipay' ) ? $sc_options->get_setting_value( 'alipay' ) : 'false' ),
						   'alipay_reusable'           => ( null !== $sc_options->get_setting_value( 'alipay_reusable' ) ? 'true' : 'false' ),
						   'locale'                    => ( null !== $sc_options->get_setting_value( 'locale' ) ? $sc_options->get_setting_value( 'locale' ) : 'auto' ),
						   'success_redirect_url'      => ( null !== $sc_options->get_setting_value( 'success_redirect_url' ) ? $sc_options->get_setting_value( 'success_redirect_url' ) : get_permalink() ),
						   'failure_redirect_url'      => ( null !== $sc_options->get_setting_value( 'failure_redirect_url' ) ? $sc_options->get_setting_value( 'failure_redirect_url' ) : get_permalink() ),
						   'prefill_email'             => 'false',
						   'verify_zip'                => ( null !== $sc_options->get_setting_value( 'verify_zip' ) ? 'true' : 'false' ),
						   'test_mode'                 => 'false',
						   'id'                        => null,
						   'payment_details_placement' => 'above',
						   'test_secret_key'           => '',
						   'test_publishable_key'      => '',
						   'live_secret_key'           => '',
						   'live_publishable_key'      => '',
					   ), $attr, 'stripe' );
		   
		   // Assign variables since we are not using extract
		   $name                      = $attr['name'];
		   $description               = $attr['description'];
		   $amount                    = $attr['amount'];
		   $image_url                 = $attr['image_url'];
		   $currency                  = $attr['currency'];
		   $checkout_button_label     = $attr['checkout_button_label'];
		   $billing                   = $attr['billing'];
		   $payment_button_label      = $attr['payment_button_label'];
		   $enable_remember           = $attr['enable_remember'];
		   $bitcoin                   = $attr['bitcoin'];
		   $alipay                    = $attr['alipay'];
		   $alipay_reusable           = $attr['alipay_reusable'];
		   $locale                    = $attr['locale'];
		   $success_redirect_url      = $attr['success_redirect_url'];
		   $failure_redirect_url      = $attr['failure_redirect_url'];
		   $prefill_email             = $attr['prefill_email'];
		   $verify_zip                = $attr['verify_zip'];
		   $test_mode                 = $attr['test_mode'];
		   $id                        = $attr['id'];
		   $payment_details_placement = $attr['payment_details_placement'];

			// Generate custom form id attribute if one not specified.
			// Rename var for clarity.
			$form_id = $id;
			if ( $form_id === null || empty( $form_id ) ) {
				$form_id = 'sc_checkout_form_' . $sc_id;
			}
		   
		   $test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : $test_mode );
		   $filter_mode = false;

		   // Check if in test mode or live mode
		   if ( 0 == $sc_options->get_setting_value( 'enable_live_key' ) || 'true' == $test_mode ) {

			   // Test mode
			   $filter_mode = true;

			   $data_key = ( null !== $sc_options->get_setting_value( 'test_publish_key' ) ? $sc_options->get_setting_value( 'test_publish_key' ) : '' );
		   } else {

			   // Live mode
			   $data_key = ( null !== $sc_options->get_setting_value( 'live_publish_key' ) ? $sc_options->get_setting_value( 'live_publish_key' ) : '' );
		   }

		   $data_key = apply_filters( 'simpay_publishable_key', $data_key, $filter_mode );

		   if ( empty( $data_key ) ) {

			   if ( current_user_can( 'manage_options' ) ) {
				   return '<h6>' . __( 'You must enter your API keys before the Stripe button will show up here.', 'stripe' ) . '</h6>';
			   }

			   return '';
		   }

		   if ( ! empty( $prefill_email ) && $prefill_email !== 'false' ) {
			   // Get current logged in user email
			   if ( is_user_logged_in() ) {
				   $prefill_email = get_userdata( get_current_user_id() )->user_email;
			   } else { 
				   $prefill_email = 'false';
			   }
		   }

		   $html  = '<form id="' . esc_attr( $form_id ) . '" method="POST" action="" data-sc-id="' . $sc_id . '" class="' . $this->get_form_classes() . '">';

		   // Add filter for adding html before payment button.
		   $html .= apply_filters( 'sc_before_payment_button', '' );
		   
		   $html .= '<script
					   src="https://checkout.stripe.com/checkout.js" class="stripe-button"
					   data-key="' . esc_js( $data_key ) . '" ' .
					   ( ! empty( $image_url ) ? 'data-image="' . esc_js( $image_url ) . '" ' : '' ) .
					   ( ! empty( $name ) ? 'data-name="' . esc_js( $name ) . '" ' : '' ) .
					   ( ! empty( $description ) ? 'data-description="' . esc_js( $description ) . '" ' : '' ) .
					   ( ! empty( $amount ) ? 'data-amount="' . esc_js( $amount ) . '" ' : '' ) .
					   ( ! empty( $currency ) ? 'data-currency="' . esc_js( $currency ) . '" ' : '' ) .
					   ( ! empty( $checkout_button_label ) ? 'data-panel-label="' . esc_js( $checkout_button_label ) . '" ' : '' ) .
					   ( ! empty( $verify_zip ) ? 'data-zip-code="' . $verify_zip . '" ' : '' ) .
					   ( ! empty( $prefill_email ) && 'false' != $prefill_email ? 'data-email="' . $prefill_email . '" ' : '' ) .
					   ( ! empty( $payment_button_label ) ? 'data-label="' . esc_js( $payment_button_label ) . '" ' : '' ) .
					   ( ! empty( $enable_remember ) ? 'data-allow-remember-me="' . $enable_remember . '" ' : '' ) .
					   ( ! empty( $bitcoin ) ? 'data-bitcoin="' . $bitcoin . '" ' : '' ) .
					   ( ! empty( $billing ) ? 'data-billing-address="' . $billing . '" ' : '' ) .
					   ( ! empty( $alipay ) ? 'data-alipay="' . $alipay . '" ' : '' ) .
					   ( ( ! empty( $alipay_reusable ) && ( 'true' === $alipay ) ) ? 'data-alipay-reusable="' . $alipay_reusable . '" ' : '' ) . // Omit Alipay reusable if Alipay not enabled.
					   ( ! empty( $locale ) ? 'data-locale="' . $locale . '" ' : '' ) .
					   '></script>';

		   $html .= '<input type="hidden" name="sc-name" value="' . esc_attr( $name ) . '" />';
		   $html .= '<input type="hidden" name="sc-description" value="' . esc_attr( $description ) . '" />';
		   $html .= '<input type="hidden" name="sc-amount" class="sc_amount" value="' . esc_attr( $amount ) . '" />';
		   $html .= '<input type="hidden" name="sc-redirect" value="' . esc_attr( ( ! empty( $success_redirect_url ) ? $success_redirect_url : get_permalink() ) ) . '" />';
		   $html .= '<input type="hidden" name="sc-redirect-fail" value="' . esc_attr( ( ! empty( $failure_redirect_url ) ? $failure_redirect_url : get_permalink() ) ) . '" />';
		   $html .= '<input type="hidden" name="sc-currency" value="' .esc_attr( $currency ) . '" />';
		   $html .= '<input type="hidden" name="sc-details-placement" value="' . ( $payment_details_placement == 'below' ? 'below' : 'above' ) . '" />';
		   $html .= '<input type="hidden" name="wp-simple-pay" value="1" />';
		   $html .= wp_nonce_field( 'charge_card', 'wp-simple-pay-nonce', '', false );

		   if ( 'true' == $test_mode ) {
			   $html .= '<input type="hidden" name="sc_test_mode" value="true" />';
		   }

		   $html .= '</form>';

		   //Stripe minimum amount allowed.
		   $stripe_minimum_amount = 50;

		   if ( ( empty( $amount ) || $amount < $stripe_minimum_amount ) || ! isset( $amount ) ) {
			   
			   if ( current_user_can( 'manage_options' ) ) {
				   
				   $html  = '<h6>';
				   $html .= sprintf( __( 'WP Simple Pay for Stripe requires an amount of %1$s (%2$s %3$s) or larger.', 'stripe' ),
									$stripe_minimum_amount, Stripe_Checkout_Misc::to_formatted_amount( $stripe_minimum_amount, $currency ), $currency );
				   $html .= '</h6>';

				   return $html;
			   }

			   return '';

		   } elseif ( ! isset( $_GET['charge'] ) ) {
			   return $html;
		   }

		   return '';
	   }

		// Helper method for adding custom CSS classes to checkout form.
		public function get_form_classes() {
			// Set default class.
			$classes   = array();
			$classes[] = 'sc-checkout-form';

			// Allow filtering of classes and then return what's left.
			$classes = apply_filters( 'simpay_form_class', $classes );

			return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );
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
