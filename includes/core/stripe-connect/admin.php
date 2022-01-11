<?php
/**
 * Stripe Connect: Admin
 *
 * @package SimplePay\Core\Stripe_Connect
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.4.0
 */

use SimplePay\Core\API;
use SimplePay\Core\License\License;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			'customer_site_url' => Settings\get_url(
				array(
					'section'    => 'stripe',
					'subsection' => 'account',
				)
			),
		),
		'https://wpsimplepay.com/?wpsp_gateway_connect_credentials=stripe_connect'
	);

	$response = wp_remote_get( esc_url_raw( $wpsp_credentials_url ) );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$stripe_account_settings_url = Settings\get_url( array(
			'section'    => 'stripe',
			'subsection' => 'account',
		) );

		$message = wpautop(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'There was an error getting your Stripe credentials. Please %1$stry again%2$s. If you continue to have this problem, please contact support.',
					'stripe'
				),
				'<a href="' . esc_url( $stripe_account_settings_url ) . '">',
				'</a>'
			)
		);

		wp_die( $message );
	}

	$data = json_decode( $response['body'], true )['data'];

	$prefix = simpay_is_test_mode()
		? 'test'
		: 'live';

	simpay_update_setting(
		$prefix . '_secret_key',
		sanitize_text_field( $data['secret_key'] )
	);

	simpay_update_setting(
		$prefix . '_publishable_key',
		sanitize_text_field( $data['publishable_key'] )
	);

	update_option(
		'simpay_stripe_connect_account_id',
		sanitize_text_field( $data['stripe_user_id'] )
	);

	// @todo Migrate to use container system.
	$license = new License( get_option( 'simpay_license_key', '' ) );

	if ( $license instanceof License ) {
		$type = $license->is_lite() ? 'lite' : 'pro';
		update_option( 'simpay_stripe_connect_type', $type );
	}

	/**
	 * Allow further processing after connecting a Stripe account.
	 *
	 * @since 3.6.0
	 *
	 * @param array $data Stripe response data.
	 */
	do_action( 'simpay_stripe_account_connected', $data );

	$stripe_account_settings_url = Settings\get_url( array(
		'section'    => 'stripe',
		'subsection' => 'account',
	) );

	wp_redirect( esc_url_raw( $stripe_account_settings_url ) );

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
	// Do not need to handle this request, bail.
	if (
		! ( isset( $_GET['page'] ) && 'simpay_settings' === $_GET['page'] ) ||
		! isset( $_GET['simpay-stripe-disconnect'] )
	) {
		return;
	}

	// Current user cannot handle this request.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_GET['_wpnonce'] ) ) {
		return;
	}

	// Invalid nonce, bail.
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'simpay-stripe-connect-disconnect' ) ) {
		return;
	}

	// Clear keys.
	simpay_update_setting( 'live_secret_key', '' );
	simpay_update_setting( 'test_secret_key', '' );
	simpay_update_setting( 'live_publishable_key', '' );
	simpay_update_setting( 'test_publishable_key', '' );

	// Clear account ID.
	update_option( 'simpay_stripe_connect_account_id', false );
	update_option( 'simpay_stripe_connect_type', false );

	$redirect = Settings\get_url(
		array(
			'section'    => 'stripe',
			'subsection' => 'account',
		)
	);

	return wp_safe_redirect( $redirect );
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

	if ( ! current_user_can( 'manage_options' ) ) {
		return wp_send_json_error( $unknown_error );
	}

	if ( ! wp_verify_nonce( $_POST['nonce'], 'simpay-stripe-connect-information' ) ) {
		return wp_send_json_error( $unknown_error );
	}

	if ( ! isset( $_POST['account_id'] ) ) {
		return wp_send_json_error( $unknown_error );
	}

	$mode = simpay_is_test_mode()
		? __( 'test', 'stripe' )
		: __( 'live', 'stripe' );

	$connect = sprintf(
		(
			'<div style="margin-top: 8px;"><a href="%s" class="wpsp-stripe-connect"><span>' .
			__( 'Connect with Stripe', 'stripe' ) .
			'</span></a></div>'
		),
		esc_url( simpay_get_stripe_connect_url() )
	);

	$access_string = class_exists( 'SimplePay\Pro\SimplePayPro', false )
		? __(
			'You cannot manage this account in Stripe to configure features such as Subscriptions, Webhooks, or Coupons.',
			'stripe'
		)
		: __(
			'You cannot manage this account in Stripe.',
			'stripe'
		);

	$dev_account_error = array(
		'message' => (
			sprintf(
				/* translators: %1$s Opening strong tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'You are currently connected to a %1$stemporary%2$s Stripe account, which can only be used for testing purposes.',
					'stripe'
				),
				'<strong>',
				'</strong>'
			) . ' ' . $access_string
		),
		'actions' => 'simpay-stripe-unactivated-account-actions',
	);

	$account_id = sanitize_text_field( $_POST['account_id'] );

	// Look for manually managed API key mishaps.
	$secret_key      = simpay_get_secret_key();
	$publishable_key = simpay_get_publishable_key();
	$key_errors      = new WP_Error();

	// Publishable Key being used for Secret Key.
	if ( 'pk_' === substr( $secret_key, 0, 3 ) ) {
		$key_errors->add(
			'simpay_sk_mismatch',
			__(
				'Invalid Secret Key. Secret Key should begin with <code>sk_</code>.',
				'stripe'
			)
		);
	}

	// Secret Key being used for Publishable Key.
	if ( 'sk_' === substr( $publishable_key, 0, 3 ) ) {
		$key_errors->add(
			'simpay_pk_mismatch',
			__(
				'Invalid Publishable Key. Publishable Key should begin with <code>pk_</code>.',
				'stripe'
			)
		);
	}

	if ( simpay_is_test_mode() ) {
		// Live Mode Publishable Key used in Test Mode Publishable Key.
		if ( 'pk_live_' === substr( $publishable_key, 0, 8 ) ) {
			$key_errors->add(
				'simpay_pk_mode_mismatch',
				__(
					'Invalid Publishable Key for current mode. Publishable Key should begin with <code>pk_test_</code>.',
					'stripe'
				)
			);
		}

		// Live Mode Secret Key used in Test Mode Secret Key.
		if ( 'sk_live_' === substr( $secret_key, 0, 8 ) ) {
			$key_errors->add(
				'simpay_sk_mode_mismatch',
				__(
					'Invalid Secret Key for current mode. Secret Key should begin with <code>sk_test_</code>.',
					'stripe'
				)
			);
		}
	} else {
		// Test Mode Secret Key used in Live Mode Secret Key.
		if ( 'pk_test_' === substr( $publishable_key, 0, 8 ) ) {
			$key_errors->add(
				'simpay_pk_mode_mismatch',
				__(
					'Invalid Publishable Key for current mode. Publishable Key should begin with <code>pk_live_</code>.',
					'stripe'
				)
			);
		}

		// Test Mode Secret Key used in Live Mode Secret Key.
		if ( 'sk_test_' === substr( $secret_key, 0, 8 ) ) {
			$key_errors->add(
				'simpay_sk_mode_mismatch',
				__(
					'Invalid Secret Key for current mode. Secret Key should begin with <code>sk_live_</code>.',
					'stripe'
				)
			);
		}
	}

	if ( ! empty( $key_errors->errors ) ) {
		return wp_send_json_error(
			array(
				'message' => sprintf(
					'<span style="color: red;">%s</span> %s %s',
					$key_errors->get_error_message(),
					__(
						'If you have manually modified these values after connecting your account, please reconnect below or update your API keys manually.',
						'stripe'
					),
					$connect
				)
			)
		);
	}


	// Stripe Connect.
	if ( ! empty( $account_id ) ) {
		try {
			$account = Stripe_API::request(
				'Account',
				'retrieve',
				$account_id,
				array(
					'api_key' => simpay_get_secret_key(),
				)
			);

			$email        = isset( $account->email ) ? $account->email : '';
			$display_name = isset( $account->display_name )
				? $account->display_name
				: '';

			if ( empty( $display_name ) ) {
				if (
					isset( $account->settings ) &&
					isset( $account->settings->dashboard ) &&
					isset( $account->settings->dashboard->display_name )
				) {
					$display_name = $account->settings->dashboard->display_name;
				}
			}

			if ( empty( $email ) && empty( $display_name ) ) {
				return wp_send_json_success( $dev_account_error );
			}

			if ( ! empty( $display_name ) ) {
				$display_name = '<strong>' . $display_name . '</strong><br/ >';
			}

			if ( ! empty( $email ) ) {
				$email = $email . ' &mdash; ';
			}

			$message = (
				$display_name .
				$email .
				esc_html( 'Administrator (Owner)', 'simple-pay' )
			);

			/**
			 * Allows filtering of the message displayed when Stripe Connect is connected.
			 *
			 * @since 4.4.1
			 *
			 * @param string $message The message to display.
			 */
			$message = apply_filters(
				'__unstable_simpay_stripe_connect_account_message',
				$message
			);

			return wp_send_json_success(
				array(
					'message' => $message,
					'actions' => 'simpay-stripe-activated-account-actions',
				)
			);
		} catch ( \SimplePay\Vendor\Stripe\Exception\AuthenticationException $e ) {
			return wp_send_json_error(
				array(
					'message' => esc_html__(
						'Unable to validate your Stripe Account with the API keys provided. If you have manually modified these values after connecting your account, please reconnect below or update your API keys manually.',
						'stripe'
					) . $connect,
				)
			);
		} catch ( \Exception $e ) {
			return wp_send_json_error( $unknown_error );
		}
	}

	// No Stripe Connect.
	try {
		// Attempt to make an API request.
		API\Customers\all(
			array(
				'limit' => 1,
			),
			array(
				'api_key' => simpay_get_secret_key(),
			)
		);

		return wp_send_json_success(
			array(
				'message' => (
					sprintf(
						/* translators: %1$s Stripe payment mode.*/
						__( 'Your manually managed %1$s mode API keys are valid.', 'stripe' ),
						'<strong>' . $mode . '</strong>'
					)
				),
			)
		);
	} catch ( \Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => (
					'<span style="color: red;">' .
						sprintf(
							/* translators: %1$s Stripe payment mode.*/
							__( 'Your manually managed %1$s mode API keys are invalid.', 'stripe' ),
							'<strong>' . $mode . '</strong>'
						)
					. '</span>'
				),
			)
		);
	}
}
add_action( 'wp_ajax_simpay_stripe_connect_account_information', 'simpay_stripe_connect_account_information' );
