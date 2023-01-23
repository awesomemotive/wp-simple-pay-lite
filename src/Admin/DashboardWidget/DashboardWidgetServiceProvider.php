<?php
/**
 * Admin Dashboard Widget: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\Admin\DashboardWidget;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * DashboardWidgetServiceProvider class.
 *
 * @since 4.6.7
 */
class DashboardWidgetServiceProvider extends AbstractPluginServiceProvider {

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
			'admin-dashboard-widget-product-education',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Product Education.
		$container->share(
			'admin-dashboard-widget-product-education',
			ProductEducationDashboardWidget::class
		);
	}

}
