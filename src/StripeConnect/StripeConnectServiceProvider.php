<?php
/**
 * StripeConnect: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\StripeConnect;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * StripeConnectServiceProvider class.
 *
 * @since 4.4.1
 */
class StripeConnectServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'stripe-connect-connection-subscriber',
			'stripe-connect-application-fee',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Connection.
		$container->share(
			'stripe-connect-connection-subscriber',
			ConnectionSubscriber::class
		);

		// Application fee.
		$container->share(
			'stripe-connect-application-fee',
			ApplicationFee::class
		)
			->withArgument( $container->get( 'scheduler' ) )
			->withArgument( $container->get( 'transaction-repository' ) );
	}

}
