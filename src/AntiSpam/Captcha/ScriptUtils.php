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
	 * Default Cloudflare Turnstile widget theme.
	 *
	 * @since 4.17.6
	 * @var string
	 */
	const TURNSTILE_DEFAULT_THEME = 'auto';

	/**
	 * Default Cloudflare Turnstile widget size.
	 *
	 * @since 4.17.6
	 * @var string
	 */
	const TURNSTILE_DEFAULT_SIZE = 'normal';

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
				wp_enqueue_script( 'simpay-hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion

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

				// Print the site key and error message with the reCAPTCHA
				// script itself. `simpay-public` is only enqueued when a
				// payment form renders, so config attached to it is missing on
				// a page with only the Manage Subscriptions form (#3579).
				// Hex-encode `<`, `>` and `&` so the site key can't open or
				// close a tag inside the inline script. Older WordPress
				// versions don't escape inline script contents themselves.
				$config = 'window.simpayGoogleRecaptcha = window.simpayGoogleRecaptcha || ' . wp_json_encode( self::get_recaptcha_v3_config(), JSON_HEX_TAG | JSON_HEX_AMP ) . ';';
				$before = wp_scripts()->get_data( 'simpay-google-recaptcha-v3', 'before' );

				if ( ! is_array( $before ) || ! in_array( $config, $before, true ) ) {
					wp_add_inline_script( 'simpay-google-recaptcha-v3', $config, 'before' );
				}
				break;
			case 'cloudflare-turnstile':
				// Enqueue Cloudflare Turnstile script.
				wp_enqueue_script( 'simpay-cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion, PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare requires api.js to be loaded from its CDN.

				break;
		}
	}

	/**
	 * Returns the `simpayGoogleRecaptcha` data the front end reads to request a
	 * reCAPTCHA v3 token.
	 *
	 * @since 4.17.6
	 *
	 * @return array<string, mixed>
	 */
	public static function get_recaptcha_v3_config() {
		return array(
			'siteKey' => self::get_key( 'site' ),
			'i18n'    => array(
				'invalid' => esc_html__(
					'Unable to generate and validate reCAPTCHA token. Please verify your Site and Secret keys.',
					'stripe'
				),
			),
		);
	}

	/**
	 * Render the captcha based on the selected type.
	 *
	 * @param string $action Form action name.
	 * @since 4.8.0
	 * @since 4.17.6 Always returns a string.
	 * @return string Captcha HTML code. Empty when no captcha is configured.
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
					'<div class="simpay-form-control cf-turnstile" data-sitekey="%s" data-action="simpay-form-%s" data-theme="%s" data-size="%s"></div>',
					esc_attr( simpay_get_setting( 'cloudflare_turnstile_site_key', '' ) ), // @phpstan-ignore-line
					esc_attr( $action ),
					esc_attr( self::get_turnstile_theme() ),
					esc_attr( self::get_turnstile_size() )
				);
				break;
		}

		// Return the buffered output.
		$html = ob_get_clean();

		return is_string( $html ) ? $html : '';
	}

	/**
	 * Retrieve the supported Cloudflare Turnstile widget themes.
	 *
	 * Single source of truth for the allowed theme values, used for
	 * validation, the settings select options, and the settings schema.
	 *
	 * @since 4.17.6
	 *
	 * @return array<string, string> Theme labels, keyed by theme value.
	 */
	public static function get_turnstile_theme_options() {
		return array(
			'auto'  => esc_html_x( 'Auto', 'captcha theme option', 'stripe' ),
			'light' => esc_html_x( 'Light', 'captcha theme option', 'stripe' ),
			'dark'  => esc_html_x( 'Dark', 'captcha theme option', 'stripe' ),
		);
	}

	/**
	 * Retrieve the supported Cloudflare Turnstile widget sizes.
	 *
	 * Single source of truth for the allowed size values, used for
	 * validation, the settings select options, and the settings schema.
	 *
	 * @since 4.17.6
	 *
	 * @return array<string, string> Size labels, keyed by size value.
	 */
	public static function get_turnstile_size_options() {
		return array(
			'normal'   => esc_html_x( 'Normal', 'captcha size option', 'stripe' ),
			'flexible' => esc_html_x( 'Flexible', 'captcha size option', 'stripe' ),
			'compact'  => esc_html_x( 'Compact', 'captcha size option', 'stripe' ),
		);
	}

	/**
	 * Retrieve the configured Cloudflare Turnstile widget theme.
	 *
	 * Falls back to `auto` if the stored value is empty or not a
	 * supported theme.
	 *
	 * @since 4.17.6
	 *
	 * @return string Widget theme. One of the keys of `get_turnstile_theme_options()`.
	 */
	public static function get_turnstile_theme() {
		return self::get_validated_setting(
			'cloudflare_turnstile_theme',
			array_keys( self::get_turnstile_theme_options() ),
			self::TURNSTILE_DEFAULT_THEME
		);
	}

	/**
	 * Retrieve the configured Cloudflare Turnstile widget size.
	 *
	 * Falls back to `normal` if the stored value is empty or not a
	 * supported size.
	 *
	 * @since 4.17.6
	 *
	 * @return string Widget size. One of the keys of `get_turnstile_size_options()`.
	 */
	public static function get_turnstile_size() {
		return self::get_validated_setting(
			'cloudflare_turnstile_size',
			array_keys( self::get_turnstile_size_options() ),
			self::TURNSTILE_DEFAULT_SIZE
		);
	}

	/**
	 * Retrieve a setting value restricted to a list of allowed values.
	 *
	 * @since 4.17.6
	 *
	 * @param string        $setting  Setting ID.
	 * @param array<string> $allowed  Allowed values.
	 * @param string        $fallback Value returned when the stored value is invalid.
	 * @return string Validated setting value.
	 */
	private static function get_validated_setting( $setting, $allowed, $fallback ) {
		$value = simpay_get_setting( $setting, $fallback );

		if ( ! is_string( $value ) || ! in_array( $value, $allowed, true ) ) {
			return $fallback;
		}

		return $value;
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
