<?php

namespace SimplePay\Core\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Payment_Button extends Custom_Field {


	/**
	 * Payment_Button constructor.
	 */
	public function __construct() {
		// No constructor needed, but to keep consistent will keep it here but just blank
	}

	/**
	 * Print the HTML for the payment button on the frontend
	 *
	 * @param $settings
	 *
	 * @return string
	 */
	public static function print_html( $settings ) {

		$html = '';

		$id   = isset( $settings['id'] ) ? $settings['id'] : '';
		$text = isset( $settings['text'] ) && ! empty( $settings['text'] ) ? $settings['text'] : esc_html__( 'Pay with Card', 'stripe' );

		// Get the button style from the global display settings
		$general_options = get_option( 'simpay_settings_general' );
		$button_style    = isset( $general_options['styles']['payment_button_style'] ) && 'stripe' === $general_options['styles']['payment_button_style'] ? 'stripe-button-el' : '';

		$id = simpay_dashify( $id );

		$html .= '<div class="simpay-form-control">';
		$html .= '<button id="' . esc_attr( $id ) . '" class="simpay-payment-btn ' . esc_attr( $button_style ) . '"><span>' . esc_html( $text ) . '</span></button>';

		// Test mode badge placement
		if ( simpay_is_test_mode() ) {
			$html .= '<span class="simpay-test-mode-badge">' . esc_html__( 'Test Mode', 'stripe' ) . '</span>';
		}

		$html .= '</div>';

		return $html;
	}
}
