<?php
/**
 * Connect: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.2
 */

namespace SimplePay\Core\Connect;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * ConnectServiceProvider class.
 *
 * @since 4.5.2
 */
class ConnectServiceProvider extends AbstractPluginServiceProvider {

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
			'connect-subscriber',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Connect upgrade.
		$container->share(
			'connect-subscriber',
			ConnectSubscriber::class
		)
			->withArgument( $container->get( 'license-manager' ) );
	}

}
