<?php
/**
 * Webhook: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Webhook;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * WebhookServiceProvider class.
 *
 * @since 4.4.1
 */
class WebhookServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'webhook-endpoint-manager',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'webhook-none-received-notice',
			'webhook-stripe-connect-sync',
			'webhook-endpoint-health-check',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Manager.
		$container->share(
			'webhook-endpoint-manager',
			WebhookEndpointManager::class
		);

		// Stripe Connect sync.
		$container->share(
			'webhook-stripe-connect-sync',
			StripeConnectSync::class
		)
			->withArgument( $container->get( 'webhook-endpoint-manager' ) );

		// Endpoint health check.
		$container->share(
			'webhook-endpoint-health-check',
			EndpointHealthCheck::class
		)
			->withArgument( $container->get( 'webhook-endpoint-manager' ) );

		// No webhook received notice.
		$container->share(
			'webhook-none-received-notice',
			NoneReceivedNotice::class
		);
	}

}
