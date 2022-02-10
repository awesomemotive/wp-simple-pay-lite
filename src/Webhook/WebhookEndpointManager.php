<?php
/**
 * Webhook: Endpoint manager
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Webhook;

use Exception;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Webhooks;

/**
 * WebhookEndpointManager class.
 *
 * @since 4.2.2
 */
class WebhookEndpointManager {

	/**
	 * Determines if the current payment mode has an up-to-date webhook endpoint.
	 *
	 * @since 4.2.2
	 *
	 * @return bool|\SimplePay\Vendor\Stripe\WebhookEndpoint
	 */
	public function exists() {
		$prefix = simpay_is_test_mode() ? 'test' : 'live';

		/** @var string $endpoint_id */
		$endpoint_id = simpay_get_setting(
			"{$prefix}_webhook_endpoint_id",
			''
		);

		// No endpoint ID for the current mode exists.
		if ( empty( $endpoint_id ) ) {
			return false;
		}

		try {
			// The endpoint is still valid in Stripe.
			$endpoint = $this->get( $endpoint_id );
		} catch ( Exception $e ) {
			// The endpoint ID exists, but the endpoint no longer exists on Stripe.
			return false;
		}

		return $endpoint;
	}

	/**
	 * Determines if a Stripe webhook endpoint is still valid.
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Vendor\Stripe\WebhookEndpoint $endpoint Webhook endpoint to validate.
	 * @return bool True if the webhook does not need to be updated.
	 */
	public function is_valid( $endpoint ) {
		$enabled_events = $endpoint->enabled_events;

		// Enabled events are not * and do not match the defined whitelist.
		if (
			! in_array( '*', $enabled_events, true ) &&
			$enabled_events != $this->get_event_whitelist()
		) {
			return false;
		}

		// URL mismatch.
		if( $endpoint->url !== simpay_get_webhook_url() ) {
			return false;
		}

		// Disabled.
		if ( 'enabled' !== $endpoint->status ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves a Stripe webhook endpoint.
	 *
	 * @since 4.4.2
	 *
	 * @param string $endpoint_id Stripe endpoint ID.
	 * @return \SimplePay\Vendor\Stripe\WebhookEndpoint
	 */
	public function get( $endpoint_id ) {
		return Stripe_API::request(
			'WebhookEndpoint',
			'retrieve',
			$endpoint_id
		);
	}

	/**
	 * Creates a Stripe webhook endpoint with the current plugin and site settings.
	 *
	 * @since 4.4.2
	 *
	 * @return \SimplePay\Vendor\Stripe\WebhookEndpoint
	 */
	public function create() {
		// Use an existing endpoint if one already exists from a previous setup.
		$endpoint = $this->get_manual_endpoint();

		if ( ! $endpoint instanceof \SimplePay\Vendor\Stripe\WebhookEndpoint ) {
			$endpoint = Stripe_API::request(
				'WebhookEndpoint',
				'create',
				array(
					'url'            => simpay_get_webhook_url(),
					'enabled_events' => $this->get_event_whitelist(),
					'connect'        => false,
					'api_version'    => SIMPLE_PAY_STRIPE_API_VERSION, // @phpstan-ignore-line
					'description'    => sprintf(
						'WP Simple Pay (WordPress plugin) endpoint (%s Mode)',
						simpay_is_test_mode() ? 'Test' : 'Live'
					),
				),
				$this->get_api_request_args()
			);
		}

		$this->persist( $endpoint );

		return $endpoint;
	}

	/**
	 * Updates a Stripe webhook endpoint with the current site and plugin settings.
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Vendor\Stripe\WebhookEndpoint $endpoint Webhook endpoint to update.
	 * @return \SimplePay\Vendor\Stripe\WebhookEndpoint
	 */
	public function update( $endpoint ) {
		$enabled_events = $endpoint->enabled_events;

		// Existing webhook does not accept all events. Merge the new whitelist.
		if ( ! in_array( '*', $enabled_events, true ) ) {
			$enabled_events = array_values(
				array_unique(
					array_merge(
						$enabled_events,
						$this->get_event_whitelist()
					)
				)
			);
		}

		$endpoint = Stripe_API::request(
			'WebhookEndpoint',
			'update',
			$endpoint->id,
			array(
				'url'            => simpay_get_webhook_url(),
				'enabled_events' => $enabled_events,
				'disabled'       => false,
			),
			$this->get_api_request_args()
		);

		$this->persist( $endpoint );

		return $endpoint;
	}

	/**
	 * Returns a list of Stripe webhook events that are supported by the plugin.
	 *
	 * @since 4.4.2
	 *
	 * @return array<string>
	 */
	public function get_event_whitelist() {
		return array_keys( Webhooks\get_event_whitelist() );
	}

	/**
	 * Persists a Stripe webhook endpoint's information.
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Vendor\Stripe\WebhookEndpoint $endpoint Webhook endpoint to persist.
	 * @return void
	 */
	private function persist( $endpoint ) {
		$prefix = simpay_is_test_mode() ? 'test' : 'live';

		simpay_update_setting(
			"{$prefix}_webhook_endpoint_id",
			$endpoint->id
		);

		simpay_update_setting(
			"{$prefix}_webhook_endpoint_events",
			$endpoint->enabled_events
		);

		simpay_update_setting(
			"{$prefix}_webhook_endpoint_url",
			$endpoint->url
		);

		// Secret is only returned on initial creation.
		if ( isset( $endpoint->secret ) ) {
			simpay_update_setting(
				"{$prefix}_webhook_endpoint_secret",
				$endpoint->secret
			);

			// Clear out from a previous connection.
		} else {
			simpay_update_setting(
				"{$prefix}_webhook_endpoint_secret",
				''
			);
		}

		simpay_update_setting(
			"{$prefix}_webhook_endpoint_created",
			$endpoint->created
		);
	}

	/**
	 * Returns per-request arguments to be passed to the Stripe API.
	 *
	 * @since 4.4.2
	 *
	 * @return array<string>
	 */
	private function get_api_request_args() {
		return array(
			'api_key' => simpay_get_secret_key(),
		);
	}

	/**
	 * Attemps to locate an endpoint that was manually created prior to 4.4.2.
	 *
	 * If we find an endpoint with an exact URL match, persist it and return it.
	 *
	 * @since 4.4.2
	 *
	 * @return bool|\SimplePay\Vendor\Stripe\WebhookEndpoint Existing webhook endpoint if one matches the expected URL.
	 *                                                       False if no endpoint matches.
	 */
	private function get_manual_endpoint() {
		$endpoints = Stripe_API::request(
			'WebhookEndpoint',
			'all',
			array(
				'limit' => 100,
			),
			$this->get_api_request_args()
		);

		foreach ( $endpoints->data as $endpoint ) {
			if ( $endpoint->url === simpay_get_webhook_url() ) {
				// Ensure the remote endpoint has the expected events. Update if needed.
				$remote_events = $endpoint->enabled_events;
				$local_events  = $this->get_event_whitelist();

				if ( count( $remote_events ) < count( $local_events ) ) {
					$endpoint = $this->update( $endpoint );
				}

				// Persist the endpoint information locally.
				$this->persist( $endpoint );

				return $endpoint;
			}
		}

		return false;
	}

}
