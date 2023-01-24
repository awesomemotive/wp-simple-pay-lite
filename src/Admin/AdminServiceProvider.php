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

use Exception;
use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Core\AdminNotice;
use SimplePay\Core\AdminPage;
use SimplePay\Core\NotificationInbox\NotificationRepository;

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
			'admin-page-notification-inbox',
			'admin-page-system-report',
			'admin-page-about-us',
			'admin-page-setup-wizard',
			'admin-page-form-templates',
			'admin-notice-update-available',
			'admin-notice-five-star-rating',
			'admin-notice-license-upgrade-top-of-page',
			'admin-notice-license-missing',
			'admin-notice-license-expired',
			'admin-notice-stripe-api-error',
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

		// System Report.
		$container->share(
			'admin-page-system-report',
			AdminPage\SystemReportPage::class
		);

		// About Us.
		$container->share(
			'admin-page-about-us',
			AdminPage\AboutUsPage::class
		);

		$pages = array(
			$container->get( 'admin-page-setup-wizard' ),
			$container->get( 'admin-page-system-report' ),
			$container->get( 'admin-page-about-us' ),
		);

		// Add Form templates if the Template Explorer is available.
		try {
			$container->share(
				'admin-page-form-templates',
				AdminPage\FormTemplatesPage::class
			)
				->withArgument(
					$container->get( 'form-builder-template-explorer' )
				);

			$pages[] = $container->get( 'admin-page-form-templates' );
		} catch ( Exception $e ) {
			// Do not add.
		}

		// Add Activity & Reports if the minimum WordPress version is met.
		global $wp_version;

		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$container->share(
				'admin-page-activity-reports',
				AdminPage\ActivityReportsPage::class
			);

			$pages[] = $container->get( 'admin-page-activity-reports' );
		}

		// Add notification inbox page if notifications are being used.
		try {
			$notifications = $container->get( 'notification-inbox-repository' );

			if (
				$notifications instanceof NotificationRepository &&
				$notifications->get_unread_count() > 0
			) {
				$container->share(
					'admin-page-notification-inbox',
					AdminPage\NotificationInboxPage::class
				)
					->withArgument( $notifications );

				$pages[] = $container->get( 'admin-page-notification-inbox' );
			}
		} catch ( Exception $e ) {
			// Do not add.
		}

		/** @var array<\SimplePay\Core\AdminPage\AdminPageInterface> $pages */
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

		// License key missing.
		$container->share(
			'admin-notice-license-missing',
			AdminNotice\LicenseMissingNotice::class
		);

		// License key expired.
		$container->share(
			'admin-notice-license-expired',
			AdminNotice\LicenseExpiredNotice::class
		);

		// Stripe API error.
		$container->share(
			'admin-notice-stripe-api-error',
			AdminNotice\StripeApiErrorNotice::class
		);

		/** @var array<\SimplePay\Core\AdminNotice\AdminNoticeInterface> */
		$notices = array(
			$container->get( 'admin-notice-update-available' ),
			$container->get( 'admin-notice-five-star-rating' ),
			$container->get( 'admin-notice-license-upgrade-top-of-page' ),
			$container->get( 'admin-notice-license-missing' ),
			$container->get( 'admin-notice-license-expired' ),
			$container->get( 'admin-notice-stripe-api-error' ),
		);

		return $notices;
	}

}
