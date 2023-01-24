<?php
/**
 * User: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\User;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * UserServiceProvider class.
 *
 * @since 4.6.7
 */
class UserServiceProvider extends AbstractPluginServiceProvider {

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
			'user-preferences-subscriber',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// User preferences.
		$container->share(
			'user-preferences-subscriber',
			UserPreferencesSubscriber::class
		);
	}

}
