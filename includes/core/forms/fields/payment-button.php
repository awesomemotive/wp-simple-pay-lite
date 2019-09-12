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
		global $simpay_form;

		$html = '';

		$id    = isset( $settings['id'] ) ? $settings['id'] : '';
		$text  = isset( $settings['text'] ) && ! empty( $settings['text'] ) ? $settings['text'] : esc_html__( 'Pay with Card', 'stripe' );
		$style = isset( $settings['style'] ) ? $settings['style'] : ( simpay_get_global_setting( 'payment_button_style' ) ? simpay_get_global_setting( 'payment_button_style' ) : 'stripe' );

		$id = simpay_dashify( $id );

		$html .= '<div class="simpay-form-control">';
		$html .= '<button id="' . esc_attr( $id ) . '" class="' . self::get_payment_button_classes( $style ) . '"><span>' . esc_html( $text ) . '</span></button>';

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
