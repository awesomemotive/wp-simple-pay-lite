<?php
/**
 * Admin page: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\AdminPage;

/**
 * AdminPageInterface interface.
 *
 * @since 4.4.0
 */
interface AdminPageInterface {

	/**
	 * Returns the menu position.
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_menu_page/#menu-structure
	 *
	 * @since 4.4.0
	 *
	 * @return int
	 */
	public function get_position();

	/**
	 * Returns the capability required to view the page.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_capability_requirement();

	/**
	 * Returns the page title that appears in the menu.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_menu_title();

	/**
	 * Returns the page title that appears in the browser.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_page_title();

	/**
	 * Returns the page slug.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_page_slug();

	/**
	 * Returns the page \WP_Screen base name.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_screen_base_name();

	/**
	 * Determines if the current page uses the block editor (or similar).
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_block_editor();

	/**
	 * Renders the page.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function render();

}
