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
			'rest-api-unstable-dashboard-widget-report',
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
			'rest-api-unstable-dashboard-widget-report',
			__UnstableDashboardWidgetReport::class
		);
	}

}
