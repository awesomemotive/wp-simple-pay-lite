<?php

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Session {

	/**
	 * Session constructor.
	 */
	public function __construct() {

		if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', 'simpay_wp_session' );
		}
	}

	/**
	 * Add an item to our session
	 *
	 * @param $id
	 * @param $value
	 */
	public static function add( $id, $value ) {
		global $wp_session;

		$wp_session[ $id ] = $value;
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

		global $wp_session;

		if ( isset( $wp_session[ $id ] ) && ! empty( $wp_session[ $id ] ) ) {
			return $wp_session[ $id ];
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

		global $wp_session;

		$id = sanitize_key( $id );

		if ( is_array( $wp_session['simpay_errors'] ) ) {
			$wp_session['simpay_errors'] = array_merge( $wp_session['simpay_errors'], array( $id => $error ) );
		} else {
			$wp_session['simpay_errors'] = array( $id => $error );
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

		global $wp_session;

		$id = sanitize_key( $id );

		if ( isset( $wp_session['simpay_errors'][ $id ] ) ) {
			return $wp_session['simpay_errors'][ $id ];
		}

		return '';
	}

	/**
	 * Check if we have errors or not
	 *
	 * @return bool
	 */
	public static function has_errors() {
		global $wp_session;

		return isset( $wp_session['simpay_errors'] ) && ! empty( $wp_session['simpay_errors'] ) ? true : false;
	}

	/**
	 * Print all the errors found in HTML
	 *
	 * @return string
	 */
	public static function print_all_errors() {

		global $wp_session;

		// Check if simpay_errors is set and if it is then we have errors so let's display them all
		if ( self::has_errors() ) {

			$html = '<p><strong>' . esc_html__( 'Error Details:', 'stripe' ) . '</strong></p>' . "\n";

			foreach ( $wp_session['simpay_errors'] as $error => $message ) {
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
		global $wp_session;

		unset( $wp_session['simpay_errors'] );
	}

	/**
	 * Clear out a specific item from our session
	 *
	 * @param $id
	 */
	public static function clear( $id ) {

		global $wp_session;

		if ( self::get( $id ) ) {
			unset( $wp_session[ $id ] );
		}
	}

	/**
	 * Completely clear our session
	 */
	public static function clear_all() {

		global $wp_session;

		// Our custom list of sessions to remove
		$wp_session['customer_id']                 = '';
		$wp_session['charge_id']                   = '';
		$wp_session['simpay_form']                 = '';
		unset( $wp_session['simpay_errors'] );

		do_action( 'simpay_clear_sessions' );
	}
}
