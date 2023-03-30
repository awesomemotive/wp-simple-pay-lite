<?php
/**
 * Report: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Report;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * ReportServiceProvider class.
 *
 * @since 4.7.3
 */
class ReportServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'report-activity-overview',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Activity overview report.
		// Top stats (with deltas) and top 5 forms (with deltas), and a DYK blurb.
		$container->add(
			'report-activity-overview',
			ActivityOverviewReport::class
		);
	}

}
