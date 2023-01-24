<?php
/**
 * Dashboard widget: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\DashboardWidget;

/**
 * DashboardWidgetInterface interface.
 *
 * @since 4.4.0
 */
interface DashboardWidgetInterface {

	/**
	 * Determines if the widget instance can be registered.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function can_register();

	/**
	 * Returns the dashboard widget's ID.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Returns the dashboard widget's name.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Returns the dashboard widget's context.
	 *
	 * @since 4.4.0
	 *
	 * @return 'normal'|'side'|'column3'|'column4'
	 */
	public function get_context();

	/**
	 * Returns the dashboard widget's priority.
	 *
	 * @since 4.4.0
	 *
	 * @return 'high'|'core'|'default'|'low'
	 */
	public function get_priority();

	/**
	 * Returns the dashboard widget's arguments.
	 *
	 * @since 4.4.0
	 *
	 * @return array<string, string>
	 */
	public function get_args();

	/**
	 * Outputs the dashboard widget's content.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function render();

}
