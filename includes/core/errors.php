<?php

/**
 * Error Tracking
 *
 * Handles the setting & retrieving of errors.
 * In turn uses session variables through the WP Simple Pay session class.
 *
 * Also using Give & EDD error tracking logic.
 */

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Errors {

	/**
	 * Get Errors
	 *
	 * Retrieves all error messages stored during the checkout process.
	 * If errors exist, they are returned.
	 *
	 * @since 3.0
	 * @uses  Session::get()
	 *
	 * @return array|bool array if errors are present, false if none found.
	 */
	public static function get() {

		return SimplePay()->session->get( 'simpay_errors' );
	}

	/**
	 * Set Error
	 *
	 * Stores an error in a session var.
	 *
	 * @since 3.0
	 * @uses  Session::set()
	 *
	 * @param int    $error_id      ID of the error being set.
	 * @param string $error_message Message to store with the error.
	 *
	 * @return void
	 */
	public static function set( $error_id, $error_message ) {

		$errors = self::get();

		if ( ! $errors ) {
			$errors = array();
		}

		$errors[ $error_id ] = $error_message;

		SimplePay()->session->set( 'simpay_errors', $errors );
	}

	/**
	 * Clears all stored errors.
	 *
	 * @since 3.0
	 * @uses  Session::set()
	 *
	 * @return void
	 */
	public static function clear_errors() {

		SimplePay()->session->set( 'simpay_errors', null );
	}

	/**
	 * Removes (unsets) a stored error.
	 *
	 * @since 3.0
	 * @uses  Session::set()
	 *
	 * @param int $error_id ID of the error being set.
	 *
	 * @return void
	 */
	public static function unset_error( $error_id ) {

		$errors = self::get();

		if ( $errors ) {

			if ( isset( $errors[ $error_id ] ) ) {
				unset( $errors[ $error_id ] );
			}

			SimplePay()->session->set( 'simpay_errors', $errors );
		}
	}

	/**
	 * Get errors in HTML format for rendering to browser.
	 *
	 * If errors exist, they are returned.
	 * Otherwise, an empty string is returned.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function get_error_html() {

		$errors = self::get();

		if ( $errors ) {

			$html = '<div class="simpay-errors">';

			// Loop error codes and display errors
			foreach ( $errors as $error_id => $error_msg ) {

				$html .= '<p class="simpay-error-item" id="simpay-error-' . $error_id . '"><strong>' . __( 'Error', 'stripe' ) . '</strong>: ' . esc_html( $error_msg ) . '</p>';
			}

			$html .= '</div>' . "\n";

			return $html;
		}
		else {
			return '';
		}
	}
}
