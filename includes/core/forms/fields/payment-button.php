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
		$button_style    = isset( $general_options['styles']['payment_button_style'] ) && 'stripe' === $general_options['styles']['payment_button_style'] ? 'stripe-button-el' : 'none';

		$id = simpay_dashify( $id );

		$html .= '<div class="simpay-form-control">';
		$html .= '<button id="' . esc_attr( $id ) . '" class="' . self::get_payment_button_classes( $button_style ) . '"><span>' . esc_html( $text ) . '</span></button>';

		// Test mode badge placement
		if ( simpay_is_test_mode() ) {
			$html .= '<div class="simpay-test-mode-badge-container">';
			$html .= '<span class="simpay-test-mode-badge">' . esc_html__( 'Test Mode', 'stripe' ) . '</span>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	// Helper method for adding custom CSS classes to payment button.
	public static function get_payment_button_classes( $button_style ) {
		// Set default class from plugin.
		$classes   = array();
		$classes[] = 'simpay-payment-btn';

		// Also add default CSS class from Stripe unless option set to "none".
		if ( 'none' != $button_style ) {
			$classes[] = 'stripe-button-el';
		}

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'simpay_payment_button_class', $classes );

		return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );
	}
}
