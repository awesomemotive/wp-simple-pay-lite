<?php

/**
 * MM settings class extension - Shared between SP Lite & Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Settings_Extended' ) ) {
	class Stripe_Checkout_Settings_Extended extends MM_Settings_Output {
		
		/**
		 * Class constructor
		 * 
		 * @param string $option This is the name of the option that will be used in the database
		 */
		public function __construct( $option ) {
			parent::__construct( $option );
		}
		
		/**
		 * The function used to create the Stripe live mode toggle.
		 */
		public function sc_live_mode_toggle() {

			// Since we're very specific to this one control right now.
			$control_id = 'enable_live_key';

			$setting_value = $this->get_setting_value( $control_id );
			$setting_id = $this->get_setting_id( $control_id );
			$esc_setting_id = esc_attr( $setting_id );

			$checked = ( ! empty( $setting_value ) ? checked( 1, $setting_value, false ) : '' );

			$html  = '<div class="sc-livemode-onoffswitch">' . "\n";
			$html .= '<input type="checkbox" id="' . $esc_setting_id . '" name="' . $esc_setting_id . '" class="sc-livemode-onoffswitch-checkbox" value="1" ' . $checked . '>';
			$html .= '<label class="sc-livemode-onoffswitch-label" for="' . $esc_setting_id . '">' . "\n";
			$html .= '<span class="sc-livemode-onoffswitch-inner"></span>' . "\n";
			$html .= '<span class="sc-livemode-onoffswitch-switch"></span>' . "\n";
			$html .= '</label>' . "\n";
			$html .= '</div>' . "\n";

			echo $html;
		}
	}
}
