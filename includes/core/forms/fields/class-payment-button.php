<?php
/**
 * Form field: Payment Button
 *
 * @package SimplePay\Core\Forms\Fields
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment_Button class.
 *
 * @since 3.0.0
 */
class Payment_Button extends Custom_Field {

	/**
	 * Print the HTML for the payment button on the frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		global $simpay_form;

		$html = '';

		$id    = isset( $settings['id'] ) ? $settings['id'] : '';
		$text  = isset( $settings['text'] ) && ! empty( $settings['text'] ) ? $settings['text'] : esc_html__( 'Pay with Card', 'stripe' );
		$style = isset( $settings['style'] )
			? $settings['style']
			: 'stripe';

		$id = simpay_dashify( $id );

		$html .= '<div class="simpay-form-control">';
		$html .= '<button id="' . esc_attr( $id ) . '" class="' . self::get_payment_button_classes( $style ) . '"><span>' . esc_html( $text ) . '</span></button>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Generates a class name for the Payment Button.
	 *
	 * @since 3.0.0
	 *
	 * @param string $button_style Button style.
	 * @return string
	 */
	public static function get_payment_button_classes( $button_style ) {
		$classes = array(
			'simpay-btn',
			'simpay-payment-btn',
		);

		// Also add default CSS class from Stripe unless option set to "none".
		if ( 'none' != $button_style ) {
			$classes[] = 'stripe-button-el';
		}

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'simpay_payment_button_class', $classes );

		return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );
	}
}
