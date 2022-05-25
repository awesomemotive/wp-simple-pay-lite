<?php
/**
 * Internationalization: Stripe
 *
 * @package SimplePay\Core\i18n
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
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
		'BR' => __( 'Brazil', 'stripe' ),
		'BG' => __( 'Bulgaria', 'stripe' ),
		'CA' => __( 'Canada', 'stripe' ),
        'HR' => __( 'Croatia', 'stripe' ),
		'CY' => __( 'Cyprus', 'stripe' ),
		'CZ' => __( 'Czech Republic', 'stripe' ),
		'DK' => __( 'Denmark', 'stripe' ),
		'EE' => __( 'Estonia', 'stripe' ),
		'FI' => __( 'Finland', 'stripe' ),
		'FR' => __( 'France', 'stripe' ),
		'DE' => __( 'Germany', 'stripe' ),
        'GI' => __( 'Gibraltar', 'stripe' ),
		'GR' => __( 'Greece', 'stripe' ),
		'HK' => __( 'Hong Kong', 'stripe' ),
        'HU' => __( 'Hungary', 'stripe' ),
		'IN' => __( 'India', 'stripe' ),
		'IE' => __( 'Ireland', 'stripe' ),
		'IT' => __( 'Italy', 'stripe' ),
		'JP' => __( 'Japan', 'stripe' ),
		'LV' => __( 'Latvia', 'stripe' ),
        'LI' => __( 'Liechtenstein', 'stripe' ),
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
		'AE' => __( 'United Arab Emirates', 'stripe' ),
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
		'auto'  => __( 'Auto-detect', 'stripe' ),
		'bg'    => __( 'Bulgarian (bg)', 'stripe' ),
		'zh'    => __( 'Chinese Simplified (zh)', 'stripe' ),
		'zh-HK' => __( 'Chinese Traditional (zh-HK)', 'stripe' ),
		'zh-TW' => __( 'Chinese Traditional (zh-TW)', 'stripe' ),
		'hr'    => __( 'Croatian (hr)', 'stripe' ),
		'cs'    => __( 'Czech (cs)', 'stripe' ),
		'da'    => __( 'Danish (da)', 'stripe' ),
		'de'    => __( 'German (de)', 'stripe' ),
		'el'    => __( 'Greek (el)', 'stripe' ),
		'en'    => __( 'English (en)', 'stripe' ),
		'en-GB' => __( 'English (en-gb)', 'stripe' ),
		'et'    => __( 'Estonian (et)', 'stripe' ),
		'fi'    => __( 'Finnish (fi)', 'stripe' ),
		'fil'   => __( 'Filipino (fil)', 'stripe' ),
		'fr'    => __( 'French (fr)', 'stripe' ),
		'fr-CA' => __( 'French (fr-ca)', 'stripe' ),
		'hu'    => __( 'Hungarian (hu)', 'stripe' ),
		'it'    => __( 'Italian (it)', 'stripe' ),
		'ja'    => __( 'Japanese (ja)', 'stripe' ),
		'ko'    => __( 'Korean (kr)', 'stripe' ),
		'lt'    => __( 'Lithuanian (lt)', 'stripe' ),
		'lv'    => __( 'Latvian (lv)', 'stripe' ),
		'ms'    => __( 'Malay (ms)', 'stripe' ),
		'mt'    => __( 'Maltese (mt)', 'stripe' ),
		'nb'    => __( 'Norwegian Bokmål (nb)', 'stripe' ),
		'nl'    => __( 'Dutch (nl)', 'stripe' ),
		'pl'    => __( 'Polish (pl)', 'stripe' ),
		'pt'    => __( 'Portuguese (pt)', 'stripe' ),
		'pt-BR' => __( 'Portuguese (pt-BR)', 'stripe' ),
		'ro'    => __( 'Romanian (ro)', 'stripe' ),
		'ru'    => __( 'Russian (ru)', 'stripe' ),
		'sk'    => __( 'Slovak (sk)', 'stripe' ),
		'sl'    => __( 'Slovenian (sl)', 'stripe' ),
		'es'    => __( 'Spanish (es)', 'stripe' ),
		'sv'    => __( 'Swedish (sv)', 'stripe' ),
		'th'    => __( 'Thai (th)', 'stripe' ),
		'tk'    => __( 'Turkish (tk)', 'stripe' ),
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
		'auto'  => __( 'Auto-detect', 'stripe' ),
		'ar'    => __( 'Arabic', 'stripe' ),
		'bg'    => __( 'Bulgarian (bg)', 'stripe' ),
		'zh'    => __( 'Chinese Simplified (zh)', 'stripe' ),
		'zh-HK' => __( 'Chinese Traditional (zh-HK)', 'stripe' ),
		'zh-TW' => __( 'Chinese Traditional (zh-TW)', 'stripe' ),
		'hr'    => __( 'Croatian (hr)', 'stripe' ),
		'cs'    => __( 'Czech (cs)', 'stripe' ),
		'da'    => __( 'Danish (da)', 'stripe' ),
		'de'    => __( 'German (de)', 'stripe' ),
		'el'    => __( 'Greek (el)', 'stripe' ),
		'en'    => __( 'English (en)', 'stripe' ),
		'en-GB' => __( 'English (en-gb)', 'stripe' ),
		'et'    => __( 'Estonian (et)', 'stripe' ),
		'fi'    => __( 'Finnish (fi)', 'stripe' ),
		'fil'   => __( 'Filipino (fil)', 'stripe' ),
		'fr'    => __( 'French (fr)', 'stripe' ),
		'fr-CA' => __( 'French (fr-ca)', 'stripe' ),
		'he'    => __( 'Hebrew (he)', 'stripe' ),
		'hu'    => __( 'Hungarian (hu)', 'stripe' ),
		'it'    => __( 'Italian (it)', 'stripe' ),
		'ja'    => __( 'Japanese (ja)', 'stripe' ),
		'ko'    => __( 'Korean (kr)', 'stripe' ),
		'lt'    => __( 'Lithuanian (lt)', 'stripe' ),
		'lv'    => __( 'Latvian (lv)', 'stripe' ),
		'ms'    => __( 'Malay (ms)', 'stripe' ),
		'mt'    => __( 'Maltese (mt)', 'stripe' ),
		'nb'    => __( 'Norwegian Bokmål (nb)', 'stripe' ),
		'nl'    => __( 'Dutch (nl)', 'stripe' ),
		'pl'    => __( 'Polish (pl)', 'stripe' ),
		'pt'    => __( 'Portuguese (pt)', 'stripe' ),
		'pt-BR' => __( 'Portuguese (pt-BR)', 'stripe' ),
		'ro'    => __( 'Romanian (ro)', 'stripe' ),
		'ru'    => __( 'Russian (ru)', 'stripe' ),
		'sk'    => __( 'Slovak (sk)', 'stripe' ),
		'sl'    => __( 'Slovenian (sl)', 'stripe' ),
		'es'    => __( 'Spanish (es)', 'stripe' ),
		'sv'    => __( 'Swedish (sv)', 'stripe' ),
		'th'    => __( 'Thai (th)', 'stripe' ),
		'tk'    => __( 'Turkish (tk)', 'stripe' ),
	);
}

/**
 * Returns a list of Stripe-supported currencies.
 *
 * @since 4.0.0
 *
 * @return array
 */
function get_stripe_currencies() {
	return array(
		'AED' => esc_html__( 'United Arab Emirates Dirham', 'stripe' ),
		'AFN' => esc_html__( 'Afghan Afghani', 'stripe' ),
		'ALL' => esc_html__( 'Albanian Lek', 'stripe' ),
		'AMD' => esc_html__( 'Armenian Dram', 'stripe' ),
		'ANG' => esc_html__( 'Netherlands Antillean Gulden', 'stripe' ),
		'AOA' => esc_html__( 'Angolan Kwanza', 'stripe' ),
		'ARS' => esc_html__( 'Argentine Peso', 'stripe' ),
		'AUD' => esc_html__( 'Australian Dollar', 'stripe' ),
		'AWG' => esc_html__( 'Aruban Florin', 'stripe' ),
		'AZN' => esc_html__( 'Azerbaijani Manat', 'stripe' ),
		'BAM' => esc_html__( 'Bosnia & Herzegovina Convertible Mark', 'stripe' ),
		'BBD' => esc_html__( 'Barbadian Dollar', 'stripe' ),
		'BDT' => esc_html__( 'Bangladeshi Taka', 'stripe' ),
		'BIF' => esc_html__( 'Burundian Franc', 'stripe' ),
		'BGN' => esc_html__( 'Bulgarian Lev', 'stripe' ),
		'BMD' => esc_html__( 'Bermudian Dollar', 'stripe' ),
		'BND' => esc_html__( 'Brunei Dollar', 'stripe' ),
		'BOB' => esc_html__( 'Bolivian Boliviano', 'stripe' ),
		'BRL' => esc_html__( 'Brazilian Real', 'stripe' ),
		'BSD' => esc_html__( 'Bahamian Dollar', 'stripe' ),
		'BWP' => esc_html__( 'Botswana Pula', 'stripe' ),
		'BYR' => esc_html__( 'Belarusian Ruble', 'stripe' ),
		'BZD' => esc_html__( 'Belize Dollar', 'stripe' ),
		'CAD' => esc_html__( 'Canadian Dollar', 'stripe' ),
		'CDF' => esc_html__( 'Congolese Franc', 'stripe' ),
		'CHF' => esc_html__( 'Swiss Franc', 'stripe' ),
		'CLP' => esc_html__( 'Chilean Peso', 'stripe' ),
		'CNY' => esc_html__( 'Chinese Renminbi Yuan', 'stripe' ),
		'COP' => esc_html__( 'Colombian Peso', 'stripe' ),
		'CRC' => esc_html__( 'Costa Rican Colón', 'stripe' ),
		'CVE' => esc_html__( 'Cape Verdean Escudo', 'stripe' ),
		'CZK' => esc_html__( 'Czech Koruna', 'stripe' ),
		'DJF' => esc_html__( 'Djiboutian Franc', 'stripe' ),
		'DKK' => esc_html__( 'Danish Krone', 'stripe' ),
		'DOP' => esc_html__( 'Dominican Peso', 'stripe' ),
		'DZD' => esc_html__( 'Algerian Dinar', 'stripe' ),
		'EGP' => esc_html__( 'Egyptian Pound', 'stripe' ),
		'ETB' => esc_html__( 'Ethiopian Birr', 'stripe' ),
		'EUR' => esc_html__( 'Euro', 'stripe' ),
		'FJD' => esc_html__( 'Fijian Dollar', 'stripe' ),
		'FKP' => esc_html__( 'Falkland Islands Pound', 'stripe' ),
		'GBP' => esc_html__( 'British Pound', 'stripe' ),
		'GEL' => esc_html__( 'Georgian Lari', 'stripe' ),
		'GIP' => esc_html__( 'Gibraltar Pound', 'stripe' ),
		'GMD' => esc_html__( 'Gambian Dalasi', 'stripe' ),
		'GNF' => esc_html__( 'Guinean Franc', 'stripe' ),
		'GTQ' => esc_html__( 'Guatemalan Quetzal', 'stripe' ),
		'GYD' => esc_html__( 'Guyanese Dollar', 'stripe' ),
		'HKD' => esc_html__( 'Hong Kong Dollar', 'stripe' ),
		'HNL' => esc_html__( 'Honduran Lempira', 'stripe' ),
		'HRK' => esc_html__( 'Croatian Kuna', 'stripe' ),
		'HTG' => esc_html__( 'Haitian Gourde', 'stripe' ),
		'HUF' => esc_html__( 'Hungarian Forint', 'stripe' ),
		'IDR' => esc_html__( 'Indonesian Rupiah', 'stripe' ),
		'ILS' => esc_html__( 'Israeli New Sheqel', 'stripe' ),
		'INR' => esc_html__( 'Indian Rupee', 'stripe' ),
		'ISK' => esc_html__( 'Icelandic Króna', 'stripe' ),
		'JMD' => esc_html__( 'Jamaican Dollar', 'stripe' ),
		'JPY' => esc_html__( 'Japanese Yen', 'stripe' ),
		'KES' => esc_html__( 'Kenyan Shilling', 'stripe' ),
		'KGS' => esc_html__( 'Kyrgyzstani Som', 'stripe' ),
		'KHR' => esc_html__( 'Cambodian Riel', 'stripe' ),
		'KMF' => esc_html__( 'Comorian Franc', 'stripe' ),
		'KRW' => esc_html__( 'South Korean Won', 'stripe' ),
		'KYD' => esc_html__( 'Cayman Islands Dollar', 'stripe' ),
		'KZT' => esc_html__( 'Kazakhstani Tenge', 'stripe' ),
		'LAK' => esc_html__( 'Lao Kip', 'stripe' ),
		'LBP' => esc_html__( 'Lebanese Pound', 'stripe' ),
		'LKR' => esc_html__( 'Sri Lankan Rupee', 'stripe' ),
		'LRD' => esc_html__( 'Liberian Dollar', 'stripe' ),
		'LSL' => esc_html__( 'Lesotho Loti', 'stripe' ),
		'MAD' => esc_html__( 'Moroccan Dirham', 'stripe' ),
		'MDL' => esc_html__( 'Moldovan Leu', 'stripe' ),
		'MGA' => esc_html__( 'Malagasy Ariary', 'stripe' ),
		'MKD' => esc_html__( 'Macedonian Denar', 'stripe' ),
		'MMK' => esc_html__( 'Myanmar Kyat', 'stripe' ),
		'MNT' => esc_html__( 'Mongolian Tögrög', 'stripe' ),
		'MOP' => esc_html__( 'Macanese Pataca', 'stripe' ),
		'MRO' => esc_html__( 'Mauritanian Ouguiya', 'stripe' ),
		'MUR' => esc_html__( 'Mauritian Rupee', 'stripe' ),
		'MVR' => esc_html__( 'Maldivian Rufiyaa', 'stripe' ),
		'MWK' => esc_html__( 'Malawian Kwacha', 'stripe' ),
		'MXN' => esc_html__( 'Mexican Peso', 'stripe' ),
		'MYR' => esc_html__( 'Malaysian Ringgit', 'stripe' ),
		'MZN' => esc_html__( 'Mozambican Metical', 'stripe' ),
		'NAD' => esc_html__( 'Namibian Dollar', 'stripe' ),
		'NGN' => esc_html__( 'Nigerian Naira', 'stripe' ),
		'NIO' => esc_html__( 'Nicaraguan Córdoba', 'stripe' ),
		'NOK' => esc_html__( 'Norwegian Krone', 'stripe' ),
		'NPR' => esc_html__( 'Nepalese Rupee', 'stripe' ),
		'NZD' => esc_html__( 'New Zealand Dollar', 'stripe' ),
		'PAB' => esc_html__( 'Panamanian Balboa', 'stripe' ),
		'PEN' => esc_html__( 'Peruvian Nuevo Sol', 'stripe' ),
		'PGK' => esc_html__( 'Papua New Guinean Kina', 'stripe' ),
		'PHP' => esc_html__( 'Philippine Peso', 'stripe' ),
		'PKR' => esc_html__( 'Pakistani Rupee', 'stripe' ),
		'PLN' => esc_html__( 'Polish Złoty', 'stripe' ),
		'PYG' => esc_html__( 'Paraguayan Guaraní', 'stripe' ),
		'QAR' => esc_html__( 'Qatari Riyal', 'stripe' ),
		'RON' => esc_html__( 'Romanian Leu', 'stripe' ),
		'RSD' => esc_html__( 'Serbian Dinar', 'stripe' ),
		'RUB' => esc_html__( 'Russian Ruble', 'stripe' ),
		'RWF' => esc_html__( 'Rwandan Franc', 'stripe' ),
		'SAR' => esc_html__( 'Saudi Riyal', 'stripe' ),
		'SBD' => esc_html__( 'Solomon Islands Dollar', 'stripe' ),
		'SCR' => esc_html__( 'Seychellois Rupee', 'stripe' ),
		'SEK' => esc_html__( 'Swedish Krona', 'stripe' ),
		'SGD' => esc_html__( 'Singapore Dollar', 'stripe' ),
		'SHP' => esc_html__( 'Saint Helenian Pound', 'stripe' ),
		'SLL' => esc_html__( 'Sierra Leonean Leone', 'stripe' ),
		'SOS' => esc_html__( 'Somali Shilling', 'stripe' ),
		'SRD' => esc_html__( 'Surinamese Dollar', 'stripe' ),
		'STD' => esc_html__( 'São Tomé and Príncipe Dobra', 'stripe' ),
		'SZL' => esc_html__( 'Swazi Lilangeni', 'stripe' ),
		'THB' => esc_html__( 'Thai Baht', 'stripe' ),
		'TJS' => esc_html__( 'Tajikistani Somoni', 'stripe' ),
		'TOP' => esc_html__( 'Tongan Paʻanga', 'stripe' ),
		'TRY' => esc_html__( 'Turkish Lira', 'stripe' ),
		'TTD' => esc_html__( 'Trinidad and Tobago Dollar', 'stripe' ),
		'TWD' => esc_html__( 'New Taiwan Dollar', 'stripe' ),
		'TZS' => esc_html__( 'Tanzanian Shilling', 'stripe' ),
		'UAH' => esc_html__( 'Ukrainian Hryvnia', 'stripe' ),
		'UGX' => esc_html__( 'Ugandan Shilling', 'stripe' ),
		'USD' => esc_html__( 'United States Dollar', 'stripe' ),
		'UYU' => esc_html__( 'Uruguayan Peso', 'stripe' ),
		'UZS' => esc_html__( 'Uzbekistani Som', 'stripe' ),
		'VND' => esc_html__( 'Vietnamese Đồng', 'stripe' ),
		'VUV' => esc_html__( 'Vanuatu Vatu', 'stripe' ),
		'WST' => esc_html__( 'Samoan Tala', 'stripe' ),
		'XAF' => esc_html__( 'Central African Cfa Franc', 'stripe' ),
		'XCD' => esc_html__( 'East Caribbean Dollar', 'stripe' ),
		'XOF' => esc_html__( 'West African Cfa Franc', 'stripe' ),
		'XPF' => esc_html__( 'Cfp Franc', 'stripe' ),
		'YER' => esc_html__( 'Yemeni Rial', 'stripe' ),
		'ZAR' => esc_html__( 'South African Rand', 'stripe' ),
		'ZMW' => esc_html__( 'Zambian Kwacha', 'stripe' ),
	);
}

/**
 * Returns a list of Customer Tax ID types.
 *
 * @since 4.2.0
 *
 * @return array $tax_id_types List of tax ID types.
 */
function get_stripe_tax_id_types() {
	$tax_id_types = array(
		'ae_trn'  => esc_html__( 'United Arab Emirates TRN', 'stripe' ),
		'au_abn'  => esc_html__( 'Australian Business Number', 'stripe' ),
		'br_cnpj' => esc_html__( 'Brazilian CNPJ number', 'stripe' ),
		'br_cpf'  => esc_html__( 'Brazilian CPF number', 'stripe' ),
		'ca_bn'   => esc_html__( 'Canadian BN', 'stripe' ),
		'ca_qst'  => esc_html__( 'Canadian QST number', 'stripe' ),
		'ch_vat'  => esc_html__( 'Switzerland VAT number', 'stripe' ),
		'cl_tin'  => esc_html__( 'Chilean TIN', 'stripe' ),
		'es_cif'  => esc_html__( 'Spanish CIF number', 'stripe' ),
		'eu_vat'  => esc_html__( 'European VAT number', 'stripe' ),
		'gb_vat'  => esc_html__( 'United Kingdom VAT number', 'stripe' ),
		'hk_br'   => esc_html__( 'Hong Kong BR number', 'stripe' ),
		'id_npwp' => esc_html__( 'Indonesian NPWP number', 'stripe' ),
		'id_gst'  => esc_html__( 'Indian GST number', 'stripe' ),
		'jp_cn'   => esc_html__( 'Japanese Corporate Number', 'stripe' ),
		'jp_rn'   => esc_html__(
			'Japanese Registered Foreign Businesses\' Registration Number',
			'stripe'
		),
		'kr_brn'   => esc_html__( 'Korean BRN', 'stripe' ),
		'li_uid'   => esc_html__( 'Liechtensteinian UID number', 'stripe' ),
		'mx_rfc'   => esc_html__( 'Mexican RFC number', 'stripe' ),
		'my_frp'   => esc_html__( 'Malaysian FRP number', 'stripe' ),
		'my_itn'   => esc_html__( 'Malaysian ITN', 'stripe' ),
		'my_sst'   => esc_html__( 'Malaysian SST number', 'stripe' ),
		'no_vat'   => esc_html__( 'Norwegian VAT number', 'stripe' ),
		'nz_gst'   => esc_html__( 'New Zealand GST number', 'stripe' ),
		'ru_inn'   => esc_html__( 'Russian INN', 'stripe' ),
		'ru_kpp'   => esc_html__( 'Russian KPP', 'stripe' ),
		'sa_vat'   => esc_html__( 'Saudi Arabia VAT', 'stripe' ),
		'sg_gst'   => esc_html__( 'Singaporean GST', 'stripe' ),
		'sg_uen'   => esc_html__( 'Singaporean UEN', 'stripe' ),
		'th_vat'   => esc_html__( 'Thai VAT', 'stripe' ),
		'tw_vat'   => esc_html__( 'Taiwanese VAT', 'stripe' ),
		'us_ein'   => esc_html__( 'United States EIN', 'stripe' ),
		'za_vat'   => esc_html__( 'South African VAT number', 'stripe' ),
	);

	/**
	 * Filters the supported Customer Tax ID types.
	 *
	 * @since 4.2.0
	 *
	 * @param array $tax_id_types Supported Customer Tax ID types.
	 */
	$tax_id_types = apply_filters( 'simpay_stripe_tax_id_types', $tax_id_types );

	return $tax_id_types;
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
