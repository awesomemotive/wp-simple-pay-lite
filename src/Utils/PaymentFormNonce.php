<?php
/**
 * Utils: Payment form nonce
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.5
 */

namespace SimplePay\Core\Utils;

/**
 * PaymentFormNonce class.
 *
 * Single source of truth for minting and verifying the nonce that protects
 * payment form requests (the payment REST routes and the File Upload AJAX
 * handler).
 *
 * Server-rendered forms emit a per-form `simpay_payment_form_{form_id}` nonce
 * under WordPress' default `nonce_life`. The form-data REST endpoint, used to
 * hydrate forms on cached pages, mints the same action under a shortened
 * 1-hour `nonce_life` (valid for 30-60 minutes). The legacy generic `simpay_payment_form` action is
 * still accepted so older cached HTML and third-party integrations keep
 * working during the transition.
 *
 * @since 4.17.5
 */
class PaymentFormNonce {

	/**
	 * Legacy generic nonce action, accepted as a fallback.
	 *
	 * @since 4.17.5
	 * @var string
	 */
	const LEGACY_ACTION = 'simpay_payment_form';

	/**
	 * REST error code returned when the payment form nonce is missing or
	 * invalid. The frontend refreshes the nonce and retries on this code.
	 *
	 * @since 4.17.5
	 * @var string
	 */
	const ERROR_CODE = 'simpay_invalid_form_nonce';

	/**
	 * Returns the per-form nonce action.
	 *
	 * @since 4.17.5
	 *
	 * @param int $form_id Payment form ID.
	 * @return string
	 */
	public static function get_action( $form_id ) {
		return self::LEGACY_ACTION . '_' . absint( $form_id );
	}

	/**
	 * Creates a fresh per-form nonce with a shortened lifetime.
	 *
	 * WordPress nonces verify for between half and all of `nonce_life`, so
	 * with a 1-hour `nonce_life` the nonce is valid for 30-60 minutes after
	 * it is minted. The frontend fetches a fresh one when a request is
	 * rejected for an expired nonce.
	 *
	 * The `nonce_life` filter is scoped narrowly around `wp_create_nonce()`
	 * so the 1-hour TTL only applies to this nonce and not other nonces
	 * created elsewhere during the same request.
	 *
	 * @since 4.17.5
	 *
	 * @param int $form_id Payment form ID.
	 * @return string Nonce for the `simpay_payment_form_{form_id}` action.
	 */
	public static function create_short_lived( $form_id ) {
		add_filter( 'nonce_life', array( __CLASS__, 'get_short_lifetime' ) );
		$nonce = wp_create_nonce( self::get_action( $form_id ) );
		remove_filter( 'nonce_life', array( __CLASS__, 'get_short_lifetime' ) );

		return $nonce;
	}

	/**
	 * Verifies a payment form nonce.
	 *
	 * Tries the per-form action first (when a form ID is known), then the
	 * legacy generic action. Each action is checked under both the default
	 * and the shortened TTL, since a nonce created under one TTL does not
	 * verify under the other.
	 *
	 * @since 4.17.5
	 *
	 * @param string $nonce   Nonce value.
	 * @param int    $form_id Payment form ID, or 0 when unknown.
	 * @return bool True if the nonce is valid.
	 */
	public static function verify( $nonce, $form_id ) {
		if ( '' === $nonce ) {
			return false;
		}

		if ( $form_id > 0 && self::verify_action( $nonce, self::get_action( $form_id ) ) ) {
			return true;
		}

		return self::verify_action( $nonce, self::LEGACY_ACTION );
	}

	/**
	 * Returns the shortened nonce lifetime.
	 *
	 * Used as a `nonce_life` filter callback.
	 *
	 * @since 4.17.5
	 *
	 * @return int
	 */
	public static function get_short_lifetime() {
		return HOUR_IN_SECONDS;
	}

	/**
	 * Verifies a nonce against an action under either TTL.
	 *
	 * @since 4.17.5
	 *
	 * @param string $nonce  Nonce value.
	 * @param string $action Nonce action.
	 * @return bool
	 */
	private static function verify_action( $nonce, $action ) {
		if ( wp_verify_nonce( $nonce, $action ) ) {
			return true;
		}

		add_filter( 'nonce_life', array( __CLASS__, 'get_short_lifetime' ) );
		$verified = (bool) wp_verify_nonce( $nonce, $action );
		remove_filter( 'nonce_life', array( __CLASS__, 'get_short_lifetime' ) );

		return $verified;
	}
}
