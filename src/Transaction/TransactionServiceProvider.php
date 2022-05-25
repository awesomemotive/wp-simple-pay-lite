<?php
/**
 * Transactions: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Transaction;

use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Vendor\League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * TransactionsServiceProvider class.
 *
 * @since 4.4.6
 */
class TransactionServiceProvider extends AbstractPluginServiceProvider implements BootableServiceProviderInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'transaction-repository',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'transaction-observer',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		$container = $this->getContainer();

		// Install repository table.
		// Create the table with BerlinDB.
		// Call maybe_upgrade() immediately instead of waiting for admin_init.
		$table = new Database\Table;
		$table->maybe_upgrade();

		// Repository.
		$container->share(
			'transaction-repository',
			TransactionRepository::class
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Observer.
		$container->share(
			'transaction-observer',
			TransactionObserver::class
		)
			->withArgument( $container->get( 'transaction-repository' ) )
			->withArgument( $container->get( 'stripe-connect-application-fee' ) );
	}

}
