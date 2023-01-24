<?php
/**
 * Admin Dashboard Widget: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\DashboardWidget;

/**
 * AbstractDashboardWidget class.
 *
 * @since 4.4.0
 */
abstract class AbstractDashboardWidget implements DashboardWidgetInterface {

	/**
	 * {@inheritdoc}
	 */
	abstract public function can_register();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_id();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_name();

	/**
	 * {@inheritdoc}
	 */
	public function get_context() {
		return 'normal';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_priority() {
		return 'high';
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_args();

	/**
	 * {@inheritdoc}
	 */
	abstract public function render();

}
