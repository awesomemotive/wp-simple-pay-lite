<?php
/**
 * EmbeddingSecret class file
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.13.0
 */

namespace SimplePay\Core\RestApi\Internal\EmbeddedComponents;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Cryptography\Cryption;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * EmbeddingSecret class.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.13.0
 */
class EmbeddingSecret implements SubscriberInterface {

	private const NAMESPACE = 'wpsp/__internal__';
	private const ROUTE     = 'embedding-secret/(?P<component>[a-zA-Z0-9-]+)';

	/**
	 * Constructor.
	 *
	 * @since 4.13.0
	 * @return void
	 */
	public function __construct() {
		if ( ! defined( 'SIMPLE_PAY_PROXY_URL_STRIPE' ) ) {
			define( 'SIMPLE_PAY_PROXY_URL_STRIPE', simpay_is_test_mode() ? 'https://requests.wpsimplepay.com/api/stripe?mode=test' : 'https://requests.wpsimplepay.com/api/stripe?mode=live' );
		}
	}

	/**
	 * Registers the REST API route.
	 *
	 * @since 4.13.0
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_embedding_secret' ),
				'permission_callback' => array( $this, 'get_capability_requirement' ),
				'args'                => array(
					'component' => array(
						'required' => true,
						'type'     => 'string',
						'enum'     => array( 'balance', 'banner' ),
					),
				),
			)
		);
	}

	/**
	 * Returns the capability requirement for the embedding secret endpoint.
	 *
	 * @since 4.13.0
	 * @return bool
	 */
	public function get_capability_requirement() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 4.13.0
	 * @return array<string, string>
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Retrieves the embedding secret from the proxy server.
	 *
	 * @since 4.13.0
	 * @param \WP_REST_Request<array<string, mixed>> $request The request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_embedding_secret( $request ) {
		// Get component from request.
		/** @var string $component */
		$component = $request->get_param( 'component' );

		/** @var string $license_key */
		$license_key = get_option( 'simpay_license_key', '' );

		// Return early if license key is empty.
		if ( empty( $license_key ) ) {
			return new WP_Error(
				'rest_invalid_license',
				'A valid license key is required',
				array( 'status' => 400 )
			);
		}

		// Send request to proxy server to get embedding secret.
		/** @var string $connected_account_id */
		$connected_account_id = simpay_get_account_id();

		if ( defined( 'SIMPLE_PAY_CONNECTED_ACCOUNT_ID' ) ) {
			/** @var string $connected_account_id */
			$connected_account_id = SIMPLE_PAY_CONNECTED_ACCOUNT_ID;
		}

		$cryption = new Cryption();

		if ( ! $cryption->is_openssl_enabled() ) {
			return new WP_Error( 'rest_internal_error', 'OpenSSL is not enabled', array( 'status' => 500 ) );
		}

		$request_data = array(
			'connectedAccountId' => $cryption->encrypt( $connected_account_id ),
			'component'          => $cryption->encrypt( $component ),
			'license'            => $cryption->encrypt( $license_key ),
		);

		$json_body = wp_json_encode( $request_data );

		if ( false === $json_body ) {
			return new WP_Error( 'rest_internal_error', 'Failed to encode request body', array( 'status' => 500 ) );
		}

		/** @var string $proxy_url */
		$proxy_url = defined( 'SIMPLE_PAY_PROXY_URL_STRIPE' ) ? SIMPLE_PAY_PROXY_URL_STRIPE : 'https://requests.wpsimplepay.com/api/stripe';

		$response = wp_remote_post(
			$proxy_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => $json_body,
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'rest_internal_error', 'Error fetching embedding secret', $response );
		}

		/** @var array{data: array{client_secret: string, publishable_key: string}} $body */
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return new WP_REST_Response(
			array(
				'client_secret'   => $body['data']['client_secret'],
				'publishable_key' => $body['data']['publishable_key'],
			)
		);
	}
}
