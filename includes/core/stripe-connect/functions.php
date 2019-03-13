<?php
/**
 * General functionality for managing Stripe Connect.
 *
 * @since 3.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve stored Stripe Account ID (generated from Stripe Connect).
 *
 * @since unknown
 *
 * @return string $account_id Stripe Account ID.
 */
function simpay_get_account_id() {
	global $simpay_form;

	$test_mode = simpay_is_test_mode();

	if ( ! empty( $simpay_form ) ) {
		return $simpay_form->account_id;
	}
	
	$account_id = get_option( 'simpay_stripe_connect_account_id', false );

	if ( ! $account_id ) {
		return false;
	}

	return trim( $account_id );
}

/**
 * Determine if the current site can manually manage Stripe Keys.
 *
 * If a Stripe Account ID exists, the keys cannot be set manually.
 *
 * @since 3.4.0
 *
 * @return bool
 */
function simpay_can_site_manage_stripe_keys() {
	$can = false;

	// No connection has been made, and keys are already set, let management continue.
	if ( ! simpay_get_account_id() && simpay_get_secret_key() ) {
		$can = true;
	}

	/**
	 * Filter the ability to manually manage Stripe keys.
	 *
	 * @since 3.4.0
	 *
	 * @param bool $can If the keys can be managed.
	 */
	$can = apply_filters( 'simpay_can_site_manage_stripe_keys', $can );

	return $can;
}
