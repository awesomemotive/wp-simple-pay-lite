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

/**
 * RestApiServiceProvider class.
 *
 * @since 4.4.5
 */
class RestApiServiceProvider extends AbstractPluginServiceProvider {

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
			'rest-api-unstable-notifications',
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
