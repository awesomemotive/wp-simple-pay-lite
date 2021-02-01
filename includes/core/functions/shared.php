<?php
/**
 * Functions: Shared
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimplePay\Core\Abstracts\Form;
use SimplePay\Core\Settings;

/**
 * Returns all saved settings.
 *
 * @since 4.0.0
 *
 * @return array
 */
function simpay_get_settings() {
	return get_option( 'simpay_settings', array() );
}

/**
 * Returns a setting value.
 *
 * @todo Move to \SimplePay\Core\Settings namespace.
 *
 * @since 3.0.0
 *
 * @param string $setting Setting key.
 * @param mixed  $default Setting default.
 * @param bool   $raw If the value should be unfiltered. Default true.
 * @return mixed|null
 */
function simpay_get_setting( $setting, $default = null, $raw = true ) {
	$legacy_setting = $setting;
	$setting        = Settings\Compat\get_setting_key( $legacy_setting );

	if ( $setting !== $legacy_setting ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html(
				sprintf(
					/* translators: %1$s Legacy setting key. %2$s Migrated setting key. */
					__(
						'Legacy setting %1$s should be accessed via %2$s.',
						'stripe'
					),
					$legacy_setting,
					$setting
				)
			),
			'4.0.0'
		);
	}

	$settings = simpay_get_settings();

	/**
	 * Filters the saved settings and values.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Saved settings and values.
	 */
	$settings = apply_filters( 'simpay_global_settings', $settings );

	if ( isset( $settings[ $setting ] ) ) {
		$value = $settings[ $setting ];
	} else {
		$value = $default;
	}

	// Look in legacy options that might be used by extensions.
	if ( null === $value ) {
		$legacy = array_merge(
			get_option( 'simpay_settings_general', array() ),
			get_option( 'simpay_settings_keys', array() ),
			get_option( 'simpay_settings_display', array() )
		);

		if ( isset( $legacy[ $legacy_setting ] ) ) {
			$value = $legacy[ $legacy_setting ];
		}
	}

	if ( false === $raw ) {
		return simpay_get_filtered( $setting, $value );
	}

	return $value;
}

/**
 * Updates a setting value.
 *
 * @since 4.0.0
 *
 * @param string $setting Setting ID.
 * @param mixed  $value Setting value.
 * @return bool True if the value was updated, false otherwise.
 */
function simpay_update_setting( $setting, $value ) {
	$existing_settings = get_option( 'simpay_settings', array() );

	$settings_to_update = array(
		$setting => $value,
	);

	$new_settings = array_merge(
		$existing_settings,
		$settings_to_update
	);

	return update_option( 'simpay_settings', $new_settings );
}

/**
 * Checks if REST API is enabled.
 *
 * @link https://github.com/Automattic/jetpack/blob/master/_inc/lib/admin-pages/class.jetpack-admin-page.php#L157-L171
 *
 * @since 4.0.0
 *
 * @return bool
 */
function simpay_is_rest_api_enabled() {
	return /** This filter is documented in wp-includes/rest-api/class-wp-rest-server.php */
		apply_filters( 'rest_enabled', true ) &&
		/** This filter is documented in wp-includes/rest-api/class-wp-rest-server.php */
		apply_filters( 'rest_jsonp_enabled', true ) &&
		/** This filter is documented in wp-includes/rest-api/class-wp-rest-server.php */
		apply_filters( 'rest_authentication_errors', true );
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
 * Get plugin URL.
 *
 * @param string $url URL to retrieve.
 * @return string
 */
function simpay_get_url( $url ) {
	return \SimplePay\Core\SimplePay()->get_url( $url );
}

/**
 * Print an error message only to those with admin privileges
 *
 * @param string $message Admin error message.
 * @param bool   $echo If the message should be echoed. Default true.
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
 * Retrieves a Payment Form.
 *
 * @todo This function is not used, but should be.
 *
 * @since 3.0.0
 *
 * @param string|int|WP_Post $form_id Payment Form to retrieve.
 * @return \SimplePay\Core\Abstracts\Form
 */
function simpay_get_form( $form_id ) {
	if ( $form_id instanceof WP_Post ) {
		$form_id = $form_id->ID;
	}

	/** This filter is documented in includes/core/shortcodes.php */
	$form = apply_filters( 'simpay_form_view', '', $form_id );

	if ( empty( $form ) ) {
		$form = new Default_Form( $form_id );
	}

	// @todo Validate if a form is found.

	return $form;
}

/**
 * Get a field.
 *
 * @since 3.0.0
 *
 * @param array  $args Field arguments.
 * @param string $name Field name.
 * @return null|\SimplePay\Core\Abstracts\Field
 */
function simpay_get_field( $args, $name = '' ) {
	$objects = \SimplePay\Core\SimplePay()->objects;

	return $objects instanceof \SimplePay\Core\Objects
		? $objects->get_field( $args, $name )
		: null;
}

/**
 * Prints a field.
 *
 * @since 3.0.0
 *
 * @param array  $args Field arguments.
 * @param string $name Field name.
 */
function simpay_print_field( $args, $name = '' ) {
	$field = simpay_get_field( $args, $name );

	if ( $field instanceof \SimplePay\Core\Abstracts\Field ) {
		$field->html();
	}
}

/**
 * Changes underscores to dashes in a string.
 *
 * @since 3.0.0
 *
 * @param string $string String to convert underscores to dashes.
 * @return string
 */
function simpay_dashify( $string ) {
	return str_replace( '_', '-', $string );
}

/**
 * Determines if the global payment mode is Test Mode.
 *
 * @since 3.0
 *
 * @return bool True if in Test Mode.
 */
function simpay_is_test_mode() {
	return 'enabled' === simpay_get_setting( 'test_mode', 'enabled' );
}

/**
 * Returns test mode badge html if in test mode.
 *
 * @return string
 */
function simpay_get_test_mode_badge() {
	$html = '';

	$html .= '<div class="simpay-test-mode-badge-container">';
	$html .= '<span class="simpay-test-mode-badge">' . esc_html__( 'Test Mode', 'stripe' ) . '</span>';
	$html .= '</div>';

	return $html;
}

/**
 * Returns the Stripe Secret Key for the current payment mode.
 *
 * @since 3.0.0
 *
 * @return string
 */
function simpay_get_secret_key() {
	$test_mode = simpay_is_test_mode();

	$setting_key = $test_mode
		? 'test_secret_key'
		: 'live_secret_key';

	$secret_key = trim(
		simpay_get_setting( $setting_key, '' )
	);

	/**
	 * Filters the Stripe API secret key.
	 *
	 * @since 3.6.6
	 *
	 * @param string $secret_key Stripe API secret key.
	 * @param bool   $test_mode If test mode is enabled.
	 */
	$secret_key = apply_filters(
		'simpay_stripe_api_secret_key',
		$secret_key,
		$test_mode
	);

	return $secret_key;
}

/**
 * Returns the stored API Publishable Key.
 *
 * @since 3.0.0
 *
 * @return string
 */
function simpay_get_publishable_key() {
	$test_mode = simpay_is_test_mode();

	$setting_key = $test_mode
		? 'test_publishable_key'
		: 'live_publishable_key';

	$publishable_key = trim(
		simpay_get_setting( $setting_key, '' )
	);

	/**
	 * Filters the Stripe API publishable key.
	 *
	 * @since 3.6.6
	 *
	 * @param string $publishable_key Stripe API publishable key.
	 * @param bool   $test_mode If test mode is enabled.
	 */
	$publishable_key = apply_filters(
		'simpay_stripe_api_publishable_key',
		$publishable_key,
		$test_mode
	);

	return $publishable_key;
}

/**
 * Checks that the API keys actually exists.
 *
 * @since 3.0.0
 *
 * @return bool
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
 * Returns the Stripe Account's currency symbol (set by user).
 *
 * @since 3.0.0
 *
 * @return string
 */
function simpay_get_saved_currency_symbol() {
	return simpay_get_currency_symbol(
		simpay_get_setting( 'currency', 'USD' )
	);
}

/**
 * Returns the currency position.
 *
 * @since 3.0.0
 *
 * @return string
 */
function simpay_get_currency_position() {
	return simpay_get_setting( 'currency_position', 'left' );
}

/**
 * Returns a saved meta setting from a form.
 *
 * @param int|string $post_id Payment Form ID.
 * @param string     $setting Payment Form meta key.
 * @param string     $default Payment Form meta default.
 * @param bool       $single Return the Paymetn Form meta as a single value.
 *                           Default true.
 * @return mixed|string
 */
function simpay_get_saved_meta( $post_id, $setting, $default = '', $single = true ) {
	if ( empty( $post_id ) ) {
		return '';
	}

	// Check for custom keys array. If it doesn't exist then that means this is a brand new form.
	$custom_keys = get_post_custom_keys( $post_id );

	if ( empty( $custom_keys ) || ! in_array( $setting, $custom_keys ) ) {
		return $default;
	}

	$value = get_post_meta( $post_id, $setting, $single );

	if ( empty( $value ) && ! empty( $default ) ) {
		return $default;
	}

	return $value;
}

/**
 * Localizes the shared script with the shared script variables.
 *
 * @since 3.0.0
 */
function simpay_shared_script_variables() {

	$strings = array();

	$bools['booleans'] = array(
		'isTestMode'    => simpay_is_test_mode(),
		'isZeroDecimal' => simpay_is_zero_decimal(),
		'scriptDebug'   => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
	);

	$strings['strings'] = array(
		'currency'          => simpay_get_setting( 'currency', 'USD' ),
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
 * Returns a list of zero decimal currencies.
 *
 * @link https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
 *
 * @since 3.0.0
 *
 * @return array
 */
function simpay_get_zero_decimal_currencies() {

	return apply_filters(
		'simpay_zero_decimal_currencies',
		array(
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
		)
	);
}

/**
 * Checks if the currency is set to a zero decimal currency or not.
 *
 * @since 3.0.0
 *
 * @param string $currency Currency code.
 * @return bool
 */
function simpay_is_zero_decimal( $currency = '' ) {

	$zero_decimal_currencies = simpay_get_zero_decimal_currencies();

	if ( empty( $currency ) ) {
		$currency = simpay_get_setting( 'currency', 'USD' );
	}

	if ( array_key_exists( strtolower( $currency ), $zero_decimal_currencies ) ) {
		return true;
	}

	return false;
}

/**
 * Returns the thousands separator.
 *
 * @since 3.0.0
 *
 * @return string
 */
function simpay_get_thousand_separator() {
	$separator = 'no' === simpay_get_setting( 'separator', 'no' )
		? ','
		: '.';

	/**
	 * Filters the thousands separator.
	 *
	 * @since 3.0.0
	 *
	 * @param string $separator Thousands separator.
	 */
	$separator = apply_filters( 'simpay_thousand_separator', $separator );

	return $separator;
}

/**
 * Returns the decimal separator.
 *
 * @since 3.0.0
 *
 * @return string
 */
function simpay_get_decimal_separator() {
	$separator = 'no' === simpay_get_setting( 'separator', 'no' )
		? '.'
		: ',';

	/**
	 * Filters the decimal separator.
	 *
	 * @since 3.0.0
	 *
	 * @param string $separator Decimal separator.
	 */
	$separator = apply_filters( 'simpay_decimal_separator', $separator );

	return $separator;
}

/**
 * Returns the number of decimal places to use.
 *
 * @since 3.0.0
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
 * Returns an amount as a float.
 *
 * @since 3.0.0
 *
 * @param string|float|int $amount Amount to unformat.
 * @return float
 */
function simpay_unformat_currency( $amount ) {

	// Nothing neesd to be replaced if we are using a number already.
	if ( is_float( $amount ) || is_int( $amount ) ) {
		return abs( $amount );
	}

	// Remove thousand separator.
	$amount = str_replace( simpay_get_thousand_separator(), '', $amount );

	// Replace decimal separator with an actual decimal point to allow converting to float.
	$amount = str_replace( simpay_get_decimal_separator(), '.', $amount );

	return abs( floatval( $amount ) );
}

/**
 * Converts a non-zero decimal currency amount to cents.
 *
 * Leaves zero decimal currencies alone.
 *
 * @since 3.0.0
 *
 * @param string|float|int $amount Amount to convert.
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
 * Converts a non-zero decimal currency amount to dollars.
 *
 * Leaves zero decimal currencies alone.
 *
 * @since 3.0.0
 *
 * @param string|int $amount Amount to convert.
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
 * Get the global system-wide minimum amount.
 *
 * Stripe dictates minimum USD is 50 cents, but set to 100 cents/currency
 * units as it can vary from currency to currency.
 *
 * @since 3.0.0
 *
 * @return float
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
 * Validates a statement subscription for a charge or plan.
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

	// Trim to 22 characters max.
	$statement_descriptor = substr( $statement_descriptor, 0, 22 );

	return $statement_descriptor;
}

/**
 * Formats an amount for a specificed currency.
 *
 * @since 3.0.0
 *
 * @param string|float|int $amount Amount to unformat.
 * @param string           $currency Currency code.
 * @param bool             $show_symbol Show currency symbol. Default true.
 * @return string
 */
function simpay_format_currency( $amount, $currency = '', $show_symbol = true ) {

	if ( empty( $currency ) ) {
		$currency = simpay_get_setting( 'currency', 'USD' );
	}

	$symbol = simpay_get_currency_symbol( $currency );

	$position = simpay_get_currency_position();

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
 * Retrieves a list of currency codes and symbols.
 *
 * @since 3.8.0
 *
 * @return array
 */
function simpay_get_currencies() {
	return array(
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
		'MXN' => 'MXN',
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
	);

	/**
	 * Filters the list of available currencies.
	 *
	 * @since 3.8.0
	 *
	 * @param array $currencies List of currency symbols and names.
	 */
	$currencies = apply_filters( 'simpay_get_currencies', $currencies );

	return $currencies;
}

/**
 * Returns a specific currency symbol.
 *
 * @link https://support.stripe.com/questions/which-currencies-does-stripe-support
 *
 * @since 3.0.0
 *
 * @param string $currency Currency code.
 * @return string
 */
function simpay_get_currency_symbol( $currency = '' ) {

	if ( ! $currency ) {

		// If no currency is passed then default it to USD.
		$currency = 'USD';
	}

	$currency   = strtoupper( $currency );
	$currencies = simpay_get_currencies();

	$symbols = apply_filters( 'simpay_currency_symbols', $currencies );

	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

	return apply_filters( 'simpay_currency_symbol', $currency_symbol, $currency );
}

/**
 * Insert an array key/value pair after a certain point in an existing associative array.
 *
 * @since 3.4.0
 *
 * @param string $new_key New key to add to the list.
 * @param array  $value New array value to add to the list.
 * @param string $needle Existing key to insert after.
 * @param array  $haystack Full existing list to modify.
 * @return array
 */
function simpay_add_to_array_after( $new_key, $value, $needle, $haystack ) {
	$split = array(); // The split off portion of the array after the key we want to insert after.
	$new   = array(); // The new array will consist of the opposite of the split + the new element we want to add.

	if ( array_key_exists( $needle, $haystack ) ) {
		$offset = array_search( $needle, array_keys( $haystack ) );

		$split = array_slice( $haystack, $offset + 1 );
		$new   = array_slice( $haystack, 0, $offset + 1 );

		// Add the new element to the bottom.
		$new[ $new_key ] = $value;
	}

	return $new + $split;
}

/**
 * Generate a shipping object containing the required fields for the Stripe API.
 *
 * @since 3.6.0
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

/**
 * Returns the Stripe logo as an SVG.
 *
 * @since 3.0.0
 *
 * @return string
 */
function simpay_get_svg_icon_url() {
	return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMS4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDQ4OC4yMDEgNDg4LjIwMSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDg4LjIwMSA0ODguMjAxOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjUxMnB4IiBoZWlnaHQ9IjUxMnB4Ij4KPGc+Cgk8Zz4KCQk8cGF0aCBkPSJNMjY1LjIsMzUwLjI1MUgzMy42Yy01LjMsMC05LjYtNC4zLTkuNi05LjZ2LTE4Mi42aDQwOC41djI0YzAsNi42LDUuNCwxMiwxMiwxMnMxMi01LjQsMTItMTJ2LTg2LjUgICAgYzAtMTguNS0xNS4xLTMzLjYtMzMuNi0zMy42SDMzLjZjLTE4LjYsMC0zMy42LDE1LjEtMzMuNiwzMy42djI0NS4xYzAsMTguNSwxNS4xLDMzLjYsMzMuNiwzMy42aDIzMS43YzYuNiwwLDEyLTUuNCwxMi0xMiAgICBTMjcxLjksMzUwLjI1MSwyNjUuMiwzNTAuMjUxeiBNMzMuNiw4NS45NTFoMzg5LjNjNS4zLDAsOS42LDQuMyw5LjYsOS42djM4LjVIMjMuOXYtMzguNUMyMy45LDkwLjI1MSwyOC4zLDg1Ljk1MSwzMy42LDg1Ljk1MXoiIGZpbGw9IiNGRkZGRkYiLz4KCQk8cGF0aCBkPSJNMjQwLjIsMjQ3LjE1MWMwLTYuNi01LjQtMTItMTItMTJIODRjLTYuNiwwLTEyLDUuNC0xMiwxMnM1LjQsMTIsMTIsMTJoMTQ0LjJDMjM0LjksMjU5LjE1MSwyNDAuMiwyNTMuNzUxLDI0MC4yLDI0Ny4xNTEgICAgeiIgZmlsbD0iI0ZGRkZGRiIvPgoJCTxwYXRoIGQ9Ik04NCwyNzguMTUxYy02LjYsMC0xMiw1LjQtMTIsMTJzNS40LDEyLDEyLDEyaDU3LjdjNi42LDAsMTItNS40LDEyLTEycy01LjQtMTItMTItMTJIODR6IiBmaWxsPSIjRkZGRkZGIi8+CgkJPHBhdGggZD0iTTgyLjYsMjE1LjY1MWgxNDQuMmM2LjYsMCwxMi01LjQsMTItMTJzLTUuNC0xMi0xMi0xMkg4Mi42Yy02LjYsMC0xMiw1LjQtMTIsMTJTNzUuOSwyMTUuNjUxLDgyLjYsMjE1LjY1MXoiIGZpbGw9IiNGRkZGRkYiLz4KCQk8cGF0aCBkPSJNNDc2LjMsMjk4LjI1MWgtMTcuNnYtMjhjMC0zNC43LTI4LjMtNjMtNjMtNjNzLTYzLDI4LjMtNjMsNjN2MjhoLTE3LjZjLTYuNiwwLTEyLDUuNC0xMiwxMnYxMDRjMCw2LjYsNS40LDEyLDEyLDEyICAgIGgxNjEuMWM2LjYsMCwxMi01LjQsMTItMTJ2LTEwNEM0ODguMywzMDMuNTUxLDQ4Mi45LDI5OC4yNTEsNDc2LjMsMjk4LjI1MXogTTM1Ni43LDI3MC4xNTFjMC0yMS41LDE3LjUtMzksMzktMzlzMzksMTcuNSwzOSwzOSAgICB2MjhoLTc4VjI3MC4xNTF6IE00NjQuMyw0MDIuMTUxSDMyNy4xdi04MGgxMzcuMXY4MEg0NjQuM3oiIGZpbGw9IiNGRkZGRkYiLz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K';
}
