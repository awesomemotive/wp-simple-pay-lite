<?php
/**
 * Admin: Product education interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

/**
 * ProductEducationInterface interface.
 *
 * @since 4.4.0
 */
interface ProductEducationInterface {

	/**
	 * Returns the upgrade button URL.
	 *
	 * @since 4.4.0
	 *
	 * @param string $utm_medium utm_medium parameter.
	 * @param string $utm_content Optional. utm_content parameter.
	 * @return string
	 */
	public function get_upgrade_button_url( $utm_medium, $utm_content = '' );

	/**
	 * Returns the upgrade button text for product education.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_upgrade_button_text();

	/**
	 * Returns the URL for license upgrade documentation.
	 *
	 * @since 4.4.6
	 *
	 * @param string $utm_medium utm_medium parameter.
	 * @param string $utm_content Optional. utm_content parameter.
	 * @return string
	 */
	public function get_already_purchased_url( $utm_medium, $utm_content = '' );

	/**
	 * Returns copy displayed under the upgrade button.
	 *
	 * @since 4.4.0
	 * @since 4.4.6 Added $upgrade_url parameter.
	 *
	 * @param string $upgrade_url Upgrade URL.
	 * @return string
	 */
	public function get_upgrade_button_subtext( $upgrade_url );

}
