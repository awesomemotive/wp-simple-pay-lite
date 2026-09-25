<?php
/**
 * Subscriptions: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription;

use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Vendor\League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * SubscriptionServiceProvider class.
 *
 * @since 4.17.4
 */
class SubscriptionServiceProvider extends AbstractPluginServiceProvider implements BootableServiceProviderInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'subscription-repository',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'subscription-observer',
			'subscription-backfiller',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		$container = $this->getContainer();

		// Install repository table with BerlinDB.
		// Call maybe_upgrade() immediately instead of waiting for admin_init.
		$table = new Database\Table();
		$table->maybe_upgrade();

		// Repository.
		$container->share(
			'subscription-repository',
			SubscriptionRepository::class
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Observer.
		$container->share(
			'subscription-observer',
			SubscriptionObserver::class
		)
			->withArgument( $container->get( 'subscription-repository' ) )
			->withArgument( $container->get( 'stripe-connect-application-fee' ) );

		// Backfiller.
		$container->share(
			'subscription-backfiller',
			SubscriptionBackfiller::class
		)
			->withArgument( $container->get( 'subscription-observer' ) );
	}
}
