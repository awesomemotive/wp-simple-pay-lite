<?php
/**
 * Admin: Education service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Core\License\License;

/**
 * EducationServiceProvider class.
 *
 * @since 4.4.0
 */
class EducationServiceProvider extends AbstractPluginServiceProvider {

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
			'admin-education-upgrade-modal',
			'admin-education-dashboard-widget',
			'admin-education-payment-forms-stripe-connect',
			'admin-education-payment-forms-first-form',
			'admin-education-dashboard-widget',
			'admin-education-plugin-email-settings',
			'admin-education-plugin-customers-settings',
			'admin-education-plugin-taxes-settings',
			'admin-education-plugin-coupons',
			'admin-education-instant-payouts',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Upgrade modal.
		$container->share(
			'admin-education-upgrade-modal',
			UpgradeModal::class
		);

		// Payment Forms: Stripe Connect.
		$container->share(
			'admin-education-payment-forms-stripe-connect',
			PaymentFormsStripeConnect::class
		);

		// Payment Forms: First Form.
		$container->share(
			'admin-education-payment-forms-first-form',
			PaymentFormsFirstForm::class
		);

		$license = $container->get( 'license' );

		if ( $license instanceof License ) {
			// "Email" settings teaser.
			$container->share(
				'admin-education-plugin-email-settings',
				PluginEmailSettings::class
			)
				->withArgument( $license );

			// "Subscription Management" settings teaser.
			$container->share(
				'admin-education-plugin-customers-settings',
				PluginCustomersSettings::class
			)
				->withArgument( $license );

			// "Taxes" settings teaser.
			$container->share(
				'admin-education-plugin-taxes-settings',
				PluginTaxesSettings::class
			)
				->withArgument( $license );

			// "Coupons" menu item teaser.
			$container->share(
				'admin-education-plugin-coupons',
				PluginCouponMenuItem::class
			)
				->withArgument( $license );
		}

		// Instant payouts.
		$container->share(
			'admin-education-instant-payouts',
			InstantPayouts::class
		);
	}

}
