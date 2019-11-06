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
		$this->label        = esc_html__( 'General', 'stripe' );
		$this->link_text    = esc_html__( 'Help docs for General Settings', 'stripe' );
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
				'title' => esc_html__( 'General', 'stripe' ),
			),
			'general_currency' => array(
				'title' => esc_html__( 'Currency Options', 'stripe' ),
			),
			'styles'           => array(
				'title' => esc_html__( 'Styles', 'stripe' ),
			),
			'general_misc'     => array(
				'title' => esc_html__( 'Other', 'stripe' ),
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
							'title'       => esc_html__( 'Payment Success Page', 'stripe' ),
							'type'        => 'select',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][success_page]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-success-page',
							'value'       => $this->get_option_value( $section, 'success_page' ),
							'page_select' => 'page_select',
							'default'     => $success_default,
							'class'       => array( 'simpay-chosen-search' ),
							'description' => sprintf( esc_html__( 'The page customers are sent to after completing a payment. The shortcode %s needs to be on this page. Output configured in the Payment Confirmation settings. This page should be excluded from any site caching.', 'stripe' ), '<code>[simpay_payment_receipt]</code>' ),
						),
						'failure_page' => array(
							'title'       => esc_html__( 'Payment Failure Page', 'stripe' ),
							'type'        => 'select',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][failure_page]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-failure-page',
							'value'       => $this->get_option_value( $section, 'failure_page' ),
							'page_select' => 'page_select',
							'class'       => array( 'simpay-chosen-search' ),
							'default'     => $failure_default,
							'description' => esc_html__( 'The page customers are sent to after a failed payment.', 'stripe' ),
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
							'title'   => esc_html__( 'Currency', 'stripe' ),
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
							'title'   => esc_html__( 'Currency Position', 'stripe' ),
							'type'    => 'select',
							//'subtype' => 'select',
							'options' => array(
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'left'        => sprintf( esc_html__( 'Left (%1$s%2$s)', 'stripe' ), $saved_currency_symbol, $formatted_amount ),
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'right'       => sprintf( esc_html__( 'Right (%1$s%2$s)', 'stripe' ), $formatted_amount, $saved_currency_symbol ),
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'left_space'  => sprintf( esc_html__( 'Left with Space (%1$s %2$s)', 'stripe' ), $saved_currency_symbol, $formatted_amount ),
								/* translators: 1. Saved Currency Symbol, 2. Formatted Amount value */
								'right_space' => sprintf( esc_html__( 'Right with Space (%1$s %2$s)', 'stripe' ), $formatted_amount, $saved_currency_symbol ),
							),
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][currency_position]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-currency-position',
							'value'   => $this->get_option_value( $section, 'currency_position' ),
							'default' => 'left',
						),
						'separator'         => array(
							'title'       => esc_html__( 'Separators', 'stripe' ),
							'type'        => 'checkbox',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][separator]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-separator',
							'value'       => $this->get_option_value( $section, 'separator' ),
							'text'        => esc_html__( 'Use a comma when formatting decimal amounts and use a period to separate thousands.', 'stripe' ),
							/* translators: 1. Example amount formatted with comma (,) as decimal separator, 2. Example amount formatted with period (.) as decimal separator */
							'description' => sprintf( esc_html__( 'If enabled, amounts will be formatted as "%1$s" instead of "%2$s".', 'stripe' ), '1.234,56', '1,234.56' ),
						),
					);

				} elseif ( 'styles' == $section ) {

					$fields[ $section ] = array(
						'default_plugin_styles' => array(
							'title'       => esc_html__( 'Opinionated Styles', 'stripe' ),
							'type'        => 'radio',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][default_plugin_styles]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-default-plugin-styles',
							'value'       => $this->get_option_value( $section, 'default_plugin_styles' ),
							'options'     => array(
								'enabled'  => esc_html__( 'Enabled', 'stripe' ),
								'disabled' => esc_html__( 'Disabled', 'stripe' ),
							),
							'default'     => 'enabled',
							'inline'      => 'inline',
							/* translators: Plugin name */
							'description' => sprintf( esc_html__( 'Automatically apply %1$s styles to payment form fields and buttons.', 'stripe' ), SIMPLE_PAY_PLUGIN_NAME ). '<br />' . esc_html__( 'Styles on the Stripe.com Checkout page cannot be changed.', 'stripe' ),
						),
					);

				} elseif ( 'general_misc' == $section ) {

					$fields[ $section ] = array(
						'save_settings' => array(
							'title'       => esc_html__( 'Save Settings', 'stripe' ),
							'type'        => 'checkbox',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][save_settings]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-save-settings',
							'value'       => $this->get_option_value( $section, 'save_settings' ),
							'default'     => 'yes',
							'description' => sprintf( esc_html__( 'If UN-checked, all %s plugin data will be removed when the plugin is deleted. However, your data saved with Stripe will not be deleted.', 'stripe' ), SIMPLE_PAY_PLUGIN_NAME ),
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
			'AED' => esc_html__( 'United Arab Emirates Dirham', 'stripe' ),
			'AFN' => esc_html__( 'Afghan Afghani', 'stripe' ), // NON AMEX
			'ALL' => esc_html__( 'Albanian Lek', 'stripe' ),
			'AMD' => esc_html__( 'Armenian Dram', 'stripe' ),
			'ANG' => esc_html__( 'Netherlands Antillean Gulden', 'stripe' ),
			'AOA' => esc_html__( 'Angolan Kwanza', 'stripe' ), // NON AMEX
			'ARS' => esc_html__( 'Argentine Peso', 'stripe' ), // non amex
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
			'BOB' => esc_html__( 'Bolivian Boliviano', 'stripe' ), // NON AMEX
			'BRL' => esc_html__( 'Brazilian Real', 'stripe' ), // NON AMEX
			'BSD' => esc_html__( 'Bahamian Dollar', 'stripe' ),
			'BWP' => esc_html__( 'Botswana Pula', 'stripe' ),
			'BZD' => esc_html__( 'Belize Dollar', 'stripe' ),
			'CAD' => esc_html__( 'Canadian Dollar', 'stripe' ),
			'CDF' => esc_html__( 'Congolese Franc', 'stripe' ),
			'CHF' => esc_html__( 'Swiss Franc', 'stripe' ),
			'CLP' => esc_html__( 'Chilean Peso', 'stripe' ), // NON AMEX
			'CNY' => esc_html__( 'Chinese Renminbi Yuan', 'stripe' ),
			'COP' => esc_html__( 'Colombian Peso', 'stripe' ), // NON AMEX
			'CRC' => esc_html__( 'Costa Rican Colón', 'stripe' ), // NON AMEX
			'CVE' => esc_html__( 'Cape Verdean Escudo', 'stripe' ), // NON AMEX
			'CZK' => esc_html__( 'Czech Koruna', 'stripe' ), // NON AMEX
			'DJF' => esc_html__( 'Djiboutian Franc', 'stripe' ), // NON AMEX
			'DKK' => esc_html__( 'Danish Krone', 'stripe' ),
			'DOP' => esc_html__( 'Dominican Peso', 'stripe' ),
			'DZD' => esc_html__( 'Algerian Dinar', 'stripe' ),
			'EGP' => esc_html__( 'Egyptian Pound', 'stripe' ),
			'ETB' => esc_html__( 'Ethiopian Birr', 'stripe' ),
			'EUR' => esc_html__( 'Euro', 'stripe' ),
			'FJD' => esc_html__( 'Fijian Dollar', 'stripe' ),
			'FKP' => esc_html__( 'Falkland Islands Pound', 'stripe' ), // NON AMEX
			'GBP' => esc_html__( 'British Pound', 'stripe' ),
			'GEL' => esc_html__( 'Georgian Lari', 'stripe' ),
			'GIP' => esc_html__( 'Gibraltar Pound', 'stripe' ),
			'GMD' => esc_html__( 'Gambian Dalasi', 'stripe' ),
			'GNF' => esc_html__( 'Guinean Franc', 'stripe' ), // NON AMEX
			'GTQ' => esc_html__( 'Guatemalan Quetzal', 'stripe' ), // NON AMEX
			'GYD' => esc_html__( 'Guyanese Dollar', 'stripe' ),
			'HKD' => esc_html__( 'Hong Kong Dollar', 'stripe' ),
			'HNL' => esc_html__( 'Honduran Lempira', 'stripe' ), // NON AMEX
			'HRK' => esc_html__( 'Croatian Kuna', 'stripe' ),
			'HTG' => esc_html__( 'Haitian Gourde', 'stripe' ),
			'HUF' => esc_html__( 'Hungarian Forint', 'stripe' ), // NON AMEX
			'IDR' => esc_html__( 'Indonesian Rupiah', 'stripe' ),
			'ILS' => esc_html__( 'Israeli New Sheqel', 'stripe' ),
			'INR' => esc_html__( 'Indian Rupee', 'stripe' ), // NON AMEX
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
			'LAK' => esc_html__( 'Lao Kip', 'stripe' ), // NON AMEX
			'LBP' => esc_html__( 'Lebanese Pound', 'stripe' ),
			'LKR' => esc_html__( 'Sri Lankan Rupee', 'stripe' ),
			'LRD' => esc_html__( 'Liberian Dollar', 'stripe' ),
			'LSL' => esc_html__( 'Lesotho Loti', 'stripe' ),
			'MAD' => esc_html__( 'Moroccan Dirham', 'stripe' ),
			'MDL' => esc_html__( 'Moldovan Leu', 'stripe' ),
			'MGA' => esc_html__( 'Malagasy Ariary', 'stripe' ),
			'MKD' => esc_html__( 'Macedonian Denar', 'stripe' ),
			'MNT' => esc_html__( 'Mongolian Tögrög', 'stripe' ),
			'MOP' => esc_html__( 'Macanese Pataca', 'stripe' ),
			'MRO' => esc_html__( 'Mauritanian Ouguiya', 'stripe' ),
			'MUR' => esc_html__( 'Mauritian Rupee', 'stripe' ), // NON AMEX
			'MVR' => esc_html__( 'Maldivian Rufiyaa', 'stripe' ),
			'MWK' => esc_html__( 'Malawian Kwacha', 'stripe' ),
			'MXN' => esc_html__( 'Mexican Peso', 'stripe' ), // NON AMEX
			'MYR' => esc_html__( 'Malaysian Ringgit', 'stripe' ),
			'MZN' => esc_html__( 'Mozambican Metical', 'stripe' ),
			'NAD' => esc_html__( 'Namibian Dollar', 'stripe' ),
			'NGN' => esc_html__( 'Nigerian Naira', 'stripe' ),
			'NIO' => esc_html__( 'Nicaraguan Córdoba', 'stripe' ), // NON AMEX
			'NOK' => esc_html__( 'Norwegian Krone', 'stripe' ),
			'NPR' => esc_html__( 'Nepalese Rupee', 'stripe' ),
			'NZD' => esc_html__( 'New Zealand Dollar', 'stripe' ),
			'PAB' => esc_html__( 'Panamanian Balboa', 'stripe' ), // NON AMEX
			'PEN' => esc_html__( 'Peruvian Nuevo Sol', 'stripe' ), // NON AMEX
			'PGK' => esc_html__( 'Papua New Guinean Kina', 'stripe' ),
			'PHP' => esc_html__( 'Philippine Peso', 'stripe' ),
			'PKR' => esc_html__( 'Pakistani Rupee', 'stripe' ),
			'PLN' => esc_html__( 'Polish Złoty', 'stripe' ),
			'PYG' => esc_html__( 'Paraguayan Guaraní', 'stripe' ), // NON AMEX
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
			'SHP' => esc_html__( 'Saint Helenian Pound', 'stripe' ), // NON AMEX
			'SLL' => esc_html__( 'Sierra Leonean Leone', 'stripe' ),
			'SOS' => esc_html__( 'Somali Shilling', 'stripe' ),
			'SRD' => esc_html__( 'Surinamese Dollar', 'stripe' ), // NON AMEX
			'STD' => esc_html__( 'São Tomé and Príncipe Dobra', 'stripe' ),
			'SVC' => esc_html__( 'Salvadoran Colón', 'stripe' ), // NON AMEX
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
			'UYU' => esc_html__( 'Uruguayan Peso', 'stripe' ), // NON AMEX
			'UZS' => esc_html__( 'Uzbekistani Som', 'stripe' ),
			'VND' => esc_html__( 'Vietnamese Đồng', 'stripe' ),
			'VUV' => esc_html__( 'Vanuatu Vatu', 'stripe' ),
			'WST' => esc_html__( 'Samoan Tala', 'stripe' ),
			'XAF' => esc_html__( 'Central African Cfa Franc', 'stripe' ),
			'XCD' => esc_html__( 'East Caribbean Dollar', 'stripe' ),
			'XOF' => esc_html__( 'West African Cfa Franc', 'stripe' ), // NON AMEX
			'XPF' => esc_html__( 'Cfp Franc', 'stripe' ), // NON AMEX
			'YER' => esc_html__( 'Yemeni Rial', 'stripe' ),
			'ZAR' => esc_html__( 'South African Rand', 'stripe' ),
			'ZMW' => esc_html__( 'Zambian Kwacha', 'stripe' ),
		);
	}
}
