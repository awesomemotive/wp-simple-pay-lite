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
	// No keys are entered.
	if ( ! has_keys() ) {
		return;
	}

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
}
add_action( 'simpay_form_before_form_bottom', __NAMESPACE__ . '\\add_script', 10, 2 );

/**
 * Validate a reCAPTCHA token.
 *
 * @since 3.9.6
 *
 * @param string $token reCAPTCHA token.
 * @param string $action reCAPTCHA action.
 */
function validate_recaptcha( $token, $action ) {
	// No keys are entered.
	if ( ! has_keys() ) {
		return true;
	}

	$secret = get_key( 'secret' );

	$request = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'body' => array(
				'secret'   => $secret,
				'response' => $token,
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

	$threshold = simpay_get_setting( 'recaptcha_score_threshold', 'default' );

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
	// No keys are entered.
	if ( ! has_keys() ) {
		return wp_send_json_success();
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
add_action( 'wp_ajax_nopriv_simpay_validate_recaptcha_source', __NAMESPACE__ . '\\validate_recaptcha_source' );
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
	// Do nothing if no keys set.
	if ( ! has_keys() ) {
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
add_action(
	'simpay_before_customer_from_payment_form_request',
	__NAMESPACE__ . '\\validate_recaptcha_customer',
	10,
	4
);

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
	// Do nothing if no keys set.
	if ( ! has_keys() ) {
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
