<?php
/**
 * Webhook: Health check
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Webhook;

use Exception;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * EndpointHealthCheck class.
 *
 * @since 4.4.2
 */
class EndpointHealthCheck implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Webhook endpoint manager.
	 *
	 * @since 4.4.2
	 * @var \SimplePay\Core\Webhook\WebhookEndpointManager $endpoint
	 */
	private $endpoint;

	/**
	 * StripeConnectCreation.
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Core\Webhook\WebhookEndpointManager $endpoint Webhook endpoint manager.
	 * @return void
	 */
	public function __construct( WebhookEndpointManager $endpoint ) {
		$this->endpoint = $endpoint;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		$dismissed = (bool) get_option(
			'simpay_webhook_event_expected_dismiss',
			false
		);

		// Do not run health check if the event received notice was permanently dismissed.
		if ( true === $dismissed ) {
			return array();
		}

		return array(
			'admin_init'             => 'maybe_update',
			'generate_rewrite_rules' => 'update_endpoint_url',
		);
	}

	/**
	 * Possibly updates the webhook endpoint if the stored values do not match what is expected.
	 *
	 * @since 4.2.2
	 *
	 * @return void
	 */
	public function maybe_update() {
		try {
			$prefix = simpay_is_test_mode() ? 'test' : 'live';

			// Check the ID.
			/** @var string $endpoint_id */
			$endpoint_id = simpay_get_setting(
				"${prefix}_webhook_endpoint_id",
				''
			);

			if ( empty( $endpoint_id ) ) {
				$this->endpoint->create();

				return;
			}

			// Check the URLs.
			/** @var string $endpoint_url */
			$endpoint_url = simpay_get_setting(
				"${prefix}_webhook_endpoint_url",
				''
			);

			if (
				empty( $endpoint_url ) ||
				$endpoint_url !== simpay_get_webhook_url()
			) {
				$endpoint = $this->endpoint->get( $endpoint_id );
				$this->endpoint->update( $endpoint );

				return;
			}

			// Check the events.
			/** @var array<string> $endpoint_events */
			$endpoint_events = simpay_get_setting(
				"${prefix}_webhook_endpoint_events",
				array()
			);

			if (
				// No events.
				empty( $endpoint_events ) ||
				(
					// Not all events, and saved is fewer than our current whitelist.
					! in_array( '*', $endpoint_events, true ) &&
					count( $endpoint_events ) < count( $this->endpoint->get_event_whitelist() )
				)
			) {
				$endpoint = $this->endpoint->get( $endpoint_id );
				$this->endpoint->update( $endpoint );

				return;
			}
		} catch ( Exception $e ) {
			// Fail silently.
			// @todo Show a separate notice?
		}
	}

	/**
	 * Updates the stored webhook URL when permalink structure changes.
	 *
	 * This will trigger an update the next time the webhook endpoint is checked.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function update_endpoint_url() {
		$prefix = simpay_is_test_mode() ? 'test' : 'live';

		simpay_update_setting(
			"${prefix}_webhook_endpoint_url",
			simpay_get_webhook_url()
		);
	}

}
