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
	
	global $sc_options;
	
	$sc_options = array();
	
	$sc_settings = array(

		/* Default Settings */
		'default' => array(
			'note' => array(
				'id'   => 'settings_note',
				'name' => '',
				'desc' => '<a href="' . sc_ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/shortcodes/stripe-checkout/', 'stripe_checkout', 'settings', 'docs' ) . '" target="_blank">' .
				          __( 'See shortcode options and examples', 'sc' ) . '</a> ' . __( 'for', 'sc' ) . ' ' . Stripe_Checkout::get_plugin_title() .
				          '<p class="description">' . __( 'Shortcode attributes take precedence and will always override site-wide default settings.', 'sc' ) . '</p>',
				'type' => 'section'
			),
			'name' => array(
				'id'   => 'name',
				'name' => __( 'Site Name', 'sc' ),
				'desc' => __( 'The name of your store or website. Defaults to Site Name.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'currency' => array(
				'id'   => 'currency',
				'name' => __( 'Currency Code', 'sc' ),
				'desc' => __( 'Specify a currency using it\'s ', 'sc' ) .
							sprintf( '<a href="%s" target="_blank">%s</a>', 'https://support.stripe.com/questions/which-currencies-does-stripe-support', __('3-letter ISO Code', 'sc' ) ) . '. ' .
							__( 'Defaults to USD.', 'sc' ),
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
				'name' => __( 'Success Redirect URL', 'sc' ),
				'desc' => __( 'The URL that the user should be redirected to after a successful payment.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'disable_success_message' => array(
				'id'   => 'disable_success_message',
				'name' => __( 'Disable Success Message', 'sc' ),
				'desc' => __( 'Disable default success message. Useful if you are redirecting to your own success page.', 'sc' ),
				'type' => 'checkbox'
			),
			'failure_redirect_url' => array(
				'id'   => 'failure_redirect_url',
				'name' => __( 'Failure Redirect URL', 'sc' ),
				'desc' => __( 'The URL that the user should be redirected to after a failed payment.' , 'sc' ),
				'type' => 'text',
				'size' => 'regular-text'
			),
			'billing' => array(
				'id'   => 'billing',
				'name' => __( 'Enable Billing Address', 'sc' ),
				'desc' => __( 'Require the user to enter their billing address during checkout.', 'sc' ) . 
						( class_exists( 'Stripe_Checkout_Pro' ) ? '<br><em>' . __( 'See below if you also need to require a shipping address.', 'sc' ) . '</em>' : '' ),
				'type' => 'checkbox'
			),
			'verify_zip' => array(
				'id'   => 'verify_zip',
				'name' => __( 'Verify Zip Code', 'sc' ),
				'desc' => __( 'Verifies the zip code of the card.', 'sc' ),
				'type' => 'checkbox'
			),
			'enable_remember' => array(
				'id'   => 'enable_remember',
				'name' => __( 'Enable "Remember Me"', 'sc' ),
				'desc' => __( 'Adds a "Remember Me" option to the checkout form to allow the user to store their credit card for future use with other sites using Stripe. ', 'sc' ) .
					sprintf( '<a href="%s" target="_blank">%s</a>', 'https://stripe.com/checkout/info', __('See how it works', 'sc' ) ) . '.',
				'type' => 'checkbox'
			),
			'disable_css' => array(
				'id'   => 'disable_css',
				'name' => __( 'Disable Form CSS', 'sc' ),
				'desc' => __( 'Disable the plugin from ouputting the default form CSS.', 'sc' ),
				'type' => 'checkbox'
			),
			'uninstall_save_settings' => array(
				'id'   => 'uninstall_save_settings',
				'name' => __( 'Save Settings', 'sc' ),
				'desc' => __( 'Save your settings when uninstalling this plugin. Useful when upgrading or re-installing.', 'sc' ),
				'type' => 'checkbox'
			)
		),
		
		/* Keys settings */
		'keys' => array(
			'enable_live_key' => array(
				'id'   => 'enable_live_key',
				'name' => __( 'Test or Live Mode', 'sc' ),
				'desc' => '<p class="description">' . __( 'Toggle between using your Test or Live API keys.', 'sc' ) . '</p>',
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
		)
	);
	
	$sc_settings = apply_filters( 'sc_settings', $sc_settings );
	
	$sc_settings_title = '';
	
	foreach( $sc_settings as $setting => $option ) {
		
		if( false == get_option( 'sc_settings_' . $setting ) ) {
			add_option( 'sc_settings_' . $setting );
		}
		
		add_settings_section(
			'sc_settings_' . $setting,
			apply_filters( 'sc_settings_' . $setting . '_title', $sc_settings_title ),
			'__return_false',
			'sc_settings_' . $setting
		);
		
		foreach ( $sc_settings[$setting] as $option ) {
			add_settings_field(
				'sc_settings_' . $setting . '[' . $option['id'] . ']',
				$option['name'],
				function_exists( 'sc_' . $option['type'] . '_callback' ) ? 'sc_' . $option['type'] . '_callback' : 'sc_missing_callback',
				'sc_settings_' . $setting,
				'sc_settings_' . $setting,
				sc_get_settings_field_args( $option, $setting )
			);
		}
		
		register_setting( 'sc_settings_' . $setting, 'sc_settings_' . $setting, 'sc_settings_sanitize' );
		
		$sc_options = array_merge( $sc_options, is_array( get_option( 'sc_settings_' . $setting ) ) ? get_option( 'sc_settings_' . $setting ) : array() );
	}
	
	update_option( 'sc_settings_master', $sc_options );
	
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
		'std'     => isset( $option['std'] ) ? $option['std'] : '',
		'product' => isset( $option['product'] ) ? $option['product'] : ''
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
	

	$checked = ( isset( $sc_options[$args['id']] ) ? checked( 1, $sc_options[$args['id']], false ) : '' );

	$html = "\n" . '<input type="checkbox" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>' . "\n";

	// Render description text directly to the right in a label if it exists.
	if ( ! empty( $args['desc'] ) )
		$html .= '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>' . "\n";

	echo $html;
}


/*
 * Section callback function
 * 
 * @since 1.0.0
 * 
 */
function sc_section_callback( $args ) {
	$html = '';
	
	if ( ! empty( $args['desc'] ) ) {
		$html .= $args['desc'];
	}

	echo $html;
}

/*
 * Function we can use to sanitize the input data and return it when saving options
 * 
 * @since 1.0.0
 * 
 */
function sc_settings_sanitize( $input ) {
	return $input;
}

/**
 * Radio button callback function
 *
 * @since 1.1.1
 */
function sc_radio_callback( $args ) {
	global $sc_options;

	foreach ( $args['options'] as $key => $option ) {
		$checked = false;
	

		if ( isset( $sc_options[ $args['id'] ] ) && $sc_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $sc_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" id="sc_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	}

	echo '<p class="description">' . $args['desc'] . '</p>';
}

/*
 * License Keys callback function
 * 
 * @since 1.1.1
 */
function sc_license_callback( $args ) {
	global $sc_options;
	
	if ( isset( $sc_options[ $args['id'] ] ) ) {
		$value = $sc_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}
	
	$item = '';
	
	$html  = '<div class="license-wrap">';
	
	$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular-text';
	$html .= "\n" . '<input type="text" class="' . $size . '" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . trim( esc_attr( $value ) ) . '"/>' . "\n";
	
	
	$licenses = get_option( 'sc_licenses' );
	
	
	// Add button on side of input
	if( ! empty( $licenses[ $args['product'] ] ) && $licenses[ $args['product'] ] == 'valid' && ! empty( $value ) ) {
		$html .= '<button class="button" data-sc-action="deactivate_license" data-sc-item="' .
		         ( ! empty( $args['product'] ) ? $args['product'] : 'none' ) . '">' . __( 'Deactivate', 'sc' ) . '</button>';
	} else {
		$html .= '<button class="button" data-sc-action="activate_license" data-sc-item="' .
		         ( ! empty( $args['product'] ) ? $args['product'] : 'none' ) . '">' . __( 'Activate', 'sc' ) . '</button>';
	}
	
	$license_class = '';
	$valid_message = '';
	
	$valid = sc_check_license( $value, $args['product'] );

	if( $valid == 'valid' ) {
		$license_class = 'sc-valid';
		$valid_message = __( 'License is valid and active.', 'sc' );
	} else if( $valid == 'notfound' ) {
		$license_class = 'sc-invalid';
		$valid_message = __( 'License service could not be found. Please contact support for assistance.', 'sc' );
	} else {
		$license_class = 'sc-inactive';
		$valid_message = __( 'License is inactive.', 'sc' );
	}
	
	$html .= '<span class="sc-spinner-wrap"><span class="spinner sc-spinner"></span></span>';
	$html .= '<span class="sc-license-message ' . $license_class . '">' . $valid_message . '</span>';
	
	// Render and style description text underneath if it exists.
	if ( ! empty( $args['desc'] ) ) {
		$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";
	}
	
	$html .= '</div>';
	
	echo $html;
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
 * Set the default settings when first installed
 * 
 * @since 1.0.0
 * 
 */
function sc_set_defaults() {
	if( ! get_option( 'sc_has_run' ) ) {
		$defaults = get_option( 'sc_settings_default' );
		$defaults['enable_remember'] = 1;
		$defaults['uninstall_save_settings'] = 1;
		update_option( 'sc_settings_default', $defaults );
		
		add_option( 'sc_has_run', 1 );
	}
}

/*
 * Update the global settings
 * 
 * @since 1.1.1
 */
function sc_get_settings() {
	
	$sc_options = get_option( 'sc_settings_master' );
	
	if( isset( $sc_options['currency'] ) ) {
		$sc_options['currency'] = strtoupper( $sc_options['currency'] );
	}
	
	
	
	return $sc_options;
}
