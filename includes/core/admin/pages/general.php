<?php

namespace SimplePay\Core\Admin\Pages;

use SimplePay\Core\Abstracts\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feeds settings.
 *
 * Handle form settings and outputs the settings page markup.
 *
 * @since 3.0.0
 */
class General extends Admin_Page {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->id           = 'general';
		$this->option_group = 'settings';
		$this->label        = esc_html__( 'General', 'simple-pay' );
		$this->link_text    = esc_html__( 'Help docs for General Settings', 'simple-pay' );
		$this->link_slug    = ''; // TODO: Fill in slug, not in use currently (issue #301)
		$this->ga_content   = 'general-settings';

		$this->sections = $this->add_sections();
		$this->fields   = $this->add_fields();
	}

	/**
	 * Add sections.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function add_sections() {

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_sections', array(
			'general'          => array(
				'title' => esc_html__( 'General', 'simple-pay' ),
			),
			'general_currency' => array(
				'title' => esc_html__( 'Currency Options', 'simple-pay' ),
			),
			'styles'           => array(
				'title' => esc_html__( 'Styles', 'simple-pay' ),
			),
			'general_misc'     => array(
				'title' => esc_html__( 'Other', 'simple-pay' ),
			),
		) );
	}

	/**
	 * Add fields.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function add_fields() {

		$fields       = array();
		$this->values = get_option( 'simpay_' . $this->option_group . '_' . $this->id );

		if ( isset( $this->sections ) && is_array( $this->sections ) ) {
			foreach ( $this->sections as $section => $a ) {

				$section = sanitize_key( $section );

				if ( 'general' == $section ) {


					$options = get_option( 'simpay_settings' );

					$success_default = isset( $options['confirmation_pages']['confirmation'] ) ? $options['confirmation_pages']['confirmation'] : '';
					$failure_default = isset( $options['confirmation_pages']['failed'] ) ? $options['confirmation_pages']['failed'] : '';

					$fields[ $section ] = array(
						'success_page' => array(
							'title'       => esc_html__( 'Payment Success Page', 'simple-pay' ),
							'type'        => 'select',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][success_page]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-success-page',
							'value'       => $this->get_option_value( $section, 'success_page' ),
							'page_select' => 'page_select',
							'default'     => $success_default,
							'class'       => array( 'simpay-chosen-search' ),
							'description' => sprintf( esc_html__( 'The page customers are sent to after completing a payment. The shortcode %s needs to be on this page. Output configured in the Payment Confirmation settings. This page should be excluded from any site caching.', 'simple-pay' ), '<code>[simpay_payment_receipt]</code>' ),
						),
						'failure_page' => array(
							'title'       => esc_html__( 'Payment Failure Page', 'simple-pay' ),
							'type'        => 'select',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][failure_page]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-failure-page',
							'value'       => $this->get_option_value( $section, 'failure_page' ),
							'page_select' => 'page_select',
							'class'       => array( 'simpay-chosen-search' ),
							'default'     => $failure_default,
							'description' => esc_html__( 'The page customers are sent to after a failed payment.', 'simple-pay' ),
						),
					);

				} elseif ( 'general_currency' == $section ) {

					$currencies = $this->get_currencies();

					if ( ! empty( $currencies ) && is_array( $currencies ) ) {
						foreach ( $currencies as $code => $name ) {
							$currencies[ $code ] = $name . ' (' . simpay_get_currency_symbol( $code ) . ')';
						}
					}

					$saved_currency_symbol = simpay_get_currency_symbol( $this->get_option_value( $section, 'currency' ) );

					$formatted_amount = simpay_format_currency( ( simpay_is_zero_decimal() ? 499 : 4.99 ), '', false );

					$fields[ $section ] = array(
						'currency'          => array(
							'title'   => esc_html__( 'Currency', 'simple-pay' ),
							'type'    => 'select',
							'options' => $currencies,
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][currency]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-currency',
							'value'   => $this->get_option_value( $section, 'currency' ),
							'class'   => array(
								'simpay-chosen-search',
							),
							'default' => 'USD',
						),
						'currency_position' => array(
							'title'   => esc_html__( 'Currency Position', 'simple-pay' ),
							'type'    => 'select',
							//'subtype' => 'select',
							'options' => array(
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'left'        => sprintf( esc_html__( 'Left (%1$s%2$s)', 'simple-pay' ), $saved_currency_symbol, $formatted_amount ),
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'right'       => sprintf( esc_html__( 'Right (%1$s%2$s)', 'simple-pay' ), $formatted_amount, $saved_currency_symbol ),
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'left_space'  => sprintf( esc_html__( 'Left with Space (%1$s %2$s)', 'simple-pay' ), $saved_currency_symbol, $formatted_amount ),
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'right_space' => sprintf( esc_html__( 'Right with Space (%1$s %2$s)', 'simple-pay' ), $formatted_amount, $saved_currency_symbol ),
							),
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][currency_position]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-currency-position',
							'value'   => $this->get_option_value( $section, 'currency_position' ),
							'default' => 'left',
						),
						'separator'         => array(
							'title'       => esc_html__( 'Separators', 'simple-pay' ),
							'type'        => 'checkbox',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][separator]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-separator',
							'value'       => $this->get_option_value( $section, 'separator' ),
							'text'        => esc_html__( 'Use a comma when formatting decimal amounts and use a period to separate thousands.', 'simple-pay' ),
							/* translators: 1. Example amount formatted with comma (,) as decimal separator, 2. Example amount formatted with period (.) as decimal separator */
							'description' => sprintf( esc_html__( 'If enabled, amounts will be formatted as "%1$s" instead of "%2$s".', 'simple-pay' ), '1.234,56', '1,234.56' ),
						),
					);

				} elseif ( 'styles' == $section ) {

					$fields[ $section ] = array(
						'default_plugin_styles' => array(
							'title'       => esc_html__( 'Default Plugin Styles', 'simple-pay' ),
							'type'        => 'radio',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][default_plugin_styles]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-default-plugin-styles',
							'value'       => $this->get_option_value( $section, 'default_plugin_styles' ),
							'options'     => array(
								'enabled'  => esc_html__( 'Enabled', 'simple-pay' ),
								'disabled' => esc_html__( 'Disabled', 'simple-pay' ),
							),
							'default'     => 'enabled',
							'inline'      => 'inline',
							'description' => sprintf( esc_html__( 'Optionally disable all payment form styles (CSS files) included with %s. Styles in the Stripe Checkout overlay cannot be changed.', 'simple-pay' ), SIMPLE_PAY_PLUGIN_NAME ),
						),
					);

				} elseif ( 'general_misc' == $section ) {

					$fields[ $section ] = array(
						'save_settings' => array(
							'title'       => esc_html__( 'Save Settings', 'simple-pay' ),
							'type'        => 'checkbox',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][save_settings]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-save-settings',
							'value'       => $this->get_option_value( $section, 'save_settings' ),
							'default'     => 'yes',
							'description' => sprintf( esc_html__( 'If UN-checked, all %s plugin data will be removed when the plugin is deleted. However, your data saved with Stripe will not be deleted.', 'simple-pay' ), SIMPLE_PAY_PLUGIN_NAME ),
						),
					);
				}
			}
		}

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_fields', $fields );
	}

	/**
	 * TODO: We need to make sure we keep these up to date if Stripe adds any more
	 * https://support.stripe.com/questions/which-currencies-does-stripe-support
	 */
	public function get_currencies() {

		return array(
			'AED' => esc_html__( 'United Arab Emirates Dirham', 'simple-pay' ),
			'AFN' => esc_html__( 'Afghan Afghani', 'simple-pay' ), // NON AMEX
			'ALL' => esc_html__( 'Albanian Lek', 'simple-pay' ),
			'AMD' => esc_html__( 'Armenian Dram', 'simple-pay' ),
			'ANG' => esc_html__( 'Netherlands Antillean Gulden', 'simple-pay' ),
			'AOA' => esc_html__( 'Angolan Kwanza', 'simple-pay' ), // NON AMEX
			'ARS' => esc_html__( 'Argentine Peso', 'simple-pay' ), // non amex
			'AUD' => esc_html__( 'Australian Dollar', 'simple-pay' ),
			'AWG' => esc_html__( 'Aruban Florin', 'simple-pay' ),
			'AZN' => esc_html__( 'Azerbaijani Manat', 'simple-pay' ),
			'BAM' => esc_html__( 'Bosnia & Herzegovina Convertible Mark', 'simple-pay' ),
			'BBD' => esc_html__( 'Barbadian Dollar', 'simple-pay' ),
			'BDT' => esc_html__( 'Bangladeshi Taka', 'simple-pay' ),
			'BIF' => esc_html__( 'Burundian Franc', 'simple-pay' ),
			'BGN' => esc_html__( 'Bulgarian Lev', 'simple-pay' ),
			'BMD' => esc_html__( 'Bermudian Dollar', 'simple-pay' ),
			'BND' => esc_html__( 'Brunei Dollar', 'simple-pay' ),
			'BOB' => esc_html__( 'Bolivian Boliviano', 'simple-pay' ), // NON AMEX
			'BRL' => esc_html__( 'Brazilian Real', 'simple-pay' ), // NON AMEX
			'BSD' => esc_html__( 'Bahamian Dollar', 'simple-pay' ),
			'BWP' => esc_html__( 'Botswana Pula', 'simple-pay' ),
			'BZD' => esc_html__( 'Belize Dollar', 'simple-pay' ),
			'CAD' => esc_html__( 'Canadian Dollar', 'simple-pay' ),
			'CDF' => esc_html__( 'Congolese Franc', 'simple-pay' ),
			'CHF' => esc_html__( 'Swiss Franc', 'simple-pay' ),
			'CLP' => esc_html__( 'Chilean Peso', 'simple-pay' ), // NON AMEX
			'CNY' => esc_html__( 'Chinese Renminbi Yuan', 'simple-pay' ),
			'COP' => esc_html__( 'Colombian Peso', 'simple-pay' ), // NON AMEX
			'CRC' => esc_html__( 'Costa Rican Colón', 'simple-pay' ), // NON AMEX
			'CVE' => esc_html__( 'Cape Verdean Escudo', 'simple-pay' ), // NON AMEX
			'CZK' => esc_html__( 'Czech Koruna', 'simple-pay' ), // NON AMEX
			'DJF' => esc_html__( 'Djiboutian Franc', 'simple-pay' ), // NON AMEX
			'DKK' => esc_html__( 'Danish Krone', 'simple-pay' ),
			'DOP' => esc_html__( 'Dominican Peso', 'simple-pay' ),
			'DZD' => esc_html__( 'Algerian Dinar', 'simple-pay' ),
			'EGP' => esc_html__( 'Egyptian Pound', 'simple-pay' ),
			'ETB' => esc_html__( 'Ethiopian Birr', 'simple-pay' ),
			'EUR' => esc_html__( 'Euro', 'simple-pay' ),
			'FJD' => esc_html__( 'Fijian Dollar', 'simple-pay' ),
			'FKP' => esc_html__( 'Falkland Islands Pound', 'simple-pay' ), // NON AMEX
			'GBP' => esc_html__( 'British Pound', 'simple-pay' ),
			'GEL' => esc_html__( 'Georgian Lari', 'simple-pay' ),
			'GIP' => esc_html__( 'Gibraltar Pound', 'simple-pay' ),
			'GMD' => esc_html__( 'Gambian Dalasi', 'simple-pay' ),
			'GNF' => esc_html__( 'Guinean Franc', 'simple-pay' ), // NON AMEX
			'GTQ' => esc_html__( 'Guatemalan Quetzal', 'simple-pay' ), // NON AMEX
			'GYD' => esc_html__( 'Guyanese Dollar', 'simple-pay' ),
			'HKD' => esc_html__( 'Hong Kong Dollar', 'simple-pay' ),
			'HNL' => esc_html__( 'Honduran Lempira', 'simple-pay' ), // NON AMEX
			'HRK' => esc_html__( 'Croatian Kuna', 'simple-pay' ),
			'HTG' => esc_html__( 'Haitian Gourde', 'simple-pay' ),
			'HUF' => esc_html__( 'Hungarian Forint', 'simple-pay' ), // NON AMEX
			'IDR' => esc_html__( 'Indonesian Rupiah', 'simple-pay' ),
			'ILS' => esc_html__( 'Israeli New Sheqel', 'simple-pay' ),
			'INR' => esc_html__( 'Indian Rupee', 'simple-pay' ), // NON AMEX
			'ISK' => esc_html__( 'Icelandic Króna', 'simple-pay' ),
			'JMD' => esc_html__( 'Jamaican Dollar', 'simple-pay' ),
			'JPY' => esc_html__( 'Japanese Yen', 'simple-pay' ),
			'KES' => esc_html__( 'Kenyan Shilling', 'simple-pay' ),
			'KGS' => esc_html__( 'Kyrgyzstani Som', 'simple-pay' ),
			'KHR' => esc_html__( 'Cambodian Riel', 'simple-pay' ),
			'KMF' => esc_html__( 'Comorian Franc', 'simple-pay' ),
			'KRW' => esc_html__( 'South Korean Won', 'simple-pay' ),
			'KYD' => esc_html__( 'Cayman Islands Dollar', 'simple-pay' ),
			'KZT' => esc_html__( 'Kazakhstani Tenge', 'simple-pay' ),
			'LAK' => esc_html__( 'Lao Kip', 'simple-pay' ), // NON AMEX
			'LBP' => esc_html__( 'Lebanese Pound', 'simple-pay' ),
			'LKR' => esc_html__( 'Sri Lankan Rupee', 'simple-pay' ),
			'LRD' => esc_html__( 'Liberian Dollar', 'simple-pay' ),
			'LSL' => esc_html__( 'Lesotho Loti', 'simple-pay' ),
			'MAD' => esc_html__( 'Moroccan Dirham', 'simple-pay' ),
			'MDL' => esc_html__( 'Moldovan Leu', 'simple-pay' ),
			'MGA' => esc_html__( 'Malagasy Ariary', 'simple-pay' ),
			'MKD' => esc_html__( 'Macedonian Denar', 'simple-pay' ),
			'MNT' => esc_html__( 'Mongolian Tögrög', 'simple-pay' ),
			'MOP' => esc_html__( 'Macanese Pataca', 'simple-pay' ),
			'MRO' => esc_html__( 'Mauritanian Ouguiya', 'simple-pay' ),
			'MUR' => esc_html__( 'Mauritian Rupee', 'simple-pay' ), // NON AMEX
			'MVR' => esc_html__( 'Maldivian Rufiyaa', 'simple-pay' ),
			'MWK' => esc_html__( 'Malawian Kwacha', 'simple-pay' ),
			'MXN' => esc_html__( 'Mexican Peso', 'simple-pay' ), // NON AMEX
			'MYR' => esc_html__( 'Malaysian Ringgit', 'simple-pay' ),
			'MZN' => esc_html__( 'Mozambican Metical', 'simple-pay' ),
			'NAD' => esc_html__( 'Namibian Dollar', 'simple-pay' ),
			'NGN' => esc_html__( 'Nigerian Naira', 'simple-pay' ),
			'NIO' => esc_html__( 'Nicaraguan Córdoba', 'simple-pay' ), // NON AMEX
			'NOK' => esc_html__( 'Norwegian Krone', 'simple-pay' ),
			'NPR' => esc_html__( 'Nepalese Rupee', 'simple-pay' ),
			'NZD' => esc_html__( 'New Zealand Dollar', 'simple-pay' ),
			'PAB' => esc_html__( 'Panamanian Balboa', 'simple-pay' ), // NON AMEX
			'PEN' => esc_html__( 'Peruvian Nuevo Sol', 'simple-pay' ), // NON AMEX
			'PGK' => esc_html__( 'Papua New Guinean Kina', 'simple-pay' ),
			'PHP' => esc_html__( 'Philippine Peso', 'simple-pay' ),
			'PKR' => esc_html__( 'Pakistani Rupee', 'simple-pay' ),
			'PLN' => esc_html__( 'Polish Złoty', 'simple-pay' ),
			'PYG' => esc_html__( 'Paraguayan Guaraní', 'simple-pay' ), // NON AMEX
			'QAR' => esc_html__( 'Qatari Riyal', 'simple-pay' ),
			'RON' => esc_html__( 'Romanian Leu', 'simple-pay' ),
			'RSD' => esc_html__( 'Serbian Dinar', 'simple-pay' ),
			'RUB' => esc_html__( 'Russian Ruble', 'simple-pay' ),
			'RWF' => esc_html__( 'Rwandan Franc', 'simple-pay' ),
			'SAR' => esc_html__( 'Saudi Riyal', 'simple-pay' ),
			'SBD' => esc_html__( 'Solomon Islands Dollar', 'simple-pay' ),
			'SCR' => esc_html__( 'Seychellois Rupee', 'simple-pay' ),
			'SEK' => esc_html__( 'Swedish Krona', 'simple-pay' ),
			'SGD' => esc_html__( 'Singapore Dollar', 'simple-pay' ),
			'SHP' => esc_html__( 'Saint Helenian Pound', 'simple-pay' ), // NON AMEX
			'SLL' => esc_html__( 'Sierra Leonean Leone', 'simple-pay' ),
			'SOS' => esc_html__( 'Somali Shilling', 'simple-pay' ),
			'SRD' => esc_html__( 'Surinamese Dollar', 'simple-pay' ), // NON AMEX
			'STD' => esc_html__( 'São Tomé and Príncipe Dobra', 'simple-pay' ),
			'SVC' => esc_html__( 'Salvadoran Colón', 'simple-pay' ), // NON AMEX
			'SZL' => esc_html__( 'Swazi Lilangeni', 'simple-pay' ),
			'THB' => esc_html__( 'Thai Baht', 'simple-pay' ),
			'TJS' => esc_html__( 'Tajikistani Somoni', 'simple-pay' ),
			'TOP' => esc_html__( 'Tongan Paʻanga', 'simple-pay' ),
			'TRY' => esc_html__( 'Turkish Lira', 'simple-pay' ),
			'TTD' => esc_html__( 'Trinidad and Tobago Dollar', 'simple-pay' ),
			'TWD' => esc_html__( 'New Taiwan Dollar', 'simple-pay' ),
			'TZS' => esc_html__( 'Tanzanian Shilling', 'simple-pay' ),
			'UAH' => esc_html__( 'Ukrainian Hryvnia', 'simple-pay' ),
			'UGX' => esc_html__( 'Ugandan Shilling', 'simple-pay' ),
			'USD' => esc_html__( 'United States Dollar', 'simple-pay' ),
			'UYU' => esc_html__( 'Uruguayan Peso', 'simple-pay' ), // NON AMEX
			'UZS' => esc_html__( 'Uzbekistani Som', 'simple-pay' ),
			'VND' => esc_html__( 'Vietnamese Đồng', 'simple-pay' ),
			'VUV' => esc_html__( 'Vanuatu Vatu', 'simple-pay' ),
			'WST' => esc_html__( 'Samoan Tala', 'simple-pay' ),
			'XAF' => esc_html__( 'Central African Cfa Franc', 'simple-pay' ),
			'XCD' => esc_html__( 'East Caribbean Dollar', 'simple-pay' ),
			'XOF' => esc_html__( 'West African Cfa Franc', 'simple-pay' ), // NON AMEX
			'XPF' => esc_html__( 'Cfp Franc', 'simple-pay' ), // NON AMEX
			'YER' => esc_html__( 'Yemeni Rial', 'simple-pay' ),
			'ZAR' => esc_html__( 'South African Rand', 'simple-pay' ),
			'ZMW' => esc_html__( 'Zambian Kwacha', 'simple-pay' ),
		);
	}
}
