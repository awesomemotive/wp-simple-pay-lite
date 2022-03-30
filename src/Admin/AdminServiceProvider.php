<?php
/**
 * Admin: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\Admin;

use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Core\AdminNotice;
use SimplePay\Core\AdminPage;

/**
 * AdminServiceProvider class.
 *
 * @since 4.4.1
 */
class AdminServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'admin-page-about-us',
			'admin-page-setup-wizard',
			'admin-notice-update-available',
			'admin-notice-five-star-rating',
			'admin-notice-license-upgrade-top-of-page',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'admin-branding',
			'admin-page-subscriber',
			'admin-notice-subscriber',
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

		// Admin notices.
		$container->share(
			'admin-notice-subscriber',
			AdminNoticeSubscriber::class
		)
			->withArgument( $this->get_notices() );
	}

	/**
	 * Returns a list of admin pages to register.
	 *
	 * @since 4.4.0
	 *
	 * @return array<\SimplePay\Core\AdminPage\AdminPageInterface> Admin pages to register.
	 */
	private function get_pages() {
		$container = $this->getContainer();

		// Setup wizard.
		$container->share(
			'admin-page-setup-wizard',
			AdminPage\SetupWizardPage::class
		);

		// About Us.
		$container->share(
			'admin-page-about-us',
			AdminPage\AboutUsPage::class
		);

		/** @var array<\SimplePay\Core\AdminPage\AdminPageInterface> $pages */
		$pages = array(
			$container->get( 'admin-page-setup-wizard' ),
			$container->get( 'admin-page-about-us' ),
		);

		return $pages;
	}

	/**
	 * Returns a list of admin notices to register.
	 *
	 * @since 4.4.1
	 * @since 4.4.4 Register notices against container to take advantage of dependency injection.
	 *
	 * @return array<\SimplePay\Core\AdminNotice\AdminNoticeInterface> Admin notices to register.
	 */
	private function get_notices() {
		$container = $this->getContainer();

		// Update Available.
		$container->share(
			'admin-notice-update-available',
			AdminNotice\UpdateAvailableNotice::class
		);

		// Five Star Rating.
		$container->share(
			'admin-notice-five-star-rating',
			AdminNotice\FiveStarRatingNotice::class
		);

		// License Upgrade (top of page).
		$container->share(
			'admin-notice-license-upgrade-top-of-page',
			AdminNotice\LicenseUpgradeTopOfPageNotice::class
		);

		/** @var array<\SimplePay\Core\AdminNotice\AdminNoticeInterface> */
		$notices = array(
			$container->get( 'admin-notice-update-available' ),
			$container->get( 'admin-notice-five-star-rating' ),
			$container->get( 'admin-notice-license-upgrade-top-of-page' )
		);

		return $notices;
	}

}
