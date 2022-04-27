<?php
/**
 * Admin bar: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\AdminBar;

use Exception;
use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * AdminBarServiceProvider class.
 *
 * @since 4.4.5
 */
class AdminBarServiceProvider extends AbstractPluginServiceProvider {

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
			'admin-bar-subscriber',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Attempt to use the notification inbox.
		try {
			$notification_inbox = $container->get( 'notification-inbox-repository' );
		} catch ( Exception $e ) {
			$notification_inbox = null;
		}

		$container->share( 'admin-bar-subscriber', AdminBarSubscriber::class )
			->withArgument( $notification_inbox );
	}

}
