<?php
/**
 * Admin education: Dashboard widget
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\DashboardWidget\DashboardWidgetInterface;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * DashboardWidget class.
 */
class DashboardWidget implements SubscriberInterface {

	/**
	 * ProductEducationDashboardWidget.
	 *
	 * @since 4.4.0
	 * @var \SimplePay\Core\DashboardWidget\DashboardWidgetInterface
	 */
	private $widget;

	/**
	 * DashboardWidget.
	 *
	 * @since 4.4.0
	 */
	public function __construct( DashboardWidgetInterface $widget ) {
		$this->widget = $widget;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'wp_dashboard_setup' => 'register_widget',
		);
	}


	/**
	 * Registers the product education dashboard widget.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function register_widget() {
		if ( false === $this->widget->can_register() ) {
			return;
		}

		wp_add_dashboard_widget(
			$this->widget->get_id(),
			$this->widget->get_name(),
			array( $this->widget, 'render' ),
			null,
			$this->widget->get_args(),
			$this->widget->get_context(),
			$this->widget->get_priority()
		);
	}

}
