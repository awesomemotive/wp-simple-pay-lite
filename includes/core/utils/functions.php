<?php
/**
 * Utils: functions
 *
 * @package SimplePay\Core\Utils
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.5
 */

namespace SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns markup that denotes the link is external.
 *
 * @since 4.0.0
 *
 * @return string
 */
function get_external_link_markup() {
	return sprintf(
		'<span class="screen-reader-text">%s</span><span aria-hidden="true" class="dashicons dashicons-external"></span>',
		' ' . esc_html__( '(opens in a new tab)', 'stripe' )
	);
}

/**
 * Retrieves the IP address of the current visitor.
 *
 * @since 3.9.5
 *
 * @return string $ip User's IP address
 */
function get_current_ip_address() {
	$ip = false;

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// Check ip from share internet.
		$ip = filter_var( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ), FILTER_VALIDATE_IP );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// To check ip is pass from proxy.
		// Can include more than 1 ip, first is the public one.

		// WPCS: sanitization ok.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ips = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );

		if ( is_array( $ips ) ) {
			$ip = filter_var( $ips[0], FILTER_VALIDATE_IP );
		}
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP );
	}

	$ip = false !== $ip ? $ip : '127.0.0.1';

	// Fix potential CSV returned from $_SERVER variables.
	$ip_array = explode( ',', $ip );
	$ip_array = array_map( 'trim', $ip_array );

	/**
	 * Filters the current visitor's IP address.
	 *
	 * @since 3.9.5
	 *
	 * @param string $ip
	 */
	return apply_filters( 'simpay_get_current_ip_address', $ip_array[0] );
}
