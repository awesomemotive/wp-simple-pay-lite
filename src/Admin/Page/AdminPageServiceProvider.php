<?php
/**
 * Admin: Page service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Page;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * AdminPageServiceProvider class.
 *
 * @since 4.4.0
 */
class AdminPageServiceProvider extends AbstractPluginServiceProvider {

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
			'admin-branding',
			'admin-page-subscriber',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Admin branding.
		$container->share( 'admin-branding', AdminBranding::class );

		// Admin pages.
		$container->share(
			'admin-page-subscriber',
			AdminPageSubscriber::class
		)
			->withArgument( $this->get_pages() );
	}

	/**
	 * Returns a list of admin pages to register.
	 *
	 * @since 4.4.0
	 *
	 * @return \SimplePay\Core\AdminPage\AdminPageInterface[] Admin pages to register.
	 */
	private function get_pages() {
		$container = $this->getContainer();
		$pages     = array();

		// "About Us" page.
		$license = $container->get( 'license' );

		if ( $license instanceof \SimplePay\Core\License\License ) {
			$pages[] = new AboutUsPage( $license );
		}

		return $pages;
	}

}
