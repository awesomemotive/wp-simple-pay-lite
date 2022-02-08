<?php
/**
 * Webhook: Create endpoint on Stripe Connect connection
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
 * StripeConnectSync class.
 *
 * @since 4.4.2
 */
class StripeConnectSync implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Webhook endpoint manager.
	 *
	 * @since 4.4.2
	 * @var \SimplePay\Core\Webhook\WebhookEndpointManager $endpoint
	 */
	private $endpoint;

	/**
	 * StripeConnectSync.
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

		return array(
			'simpay_stripe_account_connected' => 'add_update_endpoint',
		);
	}

	/**
	 * Adds or updates a webhook endpoint on Stripe Connect connection.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function add_update_endpoint() {
		try {
			$endpoint = $this->endpoint->exists();

			// Endpoint does not exist, create one.
			if ( ! $endpoint instanceof \SimplePay\Vendor\Stripe\WebhookEndpoint ) {
				$this->endpoint->create();

				return;
			}

			// Endpoint exists but it is no longer valid, update it.
			if ( false === $this->endpoint->is_valid( $endpoint ) ) {
				$this->endpoint->update( $endpoint );

				return;
			}
		} catch ( Exception $e ) {
			// Fail silently.
			// @todo Show a separate notice?
		}
	}

}
