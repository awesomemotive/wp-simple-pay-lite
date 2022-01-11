<?php
/**
 * Admin secondary page: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\AdminPage;

/**
 * AdminSecondaryPageInterface interface.
 *
 * @since 4.4.0
 */
interface AdminSecondaryPageInterface extends AdminPageInterface {

	/**
	 * Returns the slug of the primary page this is positioned under.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_parent_slug();

}
