<?php
/**
 * Admin functionality for managing Stripe Connect.
 *
 * @since 3.4.0
 *
 * @todo Namespace this file.
 */

use SimplePay\Core\Payments\Stripe_API;

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

	/**
	 * Allow further processing after connecting a Stripe account.
	 *
	 * @since 3.6.0
	 *
	 * @param array $data Stripe response data.
	 */
	do_action( 'simpay_stripe_account_connected', $data );

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

	$keys['test_keys'] = array(
		'secret_key'      => '',
		'publishable_key' => '',
	);

	$keys['live_keys'] = array(
		'secret_key'      => '',
		'publishable_key' => '',
	);

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

/**
 * Responds to the the `simpay_stripe_connect_account_information` AJAX action.
 *
 * @since 3.6.0
 */
function simpay_stripe_connect_account_information() {
	$unknown_error = array(
		'message' => esc_html__( 'Unable to retrieve account information.', 'stripe' ),
	);

	$dev_account_error = array(
		'message' => (
			esc_html__( 'You are currently connected to an unsaved Stripe account.', 'stripe' ) . ' ' .
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate */
			sprintf(
				esc_html__( 'Please %1$ssave your account in Stripe%2$s to see more information.', 'stripe' ),
				'<a href="https://dashboard.stripe.com/account/details" target="_blank" rel="noopener noreferrer">',
				'</a>'
			) . '<br />' .
			'<strong>' . esc_html__( 'You will not be able to reconnect to this account unless it is saved.', 'stripe' ) . '</strong>'
		),
		'actions'  => 'simpay-stripe-unactivated-account-actions',
	);

	if ( ! wp_verify_nonce( $_POST['nonce'], 'simpay-stripe-connect-information' ) ) {
		return wp_send_json_error( $unknown_error );
	}

	if ( ! isset( $_POST['account_id'] ) ) {
		return wp_send_json_error( $unknown_error );
	}

	$account_id = sanitize_text_field( $_POST['account_id'] );

	// Show status of Stripe Connect.
	if ( '' !== $account_id ) {
		try {
			// @todo remove when API wrapper throws errors.
			Stripe_API::set_app_info();
			Stripe_API::set_api_key();
			$account = \Stripe\Account::retrieve( $account_id );

			if ( ! $account->email ) {
				return wp_send_json_success( $dev_account_error );
			}

			$display_name = isset( $account->display_name ) ? $account->display_name : '';

			if ( empty( $display_name ) ) {
				if ( isset( $account->settings ) ) {
					$display_name = $account->settings->dashboard->display_name;
				}
			}

			if ( ! empty( $display_name ) ) {
				$display_name = '<strong>' . $display_name . '</strong><br/ >';
			}

			return wp_send_json_success( array(
				'message' => $display_name . $account->email . ' &mdash; ' . esc_html( 'Administrator (Owner)', 'simple-pay' ),
				'actions' => 'simpay-stripe-activated-account-actions',
			) );
		} catch( \Stripe\Error\Authentication $e ) {
			return wp_send_json_error( array(
				'message' => esc_html__( 'Unable to validate your Stripe Account with the API keys provided. If you have manually modified these values after connecting your account, please reconnect below or update your API keys.', 'stripe' ),
				'actions' => 'simpay-stripe-auth-error-account-actions',
			) );
		} catch( \Exception $e ) {
			return wp_send_json_error( $unknown_error );
		}
	} else {
		$mode = simpay_is_test_mode() ? __( 'test', 'stripe' ) : __( 'live', 'stripe' );
		$connect = esc_html__( 'It is highly recommended to Connect with Stripe for easier setup and improved security.', 'stripe' );

		try {
			// @todo remove when API wrapper throws errors.
			Stripe_API::set_api_key();
			$balance = \Stripe\Balance::retrieve();

			return wp_send_json_success( array(
				'message' => (
					sprintf(
						/* translators: %1$s Stripe payment mode.*/
						__( 'Your manually managed %1$s mode API keys are valid.', 'stripe' ),
						'<strong>' . $mode . '</strong>'
					) . '<br />' .
					$connect
				),
			) );
		} catch( \Exception $e ) {
			return wp_send_json_error( array(
				'message' => (
					'<span style="color: red;">' .
						sprintf(
							/* translators: %1$s Stripe payment mode.*/
							__( 'Your manually managed %1$s mode API keys are invalid.', 'stripe' ),
							'<strong>' . $mode . '</strong>'
						)
					. '</span><br />' . 
					$connect
				),
			) );
		}
	}
}
add_action( 'wp_ajax_simpay_stripe_connect_account_information', 'simpay_stripe_connect_account_information' );
