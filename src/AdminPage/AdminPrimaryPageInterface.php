<?php
/**
 * Admin primary page: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\AdminPage;

/**
 * AdminPrimaryPageInterface interface.
 *
 * @since 4.4.0
 */
interface AdminPrimaryPageInterface extends AdminPageInterface {

	/**
	 * Returns the menu icon.
	 *
	 * Accepts:
	 *
	 * - A base64-encoded SVG using a data URI.
	 * - The class name of a Dashicon to use, e.g. 'dashicons-chart-pie'.
	 * - 'none'
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_icon();

}
