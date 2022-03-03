<?php
/**
 * Elementor: Widget interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration\Elementor;

/**
 * WidgetInterface interface
 *
 * @since 4.4.3
 */
interface WidgetInterface {

	/**
	 * Returns the ID/name of the widget to target.
	 *
	 * @since 4.4.3
	 *
	 * @return string
	 */
	public function get_name();

}
