<?php
/**
 * Utils: Token validation
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

use SimplePay\Core\Utils;

/**
 * TokenValidationTrait trait.
 *
 * Helpers for managing payment form tokens. At this time tokens are limited to
 * external CAPTCHA services, but in the future we could add further built in options.
 *
 * In that case, this might make more sense as service that gets passed to the endpoints.
 *
 * @since 4.7.0
 */
trait TokenValidationUtils {

	/**
	 * Validates a payment form's CAPTCHA token.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool True if the parameter is valid, false otherwise.
	 */
	public static function validate_token( $request ) {
		$token              = $request->get_param( 'token' );
		$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
		$default            = ! empty( $existing_recaptcha )
			? 'recaptcha-v3'
			: '';
		$type               = simpay_get_setting( 'captcha_type', $default );

		switch ( $type ) {
			case 'recaptcha-v3':
				return self::validate_recaptcha_v3_token( $token );
			case 'hcaptcha':
				return self::validate_hcaptcha_token( $token );
			case 'cloudflare-turnstile':
				return self::validate_cloudflare_turnstile_token( $token, $request );
			default:
				return true;
		}
	}

	/**
	 * Validates a Google reCAPTCHA v3 token.
	 *
	 * @since 4.7.0
	 *
	 * @param string $token The CAPTCHA token.
	 * @return bool
	 */
	private static function validate_recaptcha_v3_token( $token ) {
		$request = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret'   => simpay_get_setting( 'recaptcha_secret_key' ),
					'response' => $token,
					'remoteip' => Utils\get_current_ip_address(),
				),
			)
		);

		// Request fails.
		if ( is_wp_error( $request ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ), true );

		// No score available.
		if ( ! isset( $response['score'] ) ) {
			return false;
		}

		// Actions do not match.
		if (
			isset( $response['action'] ) &&
			'simpay_payment' !== $response['action']
		) {
			return false;
		}

		$threshold = simpay_get_setting(
			'recaptcha_score_threshold',
			'aggressive'
		);

		switch ( $threshold ) {
			case 'aggressive':
				$minimum_score = '0.80';
				break;
			default:
				$minimum_score = '0.50';
		}

		/**
		 * Filter the minimum score allowed for a reCAPTCHA response to allow form submission.
		 *
		 * @since 3.9.6
		 *
		 * @param string $minimum_score Minumum score.
		 */
		$minimum_score = apply_filters( 'simpay_recpatcha_minimum_score', $minimum_score );

		return floatval( $response['score'] ) >= floatval( $minimum_score );
	}

	/**
	 * Validates an hCaptcha token.
	 *
	 * @since 4.7.0
	 *
	 * @param string $token The CAPTCHA token.
	 * @return bool
	 */
	private static function validate_hcaptcha_token( $token ) {
		$request = wp_remote_post(
			'https://hcaptcha.com/siteverify',
			array(
				'body' => array(
					'secret'   => simpay_get_setting( 'hcaptcha_secret_key', '' ),
					'response' => $token,
					'sitekey'  => simpay_get_setting( 'hcaptcha_site_key', '' ),
					'remoteip' => Utils\get_current_ip_address(),
				),
			)
		);

		// Request fails.
		if ( is_wp_error( $request ) ) {
			return false;
		}

		$response = wp_remote_retrieve_body( $request );

		if ( empty( $response ) ) {
			return false;
		}

		$response = json_decode( $response );

		if ( null === $response ) {
			return false;
		}

		return $response->success;
	}

	/**
	 * Validates a Cloudflare Turnstile token.
	 *
	 * @since 4.7.0
	 *
	 * @param string           $token The CAPTCHA token.
	 * @param \WP_REST_Request $payment_request The payment request.
	 * @return bool
	 */
	private static function validate_cloudflare_turnstile_token( $token, $payment_request ) {
		$request = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'body' => array(
					'secret'   => simpay_get_setting(
						'cloudflare_turnstile_secret_key',
						''
					),
					'response' => $token,
					'remoteip' => Utils\get_current_ip_address(),
				),
			)
		);

		// Request fails.
		if ( is_wp_error( $request ) ) {
			return false;
		}

		$response = wp_remote_retrieve_body( $request );

		if ( empty( $response ) ) {
			return false;
		}

		$response = json_decode( $response );

		if ( null === $response ) {
			return false;
		}

		$form   = PaymentRequestUtils::get_form( $payment_request );
		$action = sprintf( 'simpay-form-%d', $form->id );

		if ( $response->action !== $action ) {
			return false;
		}

		return $response->success;
	}
}
