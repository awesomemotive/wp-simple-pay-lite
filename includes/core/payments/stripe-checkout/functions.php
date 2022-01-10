<?php
/**
 * Stripe Checkout
 *
 * @package SimplePay\Core\Payments\Stripe_Checkout
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Stripe_Checkout;

use SimplePay\Core\i18n;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves the payment types available for Stripe Checkout.
 *
 * @since 3.6.0
 *
 * @return array
 */
function get_available_payment_method_types() {
	$payment_method_types = array(
		'card',
		'ideal',
	);

	/**
	 * Filters the payment types available for Stripe Checkout.
	 *
	 * @since 3.6.0
	 *
	 * @param array $payment_types Available payment types.
	 */
	$payment_method_types = apply_filters(
		'simpay_stripe_checkout_available_payment_method_types',
		$payment_method_types
	);

	return $payment_method_types;
}

/**
 * Retrieves a list of countries that support Shipping Address collection.
 *
 * @since 3.8.0
 *
 * @return array List of country codes.
 */
function get_available_shipping_address_countries() {
	// Built in countries.
	$countries = i18n\get_countries();

	// Remove unsupported countries.
	unset( $countries['AS'] );
	unset( $countries['CX'] );
	unset( $countries['CC'] );
	unset( $countries['CU'] );
	unset( $countries['TP'] );
	unset( $countries['HM'] );
	unset( $countries['IR'] );
	unset( $countries['MH'] );
	unset( $countries['FM'] );
	unset( $countries['AN'] );
	unset( $countries['NF'] );
	unset( $countries['KP'] );
	unset( $countries['MP'] );
	unset( $countries['PW'] );
	unset( $countries['SD'] );
	unset( $countries['SY'] );
	unset( $countries['UM'] );
	unset( $countries['VI'] );

	return array_keys( $countries );
}
