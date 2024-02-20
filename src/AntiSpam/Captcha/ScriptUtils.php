<?php
/**
 * Anti-Spam: Captcha Script
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.0
 */

namespace SimplePay\Core\AntiSpam\Captcha;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ScriptUtils
 * This class provides utility functions related to script enqueuing and rendering captchas.
 */
class ScriptUtils {

	/**
	 * Enqueue the necessary scripts for captcha based on the selected type.
	 *
	 * @since 4.8.0
	 * @return void
	 */
	public static function enqueue_captcha_scripts() {
		// Retrieve existing reCAPTCHA site key.
		$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );

		// Set default captcha type based on the existence of reCAPTCHA site key.
		$default = ! empty( $existing_recaptcha ) ? 'recaptcha-v3' : '';
		$type    = simpay_get_setting( 'captcha_type', $default );

		switch ( $type ) {
			case 'hcaptcha':
				// Enqueue hCaptcha script.
				wp_enqueue_script(
					'simpay-hcaptcha',
					'https://js.hcaptcha.com/1/api.js'
				);
				break;
			case 'recaptcha-v3':
				// Generate the URL for reCAPTCHA script with the site key.
				$url = add_query_arg(
					array(
						'render' => self::get_key( 'site' ),
					),
					'https://www.google.com/recaptcha/api.js'
				);

				// Enqueue reCAPTCHA v3 script.
				wp_enqueue_script( 'simpay-google-recaptcha-v3', esc_url( $url ), array(), 'v3', true );

				// Localize script with reCAPTCHA site key and custom error message.
				wp_localize_script(
					'simpay-public',
					'simpayGoogleRecaptcha',
					array(
						'siteKey' => self::get_key( 'site' ),
						'i18n'    => array(
							'invalid' => esc_html__(
								'Unable to generate and validate reCAPTCHA token. Please verify your Site and Secret keys.',
								'stripe'
							),
						),
					)
				);
				break;
			case 'cloudflare-turnstile':
				// Enqueue Cloudflare Turnstile script.
				wp_enqueue_script(
					'simpay-cloudflare-turnstile',
					'https://challenges.cloudflare.com/turnstile/v0/api.js'
				);
				break;
		}
	}

	/**
	 * Render the captcha based on the selected type.
	 *
	 * @param string $action Form action name.
	 * @since 4.8.0
	 * @return string|bool Captcha HTML code.
	 */
	public static function render_captcha( $action ) {
		// Retrieve the selected captcha type.
		$captcha_type = simpay_get_setting( 'captcha_type', '' );

		// Start output buffering.
		ob_start();

		// Render captcha based on the selected type.
		switch ( $captcha_type ) {
			case 'hcaptcha':
				printf(
					'<div class="simpay-form-control h-captcha" data-sitekey="%s"></div>',
					esc_attr( simpay_get_setting( 'hcaptcha_site_key', '' ) ) // @phpstan-ignore-line
				);
				break;
			case 'cloudflare-turnstile':
				printf(
					'<div class="simpay-form-control cf-turnstile" data-sitekey="%s" data-action="simpay-form-%s"></div>',
					esc_attr( simpay_get_setting( 'cloudflare_turnstile_site_key', '' ) ), // @phpstan-ignore-line
					esc_attr( $action )
				);
				break;
		}

		// Return the buffered output.
		return ob_get_clean();
	}

	/**
	 * Retrieve a site key.
	 *
	 * @since 3.9.6
	 *
	 * @param string $key Type of key to retrieve. `site` or `secret`.
	 * @return bool|string|mixed Site key if found, otherwise false.
	 */
	private static function get_key( $key ) {
		// Retrieve the specified key from settings.
		$key = simpay_get_setting(
			sprintf(
				'recaptcha_%s_key',
				$key
			),
			''
		);

		// Return the key if not empty, otherwise return false.
		if ( empty( $key ) ) {
			return false;
		}

		return $key;
	}
}
