<?php
/**
 * REST API: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\RestApi;

use Exception;
use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * RestApiServiceProvider class.
 *
 * @since 4.4.5
 */
class RestApiServiceProvider extends AbstractPluginServiceProvider implements LicenseAwareInterface {

	use LicenseAwareTrait;

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
			// Internal: payment.
			'rest-api-internal-payment-create',
			'rest-api-internal-payment-update',
			'rest-api-internal-payment-validate-coupon',
			'rest-api-internal-payment-calculate-tax',
			'rest-api-internal-payment-update-payment-method',

			// Internal: notifications.
			'rest-api-unstable-notifications',

			// Internal: reports.
			'rest-api-internal-report-dashboard-widget-report',
			'rest-api-internal-report-today',
			'rest-api-internal-report-latest-payments',
			'rest-api-internal-report-payment-info',
			'rest-api-internal-report-gross-volume-period-over-period',
			'rest-api-internal-report-successful-payments-period-over-period',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();
		/** @var \SimplePay\Core\License\License */
		$license   = $container->get( 'license' );

		// UPE routes.
		if ( simpay_is_upe() ) {

			// Payment create (depending on which plugin).
			$container->share(
				'rest-api-internal-payment-create',
				$license->is_lite()
					? Internal\Payment\LitePaymentCreateRoute::class
					: Internal\Payment\ProPaymentCreateRoute::class
			)
				->withArgument(
					$container->get( 'stripe-connect-application-fee' )
				);

			// Additional payment routes for Pro.
			if ( false === $license->is_lite() ) {
				$container->share(
					'rest-api-internal-payment-update',
					Internal\Payment\PaymentUpdateRoute::class
				);

				$container->share(
					'rest-api-internal-payment-validate-coupon',
					Internal\Payment\ValidateCouponRoute::class
				);

				$container->share(
					'rest-api-internal-payment-calculate-tax',
					Internal\Payment\TaxCalculationRoute::class
				);
			}
		}

		// Update payment method.
		$container->share(
			'rest-api-internal-payment-update-payment-method',
			Internal\Payment\UpdatePaymentMethodRoute::class
		);

		// Notifications.
		try {
			$notifications = $container->get( 'notification-inbox-repository' );

			$container->share(
				'rest-api-unstable-notifications',
				__UnstableNotifications::class
			)
				->withArgument( $notifications );
		} catch ( Exception $e ) {
			// Do not register endpoint if notifications aren't being used.
		}

		// Dashboard widget report.
		$container->share(
			'rest-api-internal-report-dashboard-widget-report',
			Internal\Report\DashboardWidgetReport::class
		);

		// Today Period Over Period report.
		$container->share(
			'rest-api-internal-report-today',
			Internal\Report\TodayReport::class
		);

		// Latest Payments report.
		$container->share(
			'rest-api-internal-report-latest-payments',
			Internal\Report\LatestPaymentsReport::class
		);

		// Payment Info report.
		$container->share(
			'rest-api-internal-report-payment-info',
			Internal\Report\PaymentInfoReport::class
		);

		// Gross Volume Period Over Period report.
		$container->share(
			'rest-api-internal-report-gross-volume-period-over-period',
			Internal\Report\GrossVolumePeriodOverPeriodReport::class
		);

		// Succesful Payments Period Over Period report.
		$container->share(
			'rest-api-internal-report-successful-payments-period-over-period',
			Internal\Report\SuccessfulPaymentsPeriodOverPeriodReport::class
		);
	}

}
