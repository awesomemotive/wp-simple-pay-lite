<?php
/**
 * Internationalization: Stripe
 *
 * @package SimplePay\Core\i18n
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

namespace SimplePay\Core\i18n;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns Stripe's supported countries.
 *
 * @since 3.9.0
 *
 * @return array
 */
function get_stripe_countries() {
	$countries = array(
		'AU' => __( 'Australia', 'stripe' ),
		'AT' => __( 'Austria', 'stripe' ),
		'BE' => __( 'Belgium', 'stripe' ),
		'BG' => __( 'Bulgaria', 'stripe' ),
		'CA' => __( 'Canada', 'stripe' ),
		'CY' => __( 'Cyprus', 'stripe' ),
		'CZ' => __( 'Czech Republic', 'stripe' ),
		'DK' => __( 'Denmark', 'stripe' ),
		'EE' => __( 'Estonia', 'stripe' ),
		'FI' => __( 'Finland', 'stripe' ),
		'FR' => __( 'France', 'stripe' ),
		'DE' => __( 'Germany', 'stripe' ),
		'GR' => __( 'Greece', 'stripe' ),
		'HK' => __( 'Hong Kong', 'stripe' ),
		'IE' => __( 'Ireland', 'stripe' ),
		'IT' => __( 'Italy', 'stripe' ),
		'JP' => __( 'Japan', 'stripe' ),
		'LV' => __( 'Latvia', 'stripe' ),
		'LT' => __( 'Lithuania', 'stripe' ),
		'LU' => __( 'Luxembourg', 'stripe' ),
		'MY' => __( 'Malaysia', 'stripe' ),
		'MT' => __( 'Malta', 'stripe' ),
		'MX' => __( 'Mexico', 'stripe' ),
		'NL' => __( 'Netherlands', 'stripe' ),
		'NZ' => __( 'New Zealand', 'stripe' ),
		'NO' => __( 'Norway', 'stripe' ),
		'PL' => __( 'Poland', 'stripe' ),
		'PT' => __( 'Portugal', 'stripe' ),
		'RO' => __( 'Romania', 'stripe' ),
		'SG' => __( 'Singapore', 'stripe' ),
		'SK' => __( 'Slovakia', 'stripe' ),
		'SI' => __( 'Slovenia', 'stripe' ),
		'ES' => __( 'Spain', 'stripe' ),
		'SE' => __( 'Sweden', 'stripe' ),
		'CH' => __( 'Switzerland', 'stripe' ),
		'GB' => __( 'United Kingdom', 'stripe' ),
		'US' => __( 'United States', 'stripe' ),
	);

	/**
	 * Filters the countries supported by Stripe.
	 *
	 * @since 3.9.0
	 *
	 * @param array $countries Country list, keyed by country code.
	 */
	$countries = apply_filters( 'simpay_get_stripe_countries', $countries );

	return $countries;
}

/**
 * Returns Stripe Checkout's supported locales.
 *
 * @since 3.9.0
 *
 * @return array
 */
function get_stripe_checkout_locales() {
	return array(
		'auto' => __( 'Auto-detect', 'stripe' ),
		'cs'   => __( 'Czech (cs)', 'stripe' ),
		'da'   => __( 'Danish (da)', 'stripe' ),
		'de'   => __( 'German (de)', 'stripe' ),
		'el'   => __( 'Greek (el)', 'stripe' ),
		'et'   => __( 'Estonian (et)', 'stripe' ),
		'en'   => __( 'English (en)', 'stripe' ),
		'es'   => __( 'Spanish (es)', 'stripe' ),
		'fi'   => __( 'Finnish (fi)', 'stripe' ),
		'fr'   => __( 'French (fr)', 'stripe' ),
		'hu'   => __( 'Hungarian (hu)', 'stripe' ),
		'it'   => __( 'Italian (it)', 'stripe' ),
		'ja'   => __( 'Japanese (ja)', 'stripe' ),
		'lt'   => __( 'Lithuanian (lt)', 'stripe' ),
		'lv'   => __( 'Latvian (lv)', 'stripe' ),
		'ms'   => __( 'Malay (ms)', 'stripe' ),
		'mt'   => __( 'Maltese (mt)', 'stripe' ),
		'nb'   => __( 'Norwegian Bokmål (nb)', 'stripe' ),
		'nl'   => __( 'Dutch (nl)', 'stripe' ),
		'pl'   => __( 'Polish (pl)', 'stripe' ),
		'pt'   => __( 'Portuguese (pt)', 'stripe' ),
		'ro'   => __( 'Romanian (ro)', 'stripe' ),
		'ru'   => __( 'Russian (ru)', 'stripe' ),
		'sk'   => __( 'Slovak (sk)', 'stripe' ),
		'sl'   => __( 'Slovenian (sl)', 'stripe' ),
		'sv'   => __( 'Swedish (sv)', 'stripe' ),
		'tk'   => __( 'Turkish (tk)', 'stripe' ),
		'zh'   => __( 'Chinese Simplified (zh)', 'stripe' ),
	);
}

/**
 * Returns Stripe Element's supported locales.
 *
 * @since 3.9.0
 *
 * @return array
 */
function get_stripe_elements_locales() {
	return array(
		'auto' => __( 'Auto-detect', 'stripe' ),
		'ar'   => __( 'Arabic (ar)', 'stripe' ),
		'bg'   => __( 'Bulgarian (bg)', 'stripe' ),
		'cs'   => __( 'Czech (cs)', 'stripe' ),
		'da'   => __( 'Danish (da)', 'stripe' ),
		'de'   => __( 'German (de)', 'stripe' ),
		'el'   => __( 'Greek (el)', 'stripe' ),
		'et'   => __( 'Estonian (et)', 'stripe' ),
		'en'   => __( 'English (en)', 'stripe' ),
		'es'   => __( 'Spanish (es)', 'stripe' ),
		'fi'   => __( 'Finnish (fi)', 'stripe' ),
		'fr'   => __( 'French (fr)', 'stripe' ),
		'he'   => __( 'Hebrew (he)', 'stripe' ),
		'in'   => __( 'Indonesian (in)', 'stripe' ),
		'it'   => __( 'Italian (it)', 'stripe' ),
		'ja'   => __( 'Japanese (ja)', 'stripe' ),
		'lt'   => __( 'Lithuanian (lt)', 'stripe' ),
		'lv'   => __( 'Latvian (lv)', 'stripe' ),
		'ms'   => __( 'Malay (ms)', 'stripe' ),
		'nb'   => __( 'Norwegian Bokmål (nb)', 'stripe' ),
		'nl'   => __( 'Dutch (nl)', 'stripe' ),
		'pl'   => __( 'Polish (pl)', 'stripe' ),
		'pt'   => __( 'Portuguese (pt)', 'stripe' ),
		'ru'   => __( 'Russian (ru)', 'stripe' ),
		'sk'   => __( 'Slovak (sk)', 'stripe' ),
		'sl'   => __( 'Slovenian (sl)', 'stripe' ),
		'sv'   => __( 'Swedish (sv)', 'stripe' ),
		'zh'   => __( 'Chinese Simplified (zh)', 'stripe' ),
	);
}

/**
 * Returns a list of error codes and corresponding localized error messages.
 *
 * @since 3.9.0
 *
 * @return array $error_list List of error codes and corresponding error messages.
 */
function get_localized_error_messages() {
	$error_list = array(
		'invalid_number'           => __( 'The card number is not a valid credit card number.', 'stripe' ),
		'invalid_expiry_month'     => __( 'The card\'s expiration month is invalid.', 'stripe' ),
		'invalid_expiry_year'      => __( 'The card\'s expiration year is invalid.', 'stripe' ),
		'invalid_cvc'              => __( 'The card\'s security code is invalid.', 'stripe' ),
		'incorrect_number'         => __( 'The card number is incorrect.', 'stripe' ),
		'incomplete_number'        => __( 'The card number is incomplete.', 'stripe' ),
		'incomplete_cvc'           => __( 'The card\'s security code is incomplete.', 'stripe' ),
		'incomplete_expiry'        => __( 'The card\'s expiration date is incomplete.', 'stripe' ),
		'expired_card'             => __( 'The card has expired.', 'stripe' ),
		'incorrect_cvc'            => __( 'The card\'s security code is incorrect.', 'stripe' ),
		'incorrect_zip'            => __( 'The card\'s zip code failed validation.', 'stripe' ),
		'invalid_expiry_year_past' => __( 'The card\'s expiration year is in the past', 'stripe' ),
		'card_declined'            => __( 'The card was declined.', 'stripe' ),
		'processing_error'         => __( 'An error occurred while processing the card.', 'stripe' ),
		'invalid_request_error'    => __( 'Unable to process this payment, please try again or use alternative method.', 'stripe' ),
		'email_invalid'            => __( 'Invalid email address, please correct and try again.', 'stripe' ),
	);

	/**
	 * Filters the list of available error codes and corresponding error messages.
	 *
	 * @since 3.9.0
	 *
	 * @param array $error_list List of error codes and corresponding error messages.
	 */
	$error_list = apply_filters( 'simpay_get_localized_error_list', $error_list );

	return $error_list;
}

/**
 * Returns a localized error message for a corresponding Stripe
 * error code.
 *
 * @link https://stripe.com/docs/error-codes
 *
 * @since 3.9.0
 *
 * @param string $error_code Error code.
 * @param string $error_message Original error message to return if a localized version does not exist.
 * @return string $error_message Potentially localized error message.
 */
function get_localized_error_message( $error_code, $error_message ) {
	$error_list = get_localized_error_messages();

	if ( isset( $error_list[ $error_code ] ) ) {
		return $error_list[ $error_code ];
	}

	return $error_message;
}
