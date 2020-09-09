<?php
/**
 * reCAPTCHA
 *
 * @package SimplePay\Core\reCAPTCHA
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
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
	$settings = get_option( 'simpay_settings_general' );
	$key      = isset( $settings['recaptcha'][ $key ] ) ? $settings['recaptcha'][ $key ] : false;

	if ( ! $key || '' === $key ) {
		return false;
	}

	return $key;
}

/**
 * Generate custom HTML to output under reCAPTCHA title.
 *
 * @since 3.9.6
 */
function admin_setting_description() {
	ob_start();
	?>

	<p><?php esc_html_e( 'reCAPTCHA can help automatically protect your custom payment forms from spam and fraud.', 'stripe' ); ?></p>

	<br />

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'To enable reCAPTCHA %1$sregister your site with Google%2$s with reCAPTCHA v3 to retrieve the necessary credentials.', 'stripe' ),
			'<a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer">',
			'</a>'
		)
	);
	?>
	</p>

	<br />

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'For more information view our %1$shelp docs for reCAPTCHA%2$s.', 'stripe' ),
			'<a href="' . simpay_docs_link( '', 'recaptcha', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		)
	);
	?>
	</p>

	<?php
	return ob_get_clean();
}

/**
 * Add "reCaptcha" section to General tab.
 *
 * @since 3.9.6
 *
 * @param array $sections Settings sections.
 * @return array
 */
function add_recaptcha_settings_section( $sections ) {
	$settings = array(
		'title' => __( 'reCAPTCHA', 'stripe' ),
	);

	return simpay_add_to_array_after( 'recaptcha', $settings, 'styles', $sections );
}
add_filter( 'simpay_add_settings_general_sections', __NAMESPACE__ . '\\add_recaptcha_settings_section' );

/**
 * Add "reCAPTCHA" fields to General tab.
 *
 * @since 3.9.6
 *
 * @param array $fields Settings fields.
 * @return array
 */
function add_recaptcha_settings_fields( $fields ) {
	$id      = 'settings';
	$group   = 'general';
	$section = 'recaptcha';

	$input_args = array(
		'type'    => 'standard',
		'subtype' => 'password',
		'default' => '',
		'class'   => array(
			'regular-text',
		),
	);

	$fields[ $section ]['setup'] = array(
		'title' => esc_html__( 'Setup', 'stripe' ),
		'type'  => 'custom-html',
		'html'  => admin_setting_description(),
		'name'  => 'simpay_' . $id . '_' . $group . '[' . $section . '][setup]',
		'id'    => 'simpay-' . $id . '-' . $group . '-' . $section . '-setup',
	);

	$fields[ $section ]['site'] = wp_parse_args(
		array(
			'subtype' => 'text',
			'title'   => esc_html__( 'Site Key', 'stripe' ),
			'name'    => 'simpay_' . $id . '_' . $group . '[recaptcha][site]',
			'id'      => 'simpay-' . $id . '-' . $group . '-recaptcha-site',
			'value'   => get_key( 'site' ),
		),
		$input_args
	);

	$fields[ $section ]['secret'] = wp_parse_args(
		array(
			'title' => esc_html__( 'Secret Key', 'stripe' ),
			'name'  => 'simpay_' . $id . '_' . $group . '[recaptcha][secret]',
			'id'    => 'simpay-' . $id . '-' . $group . '-recaptcha-secret',
			'value' => get_key( 'secret' ),
		),
		$input_args
	);

	$settings = get_option( 'simpay_settings_general' );

	$fields[ $section ]['threshold'] = array(
		'title'   => esc_html__( 'Score Threshold', 'stripe' ),
		'type'    => 'select',
		'default' => 'default',
		'options' => array(
			'default'    => esc_html__( 'Default', 'stripe' ),
			'aggressive' => esc_html__( 'Aggressive', 'stripe' ),
		),
		'name'  => 'simpay_' . $id . '_' . $group . '[recaptcha][threshold]',
		'id'    => 'simpay-' . $id . '-' . $group . '-recaptcha-threshold',
		'value' => isset( $settings['recaptcha']['threshold'] )
			? $settings['recaptcha']['threshold']
			: 'default'
	);

	return $fields;
}
add_filter( 'simpay_add_settings_general_fields', __NAMESPACE__ . '\\add_recaptcha_settings_fields' );

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

	wp_enqueue_script( 'google-recaptcha', esc_url( $url ), array(), 'v3', true );

	wp_localize_script(
		'google-recaptcha',
		'simpayGoogleRecaptcha',
		array(
			'siteKey' => get_key( 'site' ),
			'i18n'    => array(
				'invalid' => esc_html__( 'Unable to verify Google reCAPTCHA response.', 'stripe' ),
			),
		)
	);

	wp_enqueue_script(
		'simpay-recaptcha',
		SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-public-recaptcha.min.js',
		array(
			'wp-util',
			'google-recaptcha',
			'simpay-public',
		),
		SIMPLE_PAY_VERSION,
		true
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

	$settings  = get_option( 'simpay_settings_general' );
	$threshold = isset( $settings['recaptcha']['threshold'] )
		? $settings['recaptcha']['threshold']
		: 'default';

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
 *
 * @param array                         $object_args Arguments used to create a Customer or Session.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @param int|string                    $customer_id Stripe Customer ID, or a blank string if none is needed.
 */
function validate_recaptcha_source() {
	// No keys are entered.
	if ( ! has_keys() ) {
		return wp_send_json_success();
	}

	$token  = isset( $_POST['token'] )
		? sanitize_text_field( $_POST['token'] )
		: false;

	$recaptcha_action = isset( $_POST['recaptcha_action'] )
		? sanitize_text_field( $_POST['recaptcha_action'] )
		: false;

	// A token couldn't be generated, let it through.
	if ( false === $token || false === $recaptcha_action ) {
		return wp_send_json_error();
	}

	if ( true !== validate_recaptcha( $token, $recaptcha_action ) ) {
		return wp_send_json_error();
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
 */
function validate_recaptcha_customer( $customer_args, $form, $form_data, $form_values ) {
	// Do nothing if no keys set.
	if ( ! has_keys() ) {
		return;
	}

	// Ensure a token exists.
	if ( ! isset( $form_values['grecaptcha_customer'] ) ) {
		throw new \Exception( __( 'Invalid reCAPTCHA. Please try again.', 'stripe' ) );
	}

	if ( is_array( $form_values['grecaptcha_customer'] ) ) {
		$form_values['grecaptcha_customer'] = end( $form_values['grecaptcha_customer'] );
	}

	// Validate token.
	$valid = validate_recaptcha(
		$form_values['grecaptcha_customer'],
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
 */
function validate_recaptcha_payment( $paymentintent_args, $form, $form_data, $form_values ) {
	// Do nothing if no keys set.
	if ( ! has_keys() ) {
		return;
	}

	// Ensure a token exists.
	if ( ! isset( $form_values['grecaptcha_payment'] ) ) {
		throw new \Exception( __( 'Invalid reCAPTCHA. Please try again.', 'stripe' ) );
	}

	if ( is_array( $form_values['grecaptcha_payment'] ) ) {
		$form_values['grecaptcha_payment'] = end( $form_values['grecaptcha_payment'] );
	}

	// Validate token.
	$valid = validate_recaptcha(
		$form_values['grecaptcha_payment'],
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
