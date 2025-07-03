<?php
/**
 * Form field: Payment Button
 *
 * @package SimplePay\Core\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
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
		$html = '';

		$id = isset( $settings['id'] )
			? simpay_dashify( $settings['id'] )
			: '';

		$style = isset( $settings['style'] )
			? $settings['style']
			: 'stripe';

		// Find button loading text, and replace amount tag if specified.
		$text = (
			isset( $settings['text'] ) &&
			! empty( $settings['text'] )
		)
			? $settings['text']
			: esc_html__( 'Pay with Card', 'stripe' );

		$prices = simpay_get_payment_form_prices( self::$form );
		$price  = simpay_get_payment_form_default_price( $prices );

		$formatted_amount = simpay_format_currency(
			$price->unit_amount,
			$price->currency
		);

		$text = str_replace(
			'{{amount}}',
			'<em class="simpay-total-amount-value">' . $formatted_amount . '</em>',
			esc_html( $text )
		);

		$captcha_type = simpay_get_setting( 'captcha_type', '' );

		if ( 'stripe_checkout' === self::$form->get_display_type() ) {
			switch ( $captcha_type ) {
				case 'hcaptcha':
					$html .= sprintf(
						'<div class="simpay-form-control h-captcha" data-sitekey="%s"></div>',
						esc_attr( simpay_get_setting( 'hcaptcha_site_key', '' ) )
					);
					break;
				case 'cloudflare-turnstile':
					$html .= sprintf(
						'<div class="simpay-form-control cf-turnstile" data-sitekey="%s" data-action="simpay-form-%d"></div>',
						esc_attr( simpay_get_setting( 'cloudflare_turnstile_site_key', '' ) ),
						self::$form->id
					);
					break;
			}
		}

		$html .= '<div class="simpay-form-control">';

		$html .= sprintf(
			'<button id="%1$s" class="%2$s"><span>%3$s</span></button>',
			esc_attr( $id ),
			self::get_payment_button_classes( $style ),
			$text
		);

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
