<?php
/**
 * Integration: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration;

/**
 * IntegrationInterface interface.
 *
 * @since 4.4.3
 */
interface IntegrationInterface {

	/**
	 * Returns the integration ID.
	 *
	 * @since 4.4.3
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Determines if the integration is active.
	 *
	 * @since 4.4.3
	 *
	 * @return bool
	 */
	public function is_active();

}
