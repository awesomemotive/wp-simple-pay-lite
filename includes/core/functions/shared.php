<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared functions
 *
 * Functions shared by both back end and front end components.
 */

use SimplePay\Core\Abstracts\Form;

// TODO Need function simpay_clean?

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @since  3.0
 * @param  string|array $var
 *
 * @return string|array
 */
function simpay_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'simpay_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Get a Simple Pay setting. It will check for both a form setting or a global setting option.
 *
 * @param $setting
 *
 * @return bool|mixed|null
 */
function simpay_get_setting( $setting ) {

	// If we are in the admin we don't want to use filters so we get the raw global setting value
	if ( is_admin() ) {
		return simpay_get_global_setting( $setting, true );
	}

	global $simpay_form;

	$global = simpay_get_global_setting( $setting );
	$form   = simpay_get_form_setting( $setting );

	$form_setting   = null;
	$global_setting = null;

	if ( ! $global && ! $form ) {
		return false;
	}

	if ( $simpay_form ) {
		$form_setting = simpay_get_filtered( $setting, simpay_get_form_setting( $setting, $simpay_form->id ), $simpay_form->id );
	}

	if ( ! $form_setting ) {

		$global_setting = simpay_get_filtered( $setting, simpay_get_global_setting( $setting ) );

		return $global_setting;
	}

	return $form_setting;
}

/**
 * Get a specific form setting.
 *
 * @param      $setting
 * @param null $form_id
 *
 * @return bool
 */
function simpay_get_form_setting( $setting, $form_id = null ) {

	global $simpay_form;

	// We want to use the form ID if it is passed, but only if there isn't a global form set
	if ( ! $simpay_form && $form_id ) {
		$simpay_form = simpay_get_form( $form_id );
	}

	if ( $simpay_form ) {

		if ( $simpay_form instanceof Form && isset( $simpay_form->$setting ) ) {
			return $simpay_form->$setting;
		}
	}

	return false;
}

/**
 * Get a global setting.
 *
 * @param      $setting
 * @param bool $raw Whether to return the filtered setting data or just the raw saved value in the main settings.
 *
 * @return bool|mixed
 */
function simpay_get_global_setting( $setting, $raw = false ) {

	// This works but there must be a nicer way to do this

	$general = get_option( 'simpay_settings_general' );
	$keys    = get_option( 'simpay_settings_keys' );
	$display = get_option( 'simpay_settings_display' );

	$general = false !== $general ? $general : array();
	$keys    = false !== $keys ? $keys : array();
	$display = false !== $display ? $display : array();

	// Jam all of our settings into one array
	$mega = apply_filters( 'simpay_global_settings', array_merge( $general, $keys, $display ) );

	if ( ! empty( $mega ) ) {
		foreach ( $mega as $k => $v ) {

			if ( ! empty( $v ) && is_array( $v ) ) {
				foreach ( $v as $k2 => $v2 ) {
					if ( $setting == $k2 ) {
						if ( $raw ) {
							return $v2;
						} else {
							return simpay_get_filtered( $setting, $v2 );
						}
					} else {

					}
				}
			}
		}
	}

	return false;
}

/**
 * This will get a filtered setting. If a form specific filter is found it will use that one as higher priority over a
 * general setting filter.
 *
 * @param      $filter
 * @param      $value
 * @param null $form_id
 *
 * @return mixed
 */
function simpay_get_filtered( $filter, $value, $form_id = null ) {

	$use_form_filter = false;
	$form_filter     = '';

	if ( $form_id ) {

		$form_filter = 'simpay_form_' . $form_id . '_' . $filter;

		if ( has_filter( $form_filter ) ) {
			$use_form_filter = true;
		}
	}

	if ( $use_form_filter ) {
		return apply_filters( $form_filter, $value );
	} else {
		return apply_filters( 'simpay_' . $filter, $value );
	}
}

/**
 * Return the total amount for the form.
 *
 * @param bool $formatted
 *
 * @return string
 */
function simpay_get_total( $formatted = true ) {

	if ( $formatted ) {
		return simpay_formatted_amount( simpay_get_setting( 'amount' ) );
	}

	return simpay_get_setting( 'amount' );
}

/**
 * Get plugin URL.
 *
 * @param  string $url
 *
 * @return string
 */
function simpay_get_url( $url ) {
	return \SimplePay\Core\SimplePay()->get_url( $url );
}

/**
 * Print an error message only to those with admin privileges
 *
 * @param string $message
 * @param bool   $echo
 *
 * @return string
 */
function simpay_admin_error( $message, $echo = true ) {

	$return = '';

	if ( current_user_can( 'manage_options' ) ) {
		$return = $message;
	}

	if ( $echo ) {
		echo $return;
	} else {
		return $return;
	}

	return '';
}

/**
 * Get a form.
 *
 * @since  3.0.0
 *
 * @param  string|int|object|WP_Post $object
 *
 * @return null|\SimplePay\Core\Abstracts\Form
 */
function simpay_get_form( $object ) {
	$objects = \SimplePay\Core\SimplePay()->objects;

	return $objects instanceof \SimplePay\Core\Objects ? $objects->get_form( $object ) : null;
}

/**
 * Get a field.
 *
 * @since  3.0.0
 *
 * @param  array  $args
 * @param  string $name
 *
 * @return null|\SimplePay\Core\Abstracts\Field
 */
function simpay_get_field( $args, $name = '' ) {
	$objects = \SimplePay\Core\SimplePay()->objects;

	return $objects instanceof \SimplePay\Core\Objects ? $objects->get_field( $args, $name ) : null;
}

/**
 * Print a field.
 *
 * @since  3.0.0
 *
 * @param  array  $args
 * @param  string $name
 *
 * @return void
 */
function simpay_print_field( $args, $name = '' ) {

	$field = simpay_get_field( $args, $name );

	if ( $field instanceof \SimplePay\Core\Abstracts\Field ) {
		$field->html();
	}
}

/**
 * Change underscores to dashes in a string
 */
function simpay_dashify( $string ) {

	return str_replace( '_', '-', $string );

}

/**
 * Check if test mode is enabled.
 *
 * Returns true if test mode enabled or false if not
 */
function simpay_is_test_mode() {

	$settings = get_option( 'simpay_settings_keys' );

	return ( isset( $settings['mode']['test_mode'] ) && 'enabled' === $settings['mode']['test_mode'] );
}

/**
 * Get the stored API Secret Key
 */
function simpay_get_secret_key() {

	global $simpay_form;

	$test_mode = simpay_is_test_mode();

	if ( ! empty( $simpay_form ) ) {
		return $simpay_form->secret_key;
	} else {

		$settings = get_option( 'simpay_settings_keys' );

		$test_secret_key = isset( $settings['test_keys']['secret_key'] ) ? $settings['test_keys']['secret_key'] : '';
		$live_secret_key = isset( $settings['live_keys']['secret_key'] ) ? $settings['live_keys']['secret_key'] : '';
	}

	// If we are not in test mode use the live key
	if ( ! $test_mode ) {
		// live mode key
		return trim( $live_secret_key );
	}

	// Return test mode key by default
	return trim( $test_secret_key );

}

/**
 * Get the stored API Publishable Key
 */
function simpay_get_publishable_key() {

	global $simpay_form;

	$test_mode = simpay_is_test_mode();

	if ( ! empty( $simpay_form ) ) {
		return $simpay_form->publishable_key;
	} else {

		$settings = get_option( 'simpay_settings_keys' );

		$test_publishable_key = isset( $settings['test_keys']['publishable_key'] ) ? $settings['test_keys']['publishable_key'] : '';
		$live_publishable_key = isset( $settings['live_keys']['publishable_key'] ) ? $settings['live_keys']['publishable_key'] : '';
	}

	// If we are not in test mode use the live key
	if ( ! $test_mode ) {
		// live mode key
		return trim( $live_publishable_key );
	}

	// Return test mode key by default
	return trim( $test_publishable_key );
}

/**
 * Check that the API keys actually exist.
 */
function simpay_check_keys_exist() {

	$secret_key      = simpay_get_secret_key();
	$publishable_key = simpay_get_publishable_key();


	if ( ! empty( $secret_key ) && ! empty( $publishable_key ) ) {
		return true;
	}

	return false;
}

/**
 * Get the currency symbol saved by the user
 */
function simpay_get_saved_currency_symbol() {
	return simpay_get_currency_symbol( simpay_get_setting( 'currency' ) );
}

/**
 * Get the saved currency position value
 */
function simpay_get_currency_position() {

	$position = simpay_get_setting( 'currency_position' );

	return ( ! empty( $position ) ? $position : 'left' );
}

/**
 * Get a saved meta setting from a form
 *
 * @param        $post_id
 * @param        $setting
 * @param string $default
 * @param bool   $single
 *
 * @return mixed|string
 */
function simpay_get_saved_meta( $post_id, $setting, $default = '', $single = true ) {

	if ( empty( $post_id ) ) {
		return '';
	}

	// Check for custom keys array. If it doesn't exist then that means this is a brand new form.
	// See also comment from memuller here: https://developer.wordpress.org/reference/functions/get_post_meta/#user-contributed-notes
	$custom_keys = get_post_custom_keys( $post_id );

	if ( empty( $custom_keys ) || ! in_array( $setting, $custom_keys ) ) {
		return $default;
	}

	$value = get_post_meta( $post_id, $setting, $single );

	if ( empty( $value ) && ! empty ( $default ) ) {
		return $default;
	}

	return $value;
}

/**
 * Localize the shared script with the shared script variables.
 */
function simpay_shared_script_variables() {

	$strings = array();

	$bools['booleans'] = array(
		'isZeroDecimal' => simpay_is_zero_decimal(),
		'scriptDebug'   => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
	);

	$strings['strings'] = array(
		'currency'          => simpay_get_setting( 'currency' ),
		'currencySymbol'    => html_entity_decode( simpay_get_saved_currency_symbol() ),
		'currencyPosition'  => simpay_get_currency_position(),
		'decimalSeparator'  => simpay_get_decimal_separator(),
		'thousandSeparator' => simpay_get_thousand_separator(),
		'ajaxurl'           => admin_url( 'admin-ajax.php' ),
	);

	$i18n['i18n'] = array(
		'mediaTitle'                      => esc_html__( 'Insert Media', 'stripe' ),
		'mediaButtonText'                 => esc_html__( 'Use Image', 'stripe' ),
	);

	$integers['integers'] = array(
		'decimalPlaces' => simpay_get_decimal_places(),
	    'minAmount'     => simpay_get_stripe_minimum_amount(),
	);

	$final = apply_filters( 'simpay_shared_script_variables', array_merge( $strings, $bools, $i18n, $integers ) );

	wp_localize_script( 'simpay-shared', 'spGeneral', $final );
}

/**
 * Function to return the array of Zero Decimal currencies
 * https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
 */
function simpay_get_zero_decimal_currencies() {

	return apply_filters( 'simpay_zero_decimal_currencies', array(
		'bif' => esc_html__( 'Burundian Franc', 'stripe' ),
		'clp' => esc_html__( 'Chilean Peso', 'stripe' ),
		'djf' => esc_html__( 'Djiboutian Franc', 'stripe' ),
		'gnf' => esc_html__( 'Guinean Franc', 'stripe' ),
		'jpy' => esc_html__( 'Japanese Yen', 'stripe' ),
		'kmf' => esc_html__( 'Comorian Franc', 'stripe' ),
		'krw' => esc_html__( 'South Korean Won', 'stripe' ),
		'mga' => esc_html__( 'Malagasy Ariary', 'stripe' ),
		'pyg' => esc_html__( 'Paraguayan Guaraní', 'stripe' ),
		'rwf' => esc_html__( 'Rwandan Franc', 'stripe' ),
		'vnd' => esc_html__( 'Vietnamese Dong', 'stripe' ),
		'vuv' => esc_html__( 'Vanuatu Vatu', 'stripe' ),
		'xaf' => esc_html__( 'Central African Cfa Franc', 'stripe' ),
		'xof' => esc_html__( 'West African Cfa Franc', 'stripe' ),
		'xpf' => esc_html__( 'Cfp Franc', 'stripe' ),
	) );
}

/**
 * Check if the currency is set to a zero decimal currency or not.
 *
 * @return bool
 */
function simpay_is_zero_decimal( $currency = '' ) {

	$zero_decimal_currencies = simpay_get_zero_decimal_currencies();

	if ( empty( $currency ) ) {
		$currency = simpay_get_setting( 'currency' );
	}

	if ( array_key_exists( strtolower( $currency ), $zero_decimal_currencies ) ) {
		return true;
	}

	return false;
}

/**
 * Convert an amount to cents.
 *
 * @param        $amount
 * @param string $decimal_separator
 * @param string $thousand_separator
 *
 * @return float|int
 */
function simpay_convert_amount_to_cents( $amount, $decimal_separator = '', $thousand_separator = '' ) {

	$decimal_separator  = ( ! empty( $decimal_separator ) ? $decimal_separator : simpay_get_decimal_separator() );
	$thousand_separator = ( ! empty( $thousand_separator ) ? $thousand_separator : simpay_get_thousand_separator() );

	// Remove thousand separator
	$amount = str_replace( $thousand_separator, '', $amount );

	// Now replace decimal separator with an actual decimal point for processing purposes
	$amount = str_replace( $decimal_separator, '.', $amount );

	if ( simpay_is_zero_decimal() ) {
		return intval( $amount );
	} else {
		return floatval( $amount ) * 100;
	}

}

/**
 * Get the thousands separator.
 *
 * @return string
 */
function simpay_get_thousand_separator() {

	$swap = 'yes' === simpay_get_setting( 'separator' ) ? true : false;

	$separator = ',';

	if ( $swap ) {
		$separator = '.';
	}

	// Depending on if admin or frontend we enable a filter.
	if ( is_admin() ) {
		return $separator;
	} else {
		// This is a special case where we need a filter that's different from our global option (in this case it's a bool value checkbox)
		return apply_filters( 'simpay_thousand_separator', $separator );
	}
}

/**
 * Get the decimal separator.
 *
 * @return string
 */
function simpay_get_decimal_separator() {

	$swap = 'yes' === simpay_get_setting( 'separator' ) ? true : false;

	$decimal = '.';

	if ( $swap ) {
		$decimal = ',';
	}

	if ( is_admin() ) {
		return $decimal;
	} else {
		// This is a special case where we need a filter that's different from our global option (in this case it's a bool value checkbox)
		return apply_filters( 'simpay_decimal_separator', $decimal );
	}
}

/**
 * Get the number of decimal places to use.
 *
 * @return int
 */
function simpay_get_decimal_places() {

	$decimal_places = 2;

	if ( simpay_is_zero_decimal() ) {
		$decimal_places = 0;
	}

	return intval( apply_filters( 'simpay_decimal_places', $decimal_places ) );
}

/**
 * Get the Stripe minimum amount
 *
 * @param bool $cents
 *
 * @return float
 */
function simpay_get_stripe_minimum_amount( $cents = false ) {

	// Check if we want the number in cents or a formatted amount based on the zero decimal amount
	if ( $cents ) {
		$minimum = '50';
	} else {
		$minimum = simpay_is_zero_decimal() ? '50' : '.50';
	}

	return floatval( apply_filters( 'simpay_stripe_minimum_amount', $minimum ) );
}

/**
 * Return the formatted amount.
 *
 * @param        $amount
 * @param string $currency
 * @param bool   $show_symbol
 * @param string $separator
 *
 * @return string
 */
function simpay_formatted_amount( $amount, $currency = '', $show_symbol = true, $separator = '' ) {

	if ( empty( $currency ) ) {
		$symbol = simpay_get_currency_symbol( simpay_get_setting( 'currency' ) );
	} else {
		$symbol = simpay_get_currency_symbol( $currency );
	}

	$position = simpay_get_setting( 'currency_position' );

	// Non-zero
	if ( simpay_is_zero_decimal( $currency ) ) {
		$amount = number_format( intval( $amount ), 0, simpay_get_decimal_separator(), simpay_get_thousand_separator() );
	} else {
		$amount = number_format( floatval( $amount ) / 100, simpay_get_decimal_places(), simpay_get_decimal_separator(), simpay_get_thousand_separator() );
	}

	$amount = apply_filters( 'simpay_formatted_amount', $amount, $amount );

	if ( $show_symbol ) {
		if ( 'left' === $position ) {
			return $symbol . $amount;
		} elseif ( 'left_space' === $position ) {
			return $symbol . ' ' . $amount;
		} elseif ( 'right' === $position ) {
			return $amount . $symbol;
		} elseif ( 'right_space' === $position ) {
			return $amount . ' ' . $symbol;
		}
	}

	return $amount;
}

/**
 * Get the default editor content based on what type of editor is passed in
 *
 * @param $editor
 *
 * @return mixed|string
 */
function simpay_get_editor_default( $editor ) {

	if ( empty( $editor ) ) {
		return '';
	}

	$template = '';

	switch ( $editor ) {
		case 'one_time':
			$template .= __( 'Thanks for your purchase. Here are the details of your payment:', 'stripe' ) . "\n\n";
			$template .= '<strong>' . esc_html__( 'Item:', 'stripe' ) . '</strong>' . ' {item-description}' . "\n";
			$template .= '<strong>' . esc_html__( 'Purchased From:', 'stripe' ) . '</strong>' . ' {company-name}' . "\n";
			$template .= '<strong>' . esc_html__( 'Payment Date:', 'stripe' ) . '</strong>' . ' {charge-date}' . "\n";
			$template .= '<strong>' . esc_html__( 'Payment Amount: ', 'stripe' ) . '</strong>' . '{total-amount}' . "\n";
			return $template;
		case has_filter( 'simpay_editor_template' ):
			return apply_filters( 'simpay_editor_template', '', $editor );
		default:
			return '';
	}
}

/**
 * Get a specific currency symbol
 *
 * We need to make sure we keep these up to date if Stripe adds any more
 * https://support.stripe.com/questions/which-currencies-does-stripe-support
 */
function simpay_get_currency_symbol( $currency = '' ) {

	if ( ! $currency ) {

		// If no currency is passed then default it to USD
		$currency = 'USD';
	}

	$currency = strtoupper( $currency );

	$symbols = apply_filters( 'simpay_currency_symbols', array(
		'AED' => '&#x62f;.&#x625;',
		'AFN' => '&#x60b;',
		'ALL' => 'L',
		'AMD' => 'AMD',
		'ANG' => '&fnof;',
		'AOA' => 'Kz',
		'ARS' => '&#36;',
		'AUD' => '&#36;',
		'AWG' => '&fnof;',
		'AZN' => 'AZN',
		'BAM' => 'KM',
		'BBD' => '&#36;',
		'BDT' => '&#2547;&nbsp;',
		'BGN' => '&#1083;&#1074;.',
		'BHD' => '.&#x62f;.&#x628;',
		'BIF' => 'Fr',
		'BMD' => '&#36;',
		'BND' => '&#36;',
		'BOB' => 'Bs.',
		'BRL' => '&#82;&#36;',
		'BSD' => '&#36;',
		'BTC' => '&#3647;',
		'BTN' => 'Nu.',
		'BWP' => 'P',
		'BYR' => 'Br',
		'BZD' => '&#36;',
		'CAD' => '&#36;',
		'CDF' => 'Fr',
		'CHF' => '&#67;&#72;&#70;',
		'CLP' => '&#36;',
		'CNY' => '&yen;',
		'COP' => '&#36;',
		'CRC' => '&#x20a1;',
		'CUC' => '&#36;',
		'CUP' => '&#36;',
		'CVE' => '&#36;',
		'CZK' => '&#75;&#269;',
		'DJF' => 'Fr',
		'DKK' => 'DKK',
		'DOP' => 'RD&#36;',
		'DZD' => '&#x62f;.&#x62c;',
		'EGP' => 'EGP',
		'ERN' => 'Nfk',
		'ETB' => 'Br',
		'EUR' => '&euro;',
		'FJD' => '&#36;',
		'FKP' => '&pound;',
		'GBP' => '&pound;',
		'GEL' => '&#x10da;',
		'GGP' => '&pound;',
		'GHS' => '&#x20b5;',
		'GIP' => '&pound;',
		'GMD' => 'D',
		'GNF' => 'Fr',
		'GTQ' => 'Q',
		'GYD' => '&#36;',
		'HKD' => '&#36;',
		'HNL' => 'L',
		'HRK' => 'Kn',
		'HTG' => 'G',
		'HUF' => '&#70;&#116;',
		'IDR' => 'Rp',
		'ILS' => '&#8362;',
		'IMP' => '&pound;',
		'INR' => '&#8377;',
		'IQD' => '&#x639;.&#x62f;',
		'IRR' => '&#xfdfc;',
		'ISK' => 'Kr.',
		'JEP' => '&pound;',
		'JMD' => '&#36;',
		'JOD' => '&#x62f;.&#x627;',
		'JPY' => '&yen;',
		'KES' => 'KSh',
		'KGS' => '&#x43b;&#x432;',
		'KHR' => '&#x17db;',
		'KMF' => 'Fr',
		'KPW' => '&#x20a9;',
		'KRW' => '&#8361;',
		'KWD' => '&#x62f;.&#x643;',
		'KYD' => '&#36;',
		'KZT' => 'KZT',
		'LAK' => '&#8365;',
		'LBP' => '&#x644;.&#x644;',
		'LKR' => '&#xdbb;&#xdd4;',
		'LRD' => '&#36;',
		'LSL' => 'L',
		'LYD' => '&#x644;.&#x62f;',
		'MAD' => '&#x62f;. &#x645;.',
		'MDL' => 'L',
		'MGA' => 'Ar',
		'MKD' => '&#x434;&#x435;&#x43d;',
		'MMK' => 'Ks',
		'MNT' => '&#x20ae;',
		'MOP' => 'P',
		'MRO' => 'UM',
		'MUR' => '&#x20a8;',
		'MVR' => '.&#x783;',
		'MWK' => 'MK',
		'MXN' => '&#36;',
		'MYR' => '&#82;&#77;',
		'MZN' => 'MT',
		'NAD' => '&#36;',
		'NGN' => '&#8358;',
		'NIO' => 'C&#36;',
		'NOK' => '&#107;&#114;',
		'NPR' => '&#8360;',
		'NZD' => '&#36;',
		'OMR' => '&#x631;.&#x639;.',
		'PAB' => 'B/.',
		'PEN' => 'S/.',
		'PGK' => 'K',
		'PHP' => '&#8369;',
		'PKR' => '&#8360;',
		'PLN' => '&#122;&#322;',
		'PRB' => '&#x440;.',
		'PYG' => '&#8370;',
		'QAR' => '&#x631;.&#x642;',
		'RMB' => '&yen;',
		'RON' => 'lei',
		'RSD' => '&#x434;&#x438;&#x43d;.',
		'RUB' => '&#8381;',
		'RWF' => 'Fr',
		'SAR' => '&#x631;.&#x633;',
		'SBD' => '&#36;',
		'SCR' => '&#x20a8;',
		'SDG' => '&#x62c;.&#x633;.',
		'SEK' => '&#107;&#114;',
		'SGD' => '&#36;',
		'SHP' => '&pound;',
		'SLL' => 'Le',
		'SOS' => 'Sh',
		'SRD' => '&#36;',
		'SSP' => '&pound;',
		'STD' => 'Db',
		'SYP' => '&#x644;.&#x633;',
		'SZL' => 'L',
		'THB' => '&#3647;',
		'TJS' => '&#x405;&#x41c;',
		'TMT' => 'm',
		'TND' => '&#x62f;.&#x62a;',
		'TOP' => 'T&#36;',
		'TRY' => '&#8378;',
		'TTD' => '&#36;',
		'TWD' => '&#78;&#84;&#36;',
		'TZS' => 'Sh',
		'UAH' => '&#8372;',
		'UGX' => 'UGX',
		'USD' => '&#36;',
		'UYU' => '&#36;',
		'UZS' => 'UZS',
		'VEF' => 'Bs F',
		'VND' => '&#8363;',
		'VUV' => 'Vt',
		'WST' => 'T',
		'XAF' => 'Fr',
		'XCD' => '&#36;',
		'XOF' => 'Fr',
		'XPF' => 'Fr',
		'YER' => '&#xfdfc;',
		'ZAR' => '&#82;',
		'ZMW' => 'ZK',
	) );

	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

	return apply_filters( 'simpay_currency_symbol', $currency_symbol, $currency );
}
