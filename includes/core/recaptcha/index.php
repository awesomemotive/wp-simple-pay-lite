<?php
/**
 * reCAPTCHA
 *
 * @package SimplePay\Core\reCAPTCHA
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.6
 */

namespace SimplePay\Core\reCAPTCHA;

use SimplePay\Core\Settings;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\NotificationInbox\NotificationRuleProcessor;
use SimplePay\Core\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine if keys are entered.
 *
 * @since 3.9.6
 *
 * @return bool
 */
function has_keys() {
	return get_key( 'site' ) && get_key( 'secret' );
}

/**
 * Retrieve a site key.
 *
 * @since 3.9.6
 *
 * @param string $key Type of key to retrieve. `site` or `secret`.
 * @return bool|string
 */
function get_key( $key ) {
	$key = simpay_get_setting(
		sprintf(
			'recaptcha_%s_key',
			$key
		),
		''
	);

	if ( empty( $key ) ) {
		return false;
	}

	return $key;
}

/**
 * Enqueue scripts necessary for generating a reCAPTCHA token.
 *
 * @since 3.9.6
 *
 * @param int    $form_id Current Form ID.
 * @param object $form Current form.
 */
function add_script( $form_id, $form ) {
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	switch ( $type ) {
		case 'hcaptcha':
			wp_enqueue_script(
				'simpay-hcaptcha',
				'https://js.hcaptcha.com/1/api.js'
			);
			break;
		case 'recaptcha-v3':
			$url = add_query_arg(
				array(
					'render' => get_key( 'site' ),
				),
				'https://www.google.com/recaptcha/api.js'
			);

			wp_enqueue_script( 'simpay-google-recaptcha-v3', esc_url( $url ), array(), 'v3', true );

			wp_localize_script(
				'simpay-public',
				'simpayGoogleRecaptcha',
				array(
					'siteKey' => get_key( 'site' ),
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
			wp_enqueue_script(
				'simpay-cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js'
			);
			break;

	}
}
add_action( 'simpay_form_before_form_bottom', __NAMESPACE__ . '\\add_script', 10, 2 );

/**
 * Validate a hCaptcha token.
 *
 * @since 4.6.6
 *
 * @param string $token reCAPTCHA token.
 * @return void
 */
function validate_hcaptcha( $token ) {
	// Only validate reCAPTCHA v3.
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	if ( 'hcaptcha' !== $type ) {
		return;
	}

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
 * Validate a reCAPTCHA token.
 *
 * @since 3.9.6
 *
 * @param string $token reCAPTCHA token.
 * @param string $action reCAPTCHA action.
 */
function validate_recaptcha( $token, $action ) {
	// Only validate reCAPTCHA v3.
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	if ( 'recaptcha-v3' !== $type ) {
		return;
	}

	$secret = get_key( 'secret' );

	$request = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'body' => array(
				'secret'   => $secret,
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
	if ( isset( $response['action'] ) && $action !== $response['action'] ) {
		return false;
	}

	$threshold = simpay_get_setting( 'recaptcha_score_threshold', 'aggressive' );

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

	if ( floatval( $response['score'] ) < floatval( $minimum_score ) ) {
		return false;
	}

	return true;
}

/**
 * Validate reCAPTCHA on page load.
 *
 * @since 3.5.0
 */
function validate_recaptcha_source() {
	// Only validate reCAPTCHA v3.
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	if ( 'recaptcha-v3' !== $type ) {
		return;
	}

	$token = isset( $_POST['token'] )
		? sanitize_text_field( $_POST['token'] )
		: false;

	$recaptcha_action = isset( $_POST['recaptcha_action'] )
		? sanitize_text_field( $_POST['recaptcha_action'] )
		: false;

	$data = array(
		'message' => esc_html__(
			'Unable to validate reCAPTCHA token. Please verify your Site and Secret keys.',
			'stripe'
		),
	);

	// A token couldn't be generated, let it through.
	if ( false === $token || false === $recaptcha_action ) {
		return wp_send_json_error( $data );
	}

	if ( true !== validate_recaptcha( $token, $recaptcha_action ) ) {
		return wp_send_json_error( $data );
	}

	return wp_send_json_success();
}
add_action( 'wp_ajax_simpay_validate_recaptcha_source', __NAMESPACE__ . '\\validate_recaptcha_source' );

/**
 * Validates reCAPTCHA before Customer creation.
 *
 * @since 3.9.6
 *
 * @param array                         $customer_args Arguments used to create a PaymentIntent.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @throws \Exception If reCAPTCHA cannot be validated.
 */
function validate_recaptcha_customer( $customer_args, $form, $form_data, $form_values ) {
	// Only validate reCAPTCHA v3.
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	if ( 'recaptcha-v3' !== $type ) {
		return;
	}

	// Ensure a token exists.
	if ( ! isset( $form_data['customerCaptchaToken'] ) ) {
		throw new \Exception( __( 'Invalid reCAPTCHA. Please try again.', 'stripe' ) );
	}

	// Validate token.
	$valid = validate_recaptcha(
		$form_data['customerCaptchaToken'],
		sprintf(
			'simple_pay_form_%s_%s',
			$form->id,
			'customer'
		)
	);

	if ( false === $valid ) {
		throw new \Exception( __( 'Invalid reCAPTCHA. Please try again.', 'stripe' ) );
	}
}
// Only validate reCAPTCHA here if UPE is not enabled. Otherwise it is handled
// in the updated `wpsp/__internal__payment` endpoint.
if ( ! simpay_is_upe() ) {
	add_action(
		'simpay_before_customer_from_payment_form_request',
		__NAMESPACE__ . '\\validate_recaptcha_customer',
		10,
		4
	);
}

/**
 * Validates reCAPTCHA before PaymentIntent creation.
 *
 * @since 3.9.6
 *
 * @param array                         $paymentintent_args Arguments used to create a PaymentIntent.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @throws \Exception If reCAPTCHA cannot be validated.
 */
function validate_recaptcha_payment( $paymentintent_args, $form, $form_data, $form_values ) {
	// Only validate reCAPTCHA v3.
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	if ( 'recaptcha-v3' !== $type ) {
		return;
	}

	// Ensure a token exists.
	if ( ! isset( $form_data['paymentCaptchaToken'] ) ) {
		throw new \Exception( __( 'Invalid reCAPTCHA. Please try again.', 'stripe' ) );
	}

	// Validate token.
	$valid = validate_recaptcha(
		$form_data['paymentCaptchaToken'],
		sprintf(
			'simple_pay_form_%s_%s',
			$form->id,
			'payment'
		)
	);

	if ( false === $valid ) {
		throw new \Exception( __( 'Invalid reCAPTCHA. Please try again.', 'stripe' ) );
	}
}
// Only validate reCAPTCHA here if UPE is not enabled. Otherwise it is handled
// in the updated `wpsp/__internal__payment` endpoint.
if ( ! simpay_is_upe() ) {
	add_action(
		'simpay_before_paymentintent_from_payment_form_request',
		__NAMESPACE__ . '\\validate_recaptcha_payment',
		10,
		4
	);
	add_action(
		'simpay_before_subscription_from_payment_form_request',
		__NAMESPACE__ . '\\validate_recaptcha_payment',
		10,
		4
	);
	add_action(
		'simpay_before_charge_from_payment_form_request',
		__NAMESPACE__ . '\\validate_recaptcha_payment',
		10,
		4
	);
	add_action(
		'simpay_before_checkout_session_from_payment_form_request',
		__NAMESPACE__ . '\\validate_recaptcha_payment',
		10,
		4
	);
}

/**
 * Validates hCaptcha before payment action.
 *
 * @since 4.6.6
 *
 * @param array                         $paymentintent_args Arguments used to create a PaymentIntent.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @throws \Exception If reCAPTCHA cannot be validated.
 */
function validate_hcaptcha_payment( $paymentintent_args, $form, $form_data, $form_values ) {
	// Only validate reCAPTCHA v3.
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	if ( 'hcaptcha' !== $type ) {
		return;
	}

	// Ensure a token exists.
	if ( ! isset( $form_values['h-captcha-response'] ) ) {
		throw new \Exception(
			__(
				'Invalid CAPTCHA. Please reload the page and try again.',
				'stripe'
			)
		);
	}

	// Validate token.
	$valid = validate_hcaptcha(
		sanitize_text_field( $form_values['h-captcha-response'] )
	);

	if ( false === $valid ) {
		throw new \Exception(
			__(
				'Invalid CAPTCHA. Please reload the page and try again.',
				'stripe'
			)
		);
	}
}
// Only validate hCaptcha here if UPE is not enabled. Otherwise it is handled
// in the updated `wpsp/__internal__payment` endpoint.
if ( ! simpay_is_upe() ) {
	add_action(
		'simpay_before_paymentintent_from_payment_form_request',
		__NAMESPACE__ . '\\validate_hcaptcha_payment',
		10,
		4
	);
	add_action(
		'simpay_before_subscription_from_payment_form_request',
		__NAMESPACE__ . '\\validate_hcaptcha_payment',
		10,
		4
	);
	add_action(
		'simpay_before_checkout_session_from_payment_form_request',
		__NAMESPACE__ . '\\validate_hcaptcha_payment',
		10,
		4
	);
}

/**
 * Adds an Inbox notification if no CAPTCHA type has been set.
 *
 * @since 4.6.6
 *
 * @return void
 */
function maybe_add_inbox_notification() {
	if ( ! simpay_is_livemode() ) {
		return;
	}

	// Notification Inbox is only available in WP 5.7+.
	global $wp_version;

	if ( version_compare( $wp_version, '5.7', '<=' ) ) {
		return;
	}

	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	// A type has been set, do nothing.
	if ( ! empty( $type ) ) {
		return;
	}

	// No forms exist yet, do nothing.
	$forms = array_sum( (array) wp_count_posts( 'simple-pay' ) );

	if ( 0 === $forms ) {
		return;
	}

	$notifications = new NotificationRepository(
		new NotificationRuleProcessor
	);

	$notifications->restore(
		array(
			'type'           => 'error',
			'source'         => 'internal',
			'title'          => __(
				'🚨 Missing Payment Form Fraud Protection',
				'stripe'
			),
			'slug'           => 'no-captcha',
			'content'        => esc_html__(
				'Configure a CAPTCHA solution to protect your payment forms and prevent spam, abuse, and fraudulent payments.',
				'stripe'
			),
			'actions'        => array(
				array(
					'type' => 'primary',
					'text' => __( 'Configure CAPTCHA', 'stripe' ),
					'url'  => Settings\get_url(
						array(
							'section'    => 'general',
							'subsection' => 'recaptcha',
						)
					),
				),
				array(
					'type' => 'secondary',
					'text' => __( 'Learn More', 'stripe' ),
					'url'  => simpay_docs_link(
						'Learn More',
						'recaptcha',
						'notification-inbox',
						true
					),
				),
			),
			'conditions'     => array(),
			'start'          => gmdate( 'Y-m-d H:i:s', time() ),
			'end'            => gmdate( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
			'is_dismissible' => false,
		)
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\maybe_add_inbox_notification' );

/**
 * Dismisses the CAPTCHA Inbox notification if a type is set.
 *
 * @since 4.6.6
 *
 * @param array<string, mixed> $settings Settings.
 * @return array<string, mixed>
 */
function maybe_dismiss_inbox_notification( $settings ) {
	// Notification Inbox is only available in WP 5.7+.
	global $wp_version;

	if ( version_compare( $wp_version, '5.7', '<=' ) ) {
		return $settings;
	}

	if ( ! isset( $settings['captcha_type'] ) ) {
		return $settings;
	}

	$notifications = new NotificationRepository(
		new NotificationRuleProcessor
	);

	$notifications->dismiss( 'no-captcha' );

	return $settings;
}
add_filter(
	'simpay_update_settings',
	__NAMESPACE__ . '\\maybe_dismiss_inbox_notification',
	10,
	2
);
