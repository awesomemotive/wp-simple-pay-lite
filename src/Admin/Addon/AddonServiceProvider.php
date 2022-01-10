<?php
/**
 * Admin: Addon service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Addon;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * AddonServiceProvider class.
 *
 * @since 4.4.0
 */
class AddonServiceProvider extends AbstractPluginServiceProvider {

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
			'addon-installer',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Addon installer.
		$container->share( 'addon-installer', AddonInstaller::class );
	}

}
