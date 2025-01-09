<?php
/**
 * Functions: Shared
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimplePay\Core\License;
use SimplePay\Core\Utils;
use SimplePay\Core\Forms\Default_Form;
use SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;
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

	// Ensure we have an array to work with.
	if ( ! is_array( $existing_settings ) ) {
		$existing_settings = array();
	}

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
 * Returns the current license.
 *
 * @since 4.4.4
 *
 * @return \SimplePay\Core\License\License
 */
function simpay_get_license() {
	if ( true === defined( 'SIMPLE_PAY_LICENSE_KEY' ) ) {
		$key = SIMPLE_PAY_LICENSE_KEY; // @phpstan-ignore-line
	} else {
		$key = get_option( 'simpay_license_key', '' );
	}

	/** @var string $key */
	$key = trim( $key );

	return new License\License( $key );
}

/**
 * Check the user's license to see if subscriptions are enabled or not
 *
 * @since 3.0.0
 * @since 4.4.4 Deprecated. Use simpay_get_license()->is_subscriptions_enabled() instead.
 *
 * @return bool
 */
function simpay_subscriptions_enabled() {
	return simpay_get_license()->is_subscriptions_enabled();
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
 * @since 3.0.0
 *
 * @param string|int|WP_Post $form_id Payment Form to retrieve.
 * @return false|\SimplePay\Core\Abstracts\Form Payment Form instance or false if not found.
 */
function simpay_get_form( $form_id ) {
	if ( $form_id instanceof WP_Post ) {
		$form_id = $form_id->ID;
	}

	$post_type = get_post_type( $form_id );

	if ( 'simple-pay' !== $post_type ) {
		return false;
	}

	/** This filter is documented in includes/core/shortcodes.php */
	$form = apply_filters( 'simpay_form_view', '', $form_id );

	if ( empty( $form ) ) {
		$form = new Default_Form( $form_id );
	}

	// Associated WP_Post cannot be found, invalid.
	if ( 0 === $form->id || null === $form->post ) {
		return false;
	}

	// Associated WP_Post is trashed, invalid.
	if ( 'trash' === $form->post->post_status ) {
		return false;
	}

	// Associated WP_Post is not published, and the current user cannot manage options, invalid.
	if ( 'publish' !== $form->post->post_status && ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	// Migrate legacy amounts/subscription information if required.
	$migrations = Utils\get_collection( 'migrations' );
	$migration  = $migrations->get_item( 'prices-api' );

	if ( false === $migration->is_complete( $form ) ) {
		$migration->run( $form );
	}

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
 * Determines if "livemode" is currently enabled globally.
 *
 * @since 4.3.0
 *
 * @return bool
 */
function simpay_is_livemode() {
	return ! simpay_is_test_mode();
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
 * @param mixed      $default Payment Form meta default.
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
 *
 * @return array<mixed> Array of shared script variables.
 */
function simpay_shared_script_variables() {

	$strings = array();

	$bools['booleans'] = array(
		'isTestMode'    => simpay_is_test_mode(),
		'isZeroDecimal' => simpay_is_zero_decimal(),
		'scriptDebug'   => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
	);

	// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
	$strings['strings'] = array(
		'currency'          => simpay_get_setting( 'currency', 'USD' ),
		'currencySymbol'    => html_entity_decode(
			simpay_get_saved_currency_symbol()
		),
		'currencyPosition'  => simpay_get_currency_position(),
		'decimalSeparator'  => simpay_get_decimal_separator(),
		'thousandSeparator' => simpay_get_thousand_separator(),
		'ajaxurl'           => admin_url( 'admin-ajax.php' ),
		/* translators: %s: Minimum payment amount. */
		'customAmountLabel' => esc_html__( 'starting at %s', 'stripe' ),
		'recurringIntervals' => simpay_get_recurring_intervals(),
		/* translators: %1$s Recurring amount. %2$s Recurring interval count. %3$s Recurring interval. */
		'recurringIntervalDisplay' => esc_html_x(
			'%1$s every %2$s %3$s',
			'recurring interval',
			'stripe'
		),
		/* translators: %1$s Recurring amount. %2$s Recurring interval count -- not output when 1. %3$s Recurring interval. %4$s Limited discount interval count. %5$s Recurring amount without discount. */
		'recurringIntervalDisplayLimitedDiscount' => esc_html_x(
			'%1$s every %2$s %3$s for %4$s months then %5$s',
			'recurring interval',
			'stripe'
		),
		/* translators: %1$s Recurring amount. %2$s Recurring interval count -- not output when 1. %3$s Recurring interval. %4$s Limited discount interval count. %5$s Recurring amount without discount. */
		'recurringIntervalDisplayAutomaticTaxDiscount' => esc_html_x(
			'%1$s every %2$s %3$s until coupon expires',
			'recurring interval with automatic tax',
			'stripe'
		),
		/* translators: %1$s Invoice limit. %2$s Recurring interval count -- not output when 1. %3$s Recurring interval. %4$s Recurring amount limit */
		'recurringIntervalDisplayInvoiceLimit' => esc_html_x(
			'%1$d payments of %2$s every %3$s %4$s',
			'recurring interval with invoice limit',
			'stripe'
		),
		/* translators: %1$s Invoice limit. %2$s Recurring interval count -- not output when 1. %3$s Recurring interval. %4$s Recurring amount */
		'recurringIntervalDisplayInvoiceLimitWithCoupon' => esc_html_x(
			'%1$d payments of %2$s (for the duration of the coupon) every %3$s %4$s',
			'recurring interval',
			'stripe'
		),
		'addressRequired' => esc_html__(
			'Enter address to calculate',
			'stripe'
		),
		'addressInvalid' => esc_html__(
			'Please enter a valid address',
			'stripe'
		),
	);
	// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

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

	return $final;
}

/**
 * Returns a list of recurring billing intervals.
 *
 * @since 4.1.0
 *
 * @return array[] {
 *   @var string Singular interval.
 *   @var string Plural interval.
 * }
 */
function simpay_get_recurring_intervals() {
	return array(
		'day'   => array(
			esc_html_x( 'day', 'recurring interval', 'stripe' ),
			esc_html_x( 'days', 'recurring interval', 'stripe' ),
		),
		'week'  => array(
			esc_html_x( 'week', 'recurring interval', 'stripe' ),
			esc_html_x( 'weeks', 'recurring interval', 'stripe' ),
		),
		'month' => array(
			esc_html_x( 'month', 'recurring interval', 'stripe' ),
			esc_html_x( 'months', 'recurring interval', 'stripe' ),
		),
		'year'  => array(
			esc_html_x( 'year', 'recurring interval', 'stripe' ),
			esc_html_x( 'years', 'recurring interval', 'stripe' ),
		),
	);
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
 * @since 4.1.0 Accepts a specific currency.
 *
 * @param string $currency Optional. Currency code. Default global currency.
 * @return int
 */
function simpay_get_decimal_places( $currency = '' ) {

	$decimal_places = 2;

	if ( empty( $currency ) ) {
		$currency = strtolower( simpay_get_setting( 'currency', 'USD' ) );
	}

	if ( simpay_is_zero_decimal( $currency ) ) {
		$decimal_places = 0;
	}

	/**
	 * Filters the number of decimal places to use.
	 *
	 * @since 3.0.0
	 * @since 4.1.0 Accepts a specific currency.
	 *
	 * @param int    $decimal_places Number of decimal places.
	 * @param string $currency Currency code.
	 */
	$decimal_places = apply_filters(
		'simpay_decimal_places',
		$decimal_places,
		$currency
	);

	return intval( $decimal_places );
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
 * Retrieves a list of currency codes and symbols.
 *
 * @since 3.8.0
 *
 * @return array
 */
function simpay_get_currencies() {
	$currencies = array(
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
		'BIF' => 'Fr',
		'BMD' => '&#36;',
		'BND' => '&#36;',
		'BOB' => 'Bs.',
		'BRL' => '&#82;&#36;',
		'BSD' => '&#36;',
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
		'CVE' => '&#36;',
		'CZK' => '&#75;&#269;',
		'DJF' => 'Fr',
		'DKK' => 'DKK',
		'DOP' => 'RD&#36;',
		'DZD' => '&#x62f;.&#x62c;',
		'EGP' => 'EGP',
		'ETB' => 'Br',
		'EUR' => '&euro;',
		'FJD' => '&#36;',
		'FKP' => '&pound;',
		'GBP' => '&pound;',
		'GEL' => '&#x10da;',
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
		'INR' => '&#8377;',
		'ISK' => 'Kr.',
		'JMD' => '&#36;',
		'JPY' => '&yen;',
		'KES' => 'KSh',
		'KGS' => '&#x43b;&#x432;',
		'KHR' => '&#x17db;',
		'KMF' => 'Fr',
		'KRW' => '&#8361;',
		'KYD' => '&#36;',
		'KZT' => 'KZT',
		'LAK' => '&#8365;',
		'LBP' => '&#x644;.&#x644;',
		'LKR' => '&#xdbb;&#xdd4;',
		'LRD' => '&#36;',
		'LSL' => 'L',
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
		'PAB' => 'B/.',
		'PEN' => 'S/.',
		'PGK' => 'K',
		'PHP' => '&#8369;',
		'PKR' => '&#8360;',
		'PLN' => '&#122;&#322;',
		'PYG' => '&#8370;',
		'QAR' => '&#x631;.&#x642;',
		'RON' => 'lei',
		'RSD' => '&#x434;&#x438;&#x43d;.',
		'RUB' => '&#8381;',
		'RWF' => 'Fr',
		'SAR' => '&#x631;.&#x633;',
		'SBD' => '&#36;',
		'SCR' => '&#x20a8;',
		'SEK' => '&#107;&#114;',
		'SGD' => 'S&#36;',
		'SHP' => '&pound;',
		'SLL' => 'Le',
		'SOS' => 'Sh',
		'SRD' => '&#36;',
		'STD' => 'Db',
		'SZL' => 'L',
		'THB' => '&#3647;',
		'TJS' => '&#x405;&#x41c;',
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
		$offset = array_search( $needle, array_keys( $haystack ), true );

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
	return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTY5IiBoZWlnaHQ9IjExMyIgdmlld0JveD0iMCAwIDE2OSAxMTMiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNOSAyOVY5MEg4NVY5OUg2QzIuNjg2MjkgOTkgMCA5Ni4zMTM3IDAgOTNWNkMwIDIuNjg2MjkgMi42ODYyOSAwIDYgMEgxNDBDMTQzLjMxNCAwIDE0NiAyLjY4NjI5IDE0NiA2VjlWMjlIMTM3SDg1SDlaIiBmaWxsPSIjYTdhYWFkIi8+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNMjMgNThDMjMgNTUuNzkwOSAyNC43OTA5IDU0IDI3IDU0SDY5QzcxLjIwOTEgNTQgNzMgNTUuNzkwOSA3MyA1OEM3MyA2MC4yMDkxIDcxLjIwOTEgNjIgNjkgNjJIMjdDMjQuNzkwOSA2MiAyMyA2MC4yMDkxIDIzIDU4Wk0yMyA3MkMyMyA2OS43OTA5IDI0Ljc5MDkgNjggMjcgNjhINTRDNTYuMjA5MSA2OCA1OCA2OS43OTA5IDU4IDcyQzU4IDc0LjIwOTEgNTYuMjA5MSA3NiA1NCA3NkgyN0MyNC43OTA5IDc2IDIzIDc0LjIwOTEgMjMgNzJaIiBmaWxsPSIjYTdhYWFkIi8+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNMTEwLjg4NiA3Ni4xMzQ3VjEwNC45ODZIMTYwLjUzNlY3Ni4xMzQ3SDExMC44ODZaTTEwOS4yMzEgNjguMTIwNUMxMDUuNTc1IDY4LjEyMDUgMTAyLjYxMSA3MC45OTEgMTAyLjYxMSA3NC41MzE5VjEwNi41ODlDMTAyLjYxMSAxMTAuMTMgMTA1LjU3NSAxMTMgMTA5LjIzMSAxMTNIMTYyLjE5MUMxNjUuODQ4IDExMyAxNjguODEyIDExMC4xMyAxNjguODEyIDEwNi41ODlWNzQuNTMxOUMxNjguODEyIDcwLjk5MSAxNjUuODQ4IDY4LjEyMDUgMTYyLjE5MSA2OC4xMjA1SDEwOS4yMzFaIiBmaWxsPSIjYTdhYWFkIi8+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNMTQ4LjEyNCA2OS40NTYyVjYyLjUxMDZDMTQ4LjEyNCA1Ni4zMTQgMTQyLjkzNyA1MS4yOTA3IDEzNi41MzkgNTEuMjkwN0MxMzAuMTQgNTEuMjkwNyAxMjQuOTU0IDU2LjMxNCAxMjQuOTU0IDYyLjUxMDZWNjkuNDU2MkgxMTUuMDIzVjYyLjUxMDZDMTE1LjAyMyA1MS4wMDI3IDEyNC42NTYgNDEuNjczNyAxMzYuNTM5IDQxLjY3MzdDMTQ4LjQyMSA0MS42NzM3IDE1OC4wNTQgNTEuMDAyNyAxNTguMDU0IDYyLjUxMDZWNjkuNDU2MkgxNDguMTI0WiIgZmlsbD0iI2E3YWFhZCIvPgo8L3N2Zz4K';
}

/**
 * Returns the length for a 2 minute nonce lifespan.
 *
 * @since 4.2.0
 *
 * @return int 120.
 */
function simpay_nonce_life_2_min() {
	return MINUTE_IN_SECONDS * 2;
}

/**
 * Returns the length for a 2 hour nonce lifespan.
 *
 * @since 4.2.0
 *
 * @return int 7200
 */
function simpay_nonce_life_2_hour() {
	return HOUR_IN_SECONDS * 2;
}

/**
 * Shims wp_timezone_string() for WordPress < 5.3.0
 *
 * @since 4.3.0
 *
 * @return string
 */
function simpay_wp_timezone_string() {
	$timezone_string = get_option( 'timezone_string' );

	if ( $timezone_string ) {
		return $timezone_string;
	}

	$offset  = (float) get_option( 'gmt_offset' );
	$hours   = (int) $offset;
	$minutes = ( $offset - $hours );

	$sign      = ( $offset < 0 ) ? '-' : '+';
	$abs_hour  = abs( $hours );
	$abs_mins  = abs( $minutes * 60 );
	$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

	return $tz_offset;
}

/**
 * Determine if the base country supports Payment Request Button
 *
 * @since 3.5.0
 *
 * @return bool
 */
function simpay_can_use_payment_request_button() {
	$country = strtoupper( simpay_get_setting( 'account_country', 'US' ) );

	if ( ! $country ) {
		$country = 'US';
	}

	$countries = array(
		'AE',
		'AT',
		'AU',
		'BE',
		'BG',
		'BR',
		'CA',
		'CH',
		'CI',
		'CR',
		'CY',
		'CZ',
		'DE',
		'DK',
		'DO',
		'EE',
		'ES',
		'FI',
		'FR',
		'GB',
		'GI',
		'GR',
		'GT',
		'HK',
		'HU',
		'ID',
		'IE',
		'IN',
		'IT',
		'JP',
		'LI',
		'LT',
		'LU',
		'LV',
		'MT',
		'MX',
		'MY',
		'NL',
		'NO',
		'NZ',
		'PE',
		'PH',
		'PL',
		'PT',
		'RO',
		'SE',
		'SG',
		'SI',
		'SK',
		'SN',
		'TH',
		'TT',
		'US',
		'UY',
	);

	$can_use = in_array( $country, $countries, true );

	/**
	 * Filter Payment Request Button availibility.
	 *
	 * @since 3.5.1
	 *
	 * @param bool $can_use   Can the button be used?
	 * @param string $country Current country.
	 */
	$can_use = apply_filters( 'simpay_can_use_payment_request_button', $can_use, $country );

	return $can_use;
}

/**
 * Get the stored date format for the datepicker
 *
 * @return string
 */
function simpay_get_date_format() {
	return simpay_get_setting( 'date_format', '' );
}

/**
 * Returns a payment form setting value retrieved from a template or the database.
 *
 * This should only be used when editing a payment form in the admin.
 * Use `simpay_get_saved_meta()` for retrieving the value from the database.
 *
 * @since 4.4.3
 *
 * @param int        $form_id Payment form ID.
 * @param string     $setting Payment form setting.
 * @param mixed      $default Payment form setting default.
 * @param null|array $template Optional. Payment form template ID. If not set the value will be
 *                             returned from the database. Default null.
 */
function simpay_get_payment_form_setting(
	$form_id,
	$setting,
	$default,
	$template = null
) {
	// Use a template.
	if ( null !== $template ) {
		switch ( $setting ) {
			// Top level attributes.
			case 'title':
			case 'description':
			case 'type':
			case 'fields':
			case 'prices':
				$setting = $template['data'][ $setting ];
				break;

			// Format simplified payment methods.
			case 'payment_methods':
				$methods = $template['data']['payment_methods'];
				$setting = array();

				foreach ( $methods as $method ) {
					if ( is_array( $method ) ) {
						$setting[ $method['id'] ] = $method;
					} else {
						$setting[ $method ] = array(
							'id' => $method,
						);
					}
				}

				break;

			// Allow string dates to be converted to dates.
			case '_schedule_end_gmt':
			case '_schedule_start_gmt':
				$setting = isset( $template['data']['extra'][ $setting ] )
					? $template['data']['extra'][ $setting ]
					: $default;

				$setting = strtotime( $setting );

				break;

			// Lower level settings.
			default:
				$setting = isset( $template['data']['extra'][ $setting ] )
					? $template['data']['extra'][ $setting ]
					: $default;
		}

		// Pull the value from the database.
	} else {
		$meta_map = array(
			'title'           => '_company_name',
			'description'     => '_item_description',
			'type'            => '_form_display_type',
			'payment_methods' => '_payment_methods',
			'fields'          => '_custom_fields',
			'tax_status'      => '_tax_status',
		);

		// Pull the value from the database.
		$setting_db = simpay_get_saved_meta(
			$form_id,
			isset( $meta_map[ $setting ] ) ? $meta_map[ $setting ] : $setting,
			$default
		);

		switch ( $setting ) {
			// Flatten custom fields.
			case 'fields':
				// If pulling from the database, flatten.
				if ( $setting_db !== $default ) {
					$setting = Edit_Form\get_custom_fields_flat( $setting_db );

					// Passed default is already flattened.
				} else {
					$setting = $default;
				}

				break;

			// Pull the payment methods for the correct context.
			case 'payment_methods':
				$type = simpay_get_payment_form_setting(
					$form_id,
					'type',
					'stripe_checkout',
					$template
				);

				$context = null === $type || 'stripe_checkout' === $type
					? 'stripe-checkout'
					: 'stripe-elements';

				$setting = isset( $setting_db[ $context ] )
					? $setting_db[ $context ]
					: array(
						'card' => array(
							'id' => 'card',
						),
					);

				break;

				// Use the value directly.
			default:
				$setting = $setting_db;
		}
	}

	return $setting;
}

/**
 * Returns a list of category slugs and nicenames.
 *
 * @since 4.6.5
 *
 * @return array<string, string>
 */
function __unstable_simpay_get_form_template_categories() {
	$categories = array(
		'business-operations'    => __( 'Business Operations', 'stripe' ),
		'donations'              => __( 'Donations', 'stripe' ),
		'fundraising'            => __( 'Fundraising', 'stripe' ),
		'products-services'      => __( 'Products & Services', 'stripe' ),
		'registrations'          => __( 'Registrations', 'stripe' ),
		'features-functionality' => __( 'Features / Functionality', 'stripe' ),
	);

	if ( __unstable_simpay_has_new_form_templates() ) {
		$categories = array_merge(
			array(
				'new' => __( 'Recently Added', 'stripe' ),
			),
			$categories
		);
	}

	return $categories;
}

/**
 * Returns the nicename of a payment form template category.
 *
 * @since 4.4.4
 *
 * @param string $category_slug Category slug.
 * @return string
 */
function __unstable_simpay_get_form_template_category_name( $category_slug ) {
	$categories = __unstable_simpay_get_form_template_categories();

	return isset( $categories[ $category_slug ] )
		? $categories[ $category_slug ]
		: $category_slug;
}

/**
 * Returns a list of payment form templates.
 *
 * @since 4.4.3
 *
 * @return array<mixed>
 */
function __unstable_simpay_get_payment_form_templates() {
	static $templates = array();

	if ( ! empty( $templates ) ) {
		return $templates;
	}

	$template_files = glob( SIMPLE_PAY_DIR . '/data/templates/*.json' );

	/**
	 * Filters the list of payment form templates.
	 *
	 * @since [--next--]
	 *
	 * @param array<string> $template_files List of template file paths.
	 */
	$template_files = apply_filters(
		'__unstable__simpay_payment_form_templates',
		$template_files
	);

	if ( false === $template_files ) {
		return array();
	}

	$currency = strtolower( simpay_get_setting( 'currency', 'USD' ) );
	$has_new  = false;

	foreach ( $template_files as $template_file ) {
		$data = json_decode( file_get_contents( $template_file ), true );

		// Skip invalid templates.
		if ( ! is_array( $data ) ) {
			continue;
		}

		if (
			isset( $data['created_at'] ) &&
			strtotime( $data['created_at'] ) > strtotime( '-4 weeks' )
		) {
			$has_new = true;
		}

		// Adjust licenses if needed.
		// Templates that utilize "enhanced" subscription functionality should only continue to be available
		// for "Plus" license holders if they have been grandfathered in.
		$enhanced_subscription_functionality = array(
			'product-installment-plan-form',
			'recurring-service-trial-period-form',
			'recurring-service-setup-fee-form',
		);

		if (
			in_array( $data['slug'], $enhanced_subscription_functionality, true ) &&
			false === simpay_get_license()->is_enhanced_subscriptions_enabled()
		) {
			$plus = array_search( 'plus', $data['license'], true );

			if ( false !== $plus ) {
				unset( $data['license'][ $plus ] );
			}

			$data['license'] = array_values( $data['license'] );
		}

		// Use the store currency if one is not set.
		foreach ( $data['data']['prices'] as $k => $price ) {
			// Top level amounts.
			if ( ! isset( $price['currency'] ) ) {
				$data['data']['prices'][ $k ]['currency'] = $currency;
			}

			// Line items.
			if ( isset( $data['data']['prices'][ $k ]['line_items'] ) ) {
				foreach ( $data['data']['prices'][ $k ]['line_items'] as $line_k => $line_item ) {
					if ( ! isset( $line_item['currency'] ) ) {
						$data['data']['prices'][ $k ]['line_items'][ $line_k ]['currency'] = $currency;
					}
				}
			}
		}

		// Adjust settings for UPE.
		if ( simpay_is_upe() ) {
			$data['data']['fields'] = array_map(
				function ( $field ) {
					// Remove the card field label.
					if ( 'card' === $field['type'] ) {
						$field['label'] = '';
					}

					// Enable Link in the email field.
					if ( 'email' === $field['type'] ) {
						$field['link'] = array(
							'enabled' => 'yes',
						);
					}

					return $field;
				},
				$data['data']['fields']
			);
		}

		$templates[] = $data;
	}

	$is_form_templates_page = (
		isset( $_GET['page'] ) &&
		'simpay_form_templates' === sanitize_text_field( $_GET['page'] )
	);

	if ( $has_new && $is_form_templates_page ) {
		foreach ( $templates as $key => $data ) {
			if ( isset( $data['categories'] ) ) {
				$templates[ $key ]['categories'][] = 'new';
			}
		}

		usort(
			$templates,
			function ( $a, $b ) {
				return $a['created_at'] < $b['created_at'];
			}
		);
	}

	return $templates;
}

/**
 * Attempts to locate a payment form template via the `template` URL parameter.
 *
 * @since 4.4.3
 *
 * @return null|array<mixed>
 */
function __unstable_simpay_get_payment_form_template_from_url() {
	$id = isset( $_GET['simpay-template'] )
		? sanitize_text_field( $_GET['simpay-template'] )
		: null;

	return __unstable_simpay_get_payment_form_template( $id );
}

/**
 * Returns a payment form template for a given an ID.
 *
 * @since 4.4.3
 *
 * @param string $id Template ID.
 * @return null|array<mixed> Template data, or null if not found.
 */
function __unstable_simpay_get_payment_form_template( $id ) {
	$templates = __unstable_simpay_get_payment_form_templates();
	$template  = wp_list_filter(
		$templates,
		array(
			'id' => $id,
		)
	);

	return empty( $template ) ? null : current( $template );
}

/**
 * Determines if we have newly added templates available.
 *
 * @since 4.6.5
 *
 * @return bool True if a template was added within the last two weeks.
 */
function __unstable_simpay_has_new_form_templates() {
	$templates = __unstable_simpay_get_payment_form_templates();

	$new = array_filter(
		$templates,
		function ( $template_data ) {
			return (
				isset( $template_data['created_at'] ) &&
				strtotime( $template_data['created_at'] ) > strtotime( '-4 weeks' )
			);
		}
	);

	return ! empty( $new );
}

/**
 * Returns an list of payment form titles keyed by ID.
 *
 * @since 4.4.3
 *
 * @return array<int, string>
 */
function simpay_get_form_list_options() {
	static $options = array();

	if ( empty( $options ) ) {
		$forms = get_posts(
			array(
				'post_type'      => 'simple-pay',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $forms as $form_id ) {
			$options[ $form_id ] = get_the_title( $form_id );
		}
	}

	return $options;
}

/**
 * Appends UTM parameters to a given URL.
 *
 * @since 3.0.0
 * @since 4.4.0 Removed $raw parameter. Update utm_source to WordPress.
 *              Move utm_content to utm_medium. Add support for dynamic utm_content.
 *
 * @param string $base_url Base URL.
 * @param string $utm_medium utm_medium parameter.
 * @param string $utm_content Optional. utm_content parameter.
 * @return string $url Full Google Analytics campaign URL.
 */
function simpay_ga_url( $base_url, $utm_medium, $utm_content = false ) {
	/**
	 * Filters the UTM campaign for generated links.
	 *
	 * @since 3.0.0
	 *
	 * @param string $utm_campaign
	 */
	$utm_campaign = apply_filters( 'simpay_utm_campaign', 'lite-plugin' );

	$args = array(
		'utm_source'   => 'WordPress',
		'utm_campaign' => $utm_campaign,
		'utm_medium'   => $utm_medium,
	);

	if ( ! empty( $utm_content ) ) {
		$args['utm_content'] = $utm_content;
	}

	return esc_url( add_query_arg( $args, $base_url ) );
}

/**
 * URL for upgrading to Pro (or another Pro licecnse).
 *
 * @since 3.0.0
 *
 * @param string $utm_medium utm_medium parameter.
 * @param string $utm_content Optional. utm_content parameter.
 * @return string
 */
function simpay_pro_upgrade_url( $utm_medium, $utm_content = '' ) {
	return apply_filters(
		'simpay_upgrade_link',
		simpay_ga_url(
			'https://wpsimplepay.com/lite-vs-pro/',
			$utm_medium,
			$utm_content
		),
		$utm_medium,
		$utm_content
	);
}

/**
 * Determines if the current user can install plugins in the current environment.
 *
 * @since 4.5.2
 *
 * @return bool
 */
function simpay_can_install_plugins() {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return false;
	}

	if ( ! wp_is_file_mod_allowed( 'simpay_can_install' ) ) {
		return false;
	}

	return true;
}

/**
 * Determines if the current URL is considered a development environment.
 *
 * @since 4.5.2
 *
 * @return bool
 */
function simpay_is_dev_url( $url = '' ) {
	// wp_get_environment_type was added in WordPress 5.5.
	if ( empty( $url ) && function_exists( 'wp_get_environment_type' ) ) {
		$environment = wp_get_environment_type();

		return in_array( $environment, array( 'local', 'development' ), true );
	}

	$is_local_url = false;

	if ( empty( $url ) ) {
		$url = network_site_url( '/' );
	}

	// Trim it up.
	$url = strtolower( trim( $url ) );

	// Need to get the host...so let's add the scheme so we can use parse_url.
	if (
		false === strpos( $url, 'http://' ) &&
		false === strpos( $url, 'https://' )
	) {
		$url = set_url_scheme( $url, 'http' );
	}

	$url_parts = parse_url( $url );
	$host      = ! empty( $url_parts['host'] ) ? $url_parts['host'] : false;

	if ( ! empty( $url ) && ! empty( $host ) ) {
		if ( false !== ip2long( $host ) ) {
			if ( ! filter_var(
				$host,
				FILTER_VALIDATE_IP,
				FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
			) ) {
				$is_local_url = true;
			}
		} elseif ( 'localhost' === $host ) {
			$is_local_url = true;
		}

		$tlds_to_check = array(
			'.local',
			':8888',
			':8080',
			':8081',
			'.invalid',
			'.example',
			'.test',
		);

		foreach ( $tlds_to_check as $tld ) {
			if ( false !== strpos( $host, $tld ) ) {
				$is_local_url = true;
				break;
			}
		}

		if ( substr_count( $host, '.' ) > 1 ) {
			$subdomains_to_check = array(
				'dev.',
				'*.staging.',
				'beta.',
				'test.',
			);

			foreach ( $subdomains_to_check as $subdomain ) {
				$subdomain = str_replace( '.', '(.)', $subdomain );
				$subdomain = str_replace( array( '*', '(.)' ), '(.*)', $subdomain );

				if ( preg_match( '/^(' . $subdomain . ')/', $host ) ) {
					$is_local_url = true;
					break;
				}
			}
		}
	}

	return $is_local_url;
}

/**
 * Get the max length for metadata fields
 *
 * @since 3.0.0
 *
 * @return int
 */
function simpay_metadata_title_length() {
	return 40;
}

/**
 * Get the max length for the metadata description
 *
 * @since 3.0.0
 *
 * @return int
 */
function simpay_metadata_description_length() {
	return 500;
}

/**
 * Handle metadata truncation using calls to other DRY functions
 *
 * @since 3.0.0
 *
 * @param string $type Metadata type.
 * @param string $value Metadata value.
 * @return bool|string
 */
function simpay_truncate_metadata( $type, $value ) {

	switch ( $type ) {
		case 'title':
			return substr( $value, 0, simpay_metadata_title_length() );
		case 'description':
			return substr( $value, 0, simpay_metadata_description_length() );
		default:
			return $value;
	}
}

/**
 * Determines if the Universal Payment Element should be used.
 *
 * Currently this is only for all of Lite, new Pro installs, or manually enabled.
 *
 * @since 4.7.0
 *
 * @return bool
 */
function simpay_is_upe() {
	$is_lite = simpay_get_license()->is_lite();

	$is_upe = 'yes' === simpay_get_setting(
		'is_upe',
		$is_lite ? 'yes' : 'no'
	);

	/**
	 * Filters whether the Universal Payment Element should be used.
	 *
	 * @since 4.7.0
	 *
	 * @param bool $is_upe Whether the Universal Payment Element should be used.
	 */
	return apply_filters( 'simpay_is_upe', $is_upe );
}

/**
 * Get the separator to use for fields that list multiple values
 * Affected Custom Fields: Dropdown values/amounts/quantities, radio values/amounts/quantities
 */
function simpay_list_separator() {
	return apply_filters( 'simpay_list_separator', ',' );
}

/**
 * Link with HTML to docs site article & GA campaign values.
 *
 * @since 3.0.0
 * @since 4.4.0 Rename $ga_content to $utm_medium to work with simpay_ga_url().
 * @since 4.7.10 Available globally (not just admin).
 *
 * @param string $text Link text.
 * @param string $slug Link slug.
 * @param string $utm_medium utm_medium link parameter.
 * @param bool   $plain If the link should have an icon. Default false.
 * @return string
 */
function simpay_docs_link( $text, $slug, $utm_medium, $plain = false ) {

	// Articles on docs site currently require a base slug themselves.
	$base_url = 'https://wpsimplepay.com/doc/';

	// Ensure ending slash is included for consistency.
	$url = trailingslashit( $base_url . $slug );

	// If $plain is true we want to return ONLY the link, otherwise return the full HTML.
	// Add GA campaign params in both cases.
	if ( $plain ) {

		return simpay_ga_url( $url, $utm_medium, $text );

	} else {

		$html  = '';
		$html .= '<div class="simpay-docs-link-wrap">';
		$html .= '<a href="' . simpay_ga_url( $url, $utm_medium, $text ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $text );
		$html .= '<span class="dashicons dashicons-editor-help"></span>';
		$html .= '</a>';
		$html .= '</div>';

		return $html;
	}
}

/**
 * Get the current date in the WordPress timezone.
 *
 * @since 4.11.0
 *
 * @param string $timestamp unix timestamp.
 * @param string $format Date format. Default is the WordPress date format.
 * @return string
 */
function simpay_format_date( $timestamp, $format = '' ) {
	if ( '' === $format ) {
		$format = get_option( 'date_format' );
	}
	return wp_date( $format, $timestamp );
}
