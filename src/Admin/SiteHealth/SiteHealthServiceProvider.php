<?php
/**
 * Site Health: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.7
 */

namespace SimplePay\Core\Admin\SiteHealth;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * SiteHealthServiceProvider class.
 *
 * @since 4.4.7
 */
class SiteHealthServiceProvider extends AbstractPluginServiceProvider {

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
			'site-health-debug-information',
			'site-health-menu-item',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Site Health menu item.
		$container->share(
			'site-health-menu-item',
			SiteHealthMenuItem::class
		);

		// Site Health debug information.
		$container->share(
			'site-health-debug-information',
			SiteHealthDebugInformation::class
		)
			->withArgument( $container->get( 'event-manager' ) );
	}

}
