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
 * Create an run a validated filter on arbitrary data.
 *
 * @since unknown
 *
 * @param string $filter The name of the filter.
 * @param mixed  $value The value to filter.
 * @param mixed  $form_id If the data is form-specicific, provide the ID of
 *                        the form the data is being accessed through.
 * @return mixed
 */
function simpay_get_filtered( $filter, $value, $form_id = null ) {
	/**
	 * Filter an arbitrary form value.
	 *
	 * @since unknown
	 *
	 * @param mixed $value Value to filter.
	 * @param mixed $form_id ID of the form the data is being accessed through. Null if no form is available.
	 */
	$value = apply_filters( 'simpay_' . $filter, $value, $form_id );

	if ( $form_id ) {
		/**
		 * Filter an arbitrary form value.
		 *
		 * Since 3.5.0 the form ID is passed as an additional parameter.
		 *
		 * @since unknown
		 *
		 * @param mixed $value Value to filter.
		 * @param int   $form_id ID of the form the data is being accessed through.
		 */
		$value = apply_filters( 'simpay_form_' . $form_id . '_' . $filter, $value, $form_id );
	}

	return $value;
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
		return simpay_format_currency( simpay_get_setting( 'amount' ) );
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

	if( is_numeric( $object ) ) {
		$object = get_post( $object );
	}

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
 * Return test mode badge html if in test mode.
 *
 * @return string
 */
function simpay_get_test_mode_badge() {
	$html = '';

	if ( simpay_is_test_mode() ) {
		$html .= '<div class="simpay-test-mode-badge-container">';
		$html .= '<span class="simpay-test-mode-badge">' . esc_html__( 'Test Mode', 'stripe' ) . '</span>';
		$html .= '</div>';
	}

	return $html;
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
		'mediaTitle'      => esc_html__( 'Insert Media', 'stripe' ),
		'mediaButtonText' => esc_html__( 'Use Image', 'stripe' ),
	);

	$integers['integers'] = array(
		'decimalPlaces' => simpay_get_decimal_places(),
		'minAmount'     => simpay_global_minimum_amount(),
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
 * Return amount as number value.
 * Uses global (or filtered) decimal separator setting ("." or ",") & thousand separator setting.
 * Like accounting.unformat removes formatting/cruft first.
 * Respects decimal separator, but ignores zero decimal currency setting.
 * Also prevent negative values.
 * Similar to JS function unformatCurrency.
 *
 * @param string|float $amount
 *
 * @return float
 */
function simpay_unformat_currency( $amount ) {

	// Remove thousand separator.
	$amount = str_replace( simpay_get_thousand_separator(), '', $amount );

	// Replace decimal separator with an actual decimal point to allow converting to float.
	$amount = str_replace( simpay_get_decimal_separator(), '.', $amount );

	return abs( floatval( $amount ) );
}

/**
 * Convert from dollars to cents (in USD).
 * Leaves zero decimal currencies alone.
 * Similar to JS function convertToCents.
 *
 * @param string|float|int $amount
 *
 * @return int
 */
function simpay_convert_amount_to_cents( $amount ) {

	$amount = simpay_unformat_currency( $amount );

	if ( simpay_is_zero_decimal() ) {
		return intval( strval( $amount ) );
	} else {
		return intval( strval( $amount * 100 ) );
	}
}

/**
 * Convert from cents to dollars (in USD).
 * Uses global zero decimal currency setting.
 * Leaves zero decimal currencies alone.
 * Similar to JS function convertToDollars.
 *
 * @param string|int $amount
 *
 * @return int|float
 */
function simpay_convert_amount_to_dollars( $amount ) {

	$amount = simpay_unformat_currency( $amount );

	if ( ! simpay_is_zero_decimal() ) {
		$amount = round( intval( $amount ) / 100, simpay_get_decimal_places() );
	}

	return $amount;
}

/**
 * Get the global system-wide minimum amount. Stripe dictates minimum USD is 50 cents, but set to 100 cents/currency
 * units as it can vary from currency to currency.
 *
 * @return int
 */
function simpay_global_minimum_amount() {

	// Initially set to 1.00 for non-zero decimal currencies (i.e. $1.00 USD).
	$amount = 1;

	if ( simpay_is_zero_decimal() ) {
		$amount = 100;
	}

	return floatval( apply_filters( 'simpay_global_minimum_amount', $amount ) );
}

/**
 * Retrieves a list of unsupported characters for Stripe statement descriptors.
 *
 * @since 3.6.5
 *
 * @return array $unsupported_characters List of unsupported characters.
 */
function simpay_get_statement_descriptor_unsupported_characters() {
	$unsupported_characters = array(
		'<',
		'>',
		'"',
		'\'',
		'\\',
		'*',
	);

	/**
	 * Filters the list of unsupported characters for Stripe statement descriptors.
	 *
	 * @since 3.6.5
	 *
	 * @param array $unsupported_characters List of unsupported characters.
	 */
	$unsupported_characters = apply_filters(
		'simpay_get_statement_descriptor_unsupported_characters',
		$unsupported_characters 
	);

	return $unsupported_characters;
}

/**
 * Validate a statement subscription for a charge or plan.
 *
 * @since 3.4.0
 *
 * @param string $statement_descriptor Statement description to validate.
 * @return mixed Description or null.
 */
function simpay_validate_statement_descriptor( $statement_descriptor ) {
	if ( ! is_string( $statement_descriptor ) ) {
		$statement_descriptor = '';
	}

	// Remove unsupported characters.
	$unsupported_characters = simpay_get_statement_descriptor_unsupported_characters();
	$statement_descriptor   = trim( str_replace( $unsupported_characters, '', $statement_descriptor ) );

	// Trim to 22 characters max
	$statement_descriptor = substr( $statement_descriptor, 0, 22 );

	return $statement_descriptor;
}

/**
 * Return amount as formatted string.
 * With or without currency symbol.
 * Used for labels & amount inputs in admin & front-end.
 * Uses global (or filtered) decimal separator setting ("." or ",") & thousand separator setting.
 * Similar to JS function formatCurrency.
 *
 * @param        $amount
 * @param string $currency
 * @param bool   $show_symbol
 *
 * @return string
 */
function simpay_format_currency( $amount, $currency = '', $show_symbol = true ) {

	if ( empty( $currency ) ) {
		$currency = simpay_get_setting( 'currency' );
	}

	$symbol = simpay_get_currency_symbol( $currency );

	$position = simpay_get_setting( 'currency_position' );

	$amount = number_format( floatval( $amount ), simpay_get_decimal_places(), simpay_get_decimal_separator(), simpay_get_thousand_separator() );

	$amount = apply_filters( 'simpay_formatted_amount', $amount );

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

/**
 * Insert an array key/value pair after a certain point in an existing associative array.
 *
 * @since 3.4.0
 *
 * @param $new_key  string The new key to use for $fields[ $section ][ $new_key ]
 * @param $value    array The array that holds the information for this settings array
 * @param $needle   string The key to find in the current array of fields
 * @param $haystack array The current array to search
 * @return array
 */
function simpay_add_to_array_after( $new_key, $value, $needle, $haystack ) {
	$split = array(); // The split off portion of the array after the key we want to insert after
	$new   = array(); // The new array will consist of the opposite of the split + the new element we want to add

	if ( array_key_exists( $needle, $haystack ) ) {
		$offset = array_search( $needle, array_keys( $haystack ) );

		$split = array_slice( $haystack, $offset + 1 );
		$new   = array_slice( $haystack, 0, $offset + 1 );

		// Add the new element to the bottom
		$new[ $new_key ] = $value;
	}

	return $new + $split;
}

/**
 * Generate a shipping object containing the required fields for the Stripe API.
 *
 * @param string $type The type of address (billing or shipping).
 * @param array  $fields The field data list. Assumes data is coming from a payment form.
 * @return array
 */
function simpay_get_form_address_data( $type, $fields ) {
	$prefix = 'billing' === $type ? 'simpay_billing_address_' : 'simpay_shipping_address_';

	// No address field is filled out, bail.
	if ( ! isset( $fields[ $prefix . 'line1' ] ) || '' === $fields[ $prefix . 'line1' ] ) {
		return array();
	}

	$address        = array();
	$address_fields = array(
		'line1',
		'city',
		'state',
		'postal_code',
		'country',
	);

	// Add field to address object.
	foreach ( $address_fields as $field ) {
		$value = isset( $fields[ $prefix . $field ] ) ? $fields[ $prefix . $field ] : null;

		$address[ $field ] = sanitize_text_field( $value );
	}

	return array_filter( $address );
}
