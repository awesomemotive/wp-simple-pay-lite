<?php
/**
 * Internationalization: Functions
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats an amount for a specificed currency.
 *
 * @since 3.0.0
 * @since 4.1.0 Expects amount to be in lowest possible unit. Currency is encouraged.
 *
 * @param string|float|int $amount Amount to format.
 * @param string           $currency Currency code.
 * @param bool             $show_symbol Show currency symbol. Default true.
 * @return string
 */
function simpay_format_currency( $amount, $currency = '', $show_symbol = true ) {
	if ( empty( $currency ) ) {
		$currency = strtolower( simpay_get_setting( 'currency', 'USD' ) );
	}

	$symbol          = simpay_get_currency_symbol( $currency );
	$position        = simpay_get_currency_position();
	$is_zero_decimal = simpay_is_zero_decimal( $currency );

	$amount = $is_zero_decimal
		? $amount
		: simpay_convert_amount_to_dollars( $amount );

	$amount = number_format(
		floatval( $amount ),
		simpay_get_decimal_places( $currency ),
		simpay_get_decimal_separator(),
		simpay_get_thousand_separator()
	);

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
 * Returns a list of zero decimal currencies.
 *
 * @link https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
 *
 * @since 3.0.0
 *
 * @return array
 */
function simpay_get_zero_decimal_currencies() {
	$currencies = array(
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
	);

	/**
	 * Filters the currencies that are considered zero-decimal.
	 *
	 * @since 3.0.0
	 *
	 * @param array List of currencies.
	 */
	$currencies = apply_filters( 'simpay_zero_decimal_currencies', $currencies );

	return $currencies;
}

/**
 * Checks if a currency is set to a zero decimal currency or not.
 *
 * @since 3.0.0
 * @since 4.1.0 Supplying a specific currency code is recommended.
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
 * Returns the minimum amount that can be processed by Stripe for a currency in
 * the currency's lowest decimal.
 *
 * @since 4.1.0
 *
 * @param string $currency Currency code.
 * @return int
 */
function simpay_get_currency_minimum( $currency ) {
	$minimum  = 100;
	$currency = strtolower( $currency );

	/**
	 * Filters the minimum amount (in lowest possible decimal) for a specific currency.
	 *
	 * @since 4.1.0
	 *
	 * @param string $currency Currency code.
	 */
	$minimum = apply_filters( 'simpay_get_currency_minimum', $minimum, $currency );

	return $minimum;
}