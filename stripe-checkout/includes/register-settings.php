<?php

/**
 * Register all settings needed for the Settings API.
 *
 * @package    SC
 * @subpackage Includes
 * @author     Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *  Main function to register all of the plugin settings
 *
 * @since 1.0.0
 */
function sc_register_settings() {
	$sc_settings = array(

		/* General Settings */
		'general' => array(
			'enable_test_key' => array(
				'id'   => 'enable_test_key',
				'name' => __( 'Enable Test Mode', 'sc' ),
				'desc' => __( 'Place Stripe in Test mode using your test API keys.', 'sc' ),
				'type' => 'checkbox'
			),
			'test_secret_key' => array(
				'id'   => 'test_secret_key',
				'name' => __( 'Test Secret Key', 'sc' ),
				'desc' => __( 'Enter your test secret key, found in your Stripe account settings.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'test_publish_key' => array(
				'id'   => 'test_publish_key',
				'name' => __( 'Test Publishable Key', 'sc' ),
				'desc' => __( 'Enter your test publishable key, found in your Stripe account settings.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'live_secret_key' => array(
				'id'   => 'live_secret_key',
				'name' => __( 'Live Secret Key', 'sc' ),
				'desc' => __( 'Enter your live secret key, found in your Stripe account settings.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'live_publish_key' => array(
				'id'   => 'live_publish_key',
				'name' => __( 'Live Publishable Key', 'sc' ),
				'desc' => __( 'Enter your live publishable key, found in your Stripe account settings.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			)
		),
		'default' => array(
			'name' => array(
				'id'   => 'name',
				'name' => __( 'Name', 'sc' ),
				'desc' => __( 'The name of your company or website.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'description' => array(
				'id'   => 'description',
				'name' => __( 'Description', 'sc' ),
				'desc' => __( 'A description of the product or service being offered.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'amount' => array(
				'id'   => 'amount',
				'name' => __( 'Amount', 'sc' ),
				'desc' => __( 'The amount to charge.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'currency' => array(
				'id'   => 'currency',
				'name' => __( 'Currency', 'sc' ),
				'desc' => __( 'Specify a specific currency by using it\'s 3-letter ISO Code', 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'image_url' => array(
				'id'   => 'image_url',
				'name' => __( 'Image URL', 'sc' ),
				'desc' => __( 'A URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'checkout_button_label' => array(
				'id'   => 'checkout_button_label',
				'name' => __( 'Checkout Button Label', 'sc' ),
				'desc' => __( 'The label of the payment button in the checkout form. You can use {{amount}} to display the amount.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'payment_button_label' => array(
				'id'   => 'payment_button_label',
				'name' => __( 'Payment Button Label', 'sc' ),
				'desc' => __( 'Text to display on the default blue button that users click to initiate a checkout process.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'success_redirect_url' => array(
				'id'   => 'success_redirect_url',
				'name' => __( 'Redirect URL', 'sc' ),
				'desc' => __( 'The URL that the user should be redirected to after a successful payment.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'billing' => array(
				'id'   => 'billing',
				'name' => __( 'Enable Billing', 'sc' ),
				'desc' => __( 'Used to gather the billing information during the checkout process.', 'sc' ),
				'type' => 'checkbox'
			),
			'shipping' => array(
				'id'   => 'shipping',
				'name' => __( 'Enable Shipping', 'sc' ),
				'desc' => __( 'Used to gather the shipping information during the checkout process.', 'sc' ),
				'type' => 'checkbox'
			),
			'enable_remember' => array(
				'id'   => 'enable_remember',
				'name' => __( 'Enable "Remember Me"', 'sc' ),
				'desc' => __( 'Adds a "Remember Me" checkbox to the checkout form.', 'sc' ),
				'type' => 'checkbox'
			)
		)
	);

	/* If the options do not exist then create them for each section */
	if ( false == get_option( 'sc_settings_general' ) ) {
		add_option( 'sc_settings_general' );
	}
	
	if( false == get_option( 'sc_settings_default') ) {
		add_option( 'sc_settings_default' );
	}

	/* Add the General Settings section */
	add_settings_section(
		'sc_settings_general',
		__( 'Keys', 'sc' ),
		'__return_false',
		'sc_settings_general'
	);

	foreach ( $sc_settings['general'] as $option ) {
		add_settings_field(
			'sc_settings_general[' . $option['id'] . ']',
			$option['name'],
			function_exists( 'sc_' . $option['type'] . '_callback' ) ? 'sc_' . $option['type'] . '_callback' : 'sc_missing_callback',
			'sc_settings_general',
			'sc_settings_general',
			sc_get_settings_field_args( $option, 'general' )
		);
	}
	
	/* Add the Default Settings section */
	add_settings_section(
		'sc_settings_default',
		__( 'Default Settings', 'sc' ),
		'__return_false',
		'sc_settings_default'
	);

	foreach ( $sc_settings['default'] as $option ) {
		add_settings_field(
			'sc_settings_default[' . $option['id'] . ']',
			$option['name'],
			function_exists( 'sc_' . $option['type'] . '_callback' ) ? 'sc_' . $option['type'] . '_callback' : 'sc_missing_callback',
			'sc_settings_default',
			'sc_settings_default',
			sc_get_settings_field_args( $option, 'default' )
		);
	}

	/* Register all settings or we will get an error when trying to save */
	register_setting( 'sc_settings_general', 'sc_settings_general', 'sc_settings_sanitize' );
	register_setting( 'sc_settings_default', 'sc_settings_default', 'sc_settings_sanitize' );

}
add_action( 'admin_init', 'sc_register_settings' );

/*
 * Return generic add_settings_field $args parameter array.
 *
 * @since     1.0.0
 *
 * @param   string  $option   Single settings option key.
 * @param   string  $section  Section of settings apge.
 * @return  array             $args parameter to use with add_settings_field call.
 */
function sc_get_settings_field_args( $option, $section ) {
	$settings_args = array(
		'id'      => $option['id'],
		'desc'    => $option['desc'],
		'name'    => $option['name'],
		'section' => $section,
		'size'    => isset( $option['size'] ) ? $option['size'] : null,
		'options' => isset( $option['options'] ) ? $option['options'] : '',
		'std'     => isset( $option['std'] ) ? $option['std'] : ''
	);

	// Link label to input using 'label_for' argument if text, textarea, password, select, or variations of.
	// Just add to existing settings args array if needed.
	if ( in_array( $option['type'], array( 'text', 'select', 'textarea', 'password', 'number' ) ) ) {
		$settings_args = array_merge( $settings_args, array( 'label_for' => 'sc_settings_' . $section . '[' . $option['id'] . ']' ) );
	}

	return $settings_args;
}


/**
 * Textbox callback function
 * Valid built-in size CSS class values:
 * small-text, regular-text, large-text
 * 
 * @since 1.0.0
 * 
 */
function sc_text_callback( $args ) {
	global $sc_options;

	if ( isset( $sc_options[ $args['id'] ] ) )
		$value = $sc_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : '';
	$html = "\n" . '<input type="text" class="' . $size . '" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . trim( esc_attr( $value ) ) . '"/>' . "\n";

	// Render and style description text underneath if it exists.
	if ( ! empty( $args['desc'] ) )
		$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";

	echo $html;
}

/*
 * Single checkbox callback function
 * 
 * @since 1.0.0
 * 
 */
function sc_checkbox_callback( $args ) {
	global $sc_options;

	$checked = isset( $sc_options[$args['id']] ) ? checked( 1, $sc_options[$args['id']], false ) : '';
	$html = "\n" . '<input type="checkbox" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>' . "\n";

	// Render description text directly to the right in a label if it exists.
	if ( ! empty( $args['desc'] ) )
		$html .= '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>' . "\n";

	echo $html;
}

/*
 * Function we can use to sanitize the input data and return it when saving options
 * 
 * @since 1.0.0
 * 
 */
function sc_settings_sanitize( $input ) {
	//add_settings_error( 'sc-notices', '', '', '' );
	return $input;
}

/*
 *  Default callback function if correct one does not exist
 * 
 * @since 1.0.0
 * 
 */
function sc_missing_callback( $args ) {
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'sc' ), $args['id'] );
}

/*
 * Function used to return an array of all of the plugin settings
 * 
 * @since 1.0.0
 * 
 */
function sc_get_settings() {

	$general_settings = is_array( get_option( 'sc_settings_general' ) ) ? get_option( 'sc_settings_general' ) : array();
	$default_settings = is_array( get_option( 'sc_settings_default' ) ) ? get_option( 'sc_settings_default' ) : array();

	return $general_settings;
}
