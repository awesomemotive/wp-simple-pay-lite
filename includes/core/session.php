<?php

/**
 * WP Simple Pay Session Class
 *
 * This is a wrapper class for WP Native PHP Sessions by Pantheon
 * (https://github.com/pantheon-systems/wp-native-php-sessions) and handles storage of sessions within WP Simple Pay.
 */

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Session {

	/**
	 * Session index prefix
	 *
	 * @access private
	 *
	 * @var    string
	 */
	private static $prefix = 'simpay_';

	/**
	 * Add an item to our session
	 *
	 * @param $id
	 * @param $value
	 */
	public static function add( $id, $value ) {

		$_SESSION[ self::$prefix . $id ] = $value;
	}

	/**
	 * Get a specific item from our session
	 *
	 * @param        $id
	 * @param string $default
	 *
	 * @return bool|string
	 */
	public static function get( $id, $default = '' ) {

		if ( isset( $_SESSION[ self::$prefix . $id ] ) && ! empty( $_SESSION[ self::$prefix . $id ] ) ) {
			return $_SESSION[ self::$prefix . $id ];
		}

		if ( ! empty( $default ) ) {
			return $default;
		}

		return false;
	}

	/**
	 * Add an error to our session
	 *
	 * @param $id
	 * @param $error
	 */
	public static function add_error( $id, $error ) {

		$id = sanitize_key( $id );

		if ( is_array( $_SESSION[ self::$prefix . 'errors' ] ) ) {
			$_SESSION[ self::$prefix . 'errors' ] = array_merge( $_SESSION[ self::$prefix . 'errors' ], array( $id => $error ) );
		} else {
			$_SESSION[ self::$prefix . 'errors' ] = array( $id => $error );
		}
	}

	/**
	 * Get a specific error from our session
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public static function get_error( $id ) {

		$id = sanitize_key( $id );

		if ( isset( $_SESSION[ self::$prefix . 'errors' ][ $id ] ) ) {
			return $_SESSION[ self::$prefix . 'errors' ][ $id ];
		} else {
			return '';
		}
	}

	/**
	 * Check if we have errors or not
	 *
	 * @return bool
	 */
	public static function has_errors() {

		return isset( $_SESSION[ self::$prefix . 'errors' ] ) && ! empty( $_SESSION[ self::$prefix . 'errors' ] ) ? true : false;
	}

	/**
	 * Print all the errors found in HTML
	 *
	 * @return string
	 */
	public static function print_all_errors() {

		// Check if simpay_errors is set and if it is then we have errors so let's display them all
		if ( self::has_errors() ) {

			$html = '<p><strong>' . esc_html__( 'Error Details:', 'stripe' ) . '</strong></p>' . "\n";

			foreach ( $_SESSION[ self::$prefix . 'errors' ] as $error => $message ) {
				$html .= '<p class="simpay-error-item ' . esc_attr( $error ) . '">' . esc_html( $message ) . '</p>' . "\n";
			}

			return $html;
		}

		return '';
	}

	/**
	 * Clear all the error session values
	 */
	public static function clear_errors() {

		unset( $_SESSION[ self::$prefix . 'errors' ] );
	}

	/**
	 * Clear out a specific item from our session
	 *
	 * @param $id
	 */
	public static function clear( $id ) {

		if ( self::get( $id ) ) {
			unset( $_SESSION[ self::$prefix . $id ] );
		}
	}

	/**
	 * Completely clear our session
	 */
	public static function clear_all() {

		// Our custom list of sessions to remove
		$_SESSION[ self::$prefix . 'customer_id'] = '';
		$_SESSION[ self::$prefix . 'charge_id']   = '';
		$_SESSION[ self::$prefix . 'simpay_form'] = '';

		self::clear_errors();

		do_action( 'simpay_clear_sessions' );
	}
}
