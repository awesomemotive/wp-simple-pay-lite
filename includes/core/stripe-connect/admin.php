<?php
/**
 * Admin functionality for managing Stripe Connect.
 *
 * @since 3.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output Connect CSS on Keys page.
 *
 * @since 3.5.0
 */
add_action( 'simpay_admin_page_settings_keys_start', 'simpay_stripe_connect_button_css' );

/**
 * Listens for Stripe Connect completion requests and saves the Stripe API keys.
 *
 * @since 2.6.14
 */
function simpay_process_gateway_connect_completion() {
	// Current user cannot handle this request.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Do not need to handle this request, bail.
	if (
		! isset( $_GET['wpsp_gateway_connect_completion'] ) ||
		'stripe_connect' !== $_GET['wpsp_gateway_connect_completion'] ||
		! isset( $_GET['state'] )
	) {
		return;
	}

	// Unable to redirect, bail.
	if ( headers_sent() ) {
		return;
	}

	$wpsp_credentials_url = add_query_arg(
		array(
			'live_mode'         => (int) ! simpay_is_test_mode(),
			'state'             => sanitize_text_field( $_GET['state'] ),
			'customer_site_url' => admin_url( 'admin.php?page=simpay_settings&tab=keys' ),
		),
		'https://wpsimplepay.com/?wpsp_gateway_connect_credentials=stripe_connect'
	);

	$response = wp_remote_get( esc_url_raw( $wpsp_credentials_url ) );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$message = '<p>' . sprintf( __( 'There was an error getting your Stripe credentials. Please <a href="%s">try again</a>. If you continue to have this problem, please contact support.', 'stripe' ), esc_url( admin_url( 'admin.php?page=simpay_settings&tab=keys' ) ) ) . '</p>';

		wp_die( $message );
	}

	$data     = json_decode( $response['body'], true )['data'];
	$settings = get_option( 'simpay_settings_keys' );

	if ( simpay_is_test_mode() ) {
		$settings['test_keys']['secret_key']      = sanitize_text_field( $data['secret_key'] );
		$settings['test_keys']['publishable_key'] = sanitize_text_field( $data['publishable_key'] );
	} else {
		$settings['live_keys']['secret_key']      = sanitize_text_field( $data['secret_key'] );
		$settings['live_keys']['publishable_key'] = sanitize_text_field( $data['publishable_key'] );
	}

	update_option( 'simpay_settings_keys', $settings );
	update_option( 'simpay_stripe_connect_account_id', sanitize_text_field( $data['stripe_user_id'] ) );

	wp_redirect( esc_url_raw( admin_url( 'admin.php?page=simpay_settings&tab=keys' ) ) );

	exit;
}
add_action( 'admin_init', 'simpay_process_gateway_connect_completion' );

/**
 * Listen for a disconnect URL.
 *
 * Clears out the `simpay_stripe_connect_account_id` and the API keys.
 *
 * @since 3.5.0
 */
function simpay_process_stripe_disconnect() {
	// Current user cannot handle this request.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Do not need to handle this request, bail.
	if (
		! ( isset( $_GET['page'] ) && 'simpay_settings' === $_GET['page'] ) ||
		! isset( $_GET['simpay-stripe-disconnect'] )
	) {
		return;
	}

	$test = simpay_is_test_mode();
	$keys = get_option( 'simpay_settings_keys' );

	// Only clear keys for current mode.
	if ( $test ) {
		$keys['test_keys'] = array(
			'secret_key'      => '',
			'publishable_key' => '',
		);
	} else {
		$keys['live_keys'] = array(
			'secret_key'      => '',
			'publishable_key' => '',
		);
	}

	update_option( 'simpay_settings_keys', $keys );
	update_option( 'simpay_stripe_connect_account_id', false );

	$redirect = add_query_arg(
		array(
			'page' => 'simpay_settings',
			'tab'  => 'keys',
		),
		admin_url( 'admin.php' )
	);

	return wp_redirect( esc_url_raw( $redirect ) );
}
add_action( 'admin_init', 'simpay_process_stripe_disconnect' );
