<?php
/**
 * Admin page: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\AdminPage;

/**
 * AbstractAdminPage abstract.
 *
 * @since 4.4.0
 */
abstract class AbstractAdminPage {

	/**
	 * Returns the capability required to view the page.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	abstract public function get_capability_requirement();

	/**
	 * Returns the page title that appears in the menu.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	abstract public function get_menu_title();

	/**
	 * Returns the page title that appears in the browser.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	abstract public function get_page_title();

	/**
	 * Returns the page slug.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	abstract public function get_page_slug();

	/**
	 * Returns the page \WP_Screen base name.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_screen_base_name() {
		$base = '';

		if ( $this instanceof AdminPrimaryPageInterface ) {
			$base = 'toplevel_page_' . $this->get_page_slug();
		} elseif ( $this instanceof AdminSecondaryPageInterface ) {
			$base = 'simpay_page_' . $this->get_page_slug();
		}

		return $base;
	}

	/**
	 * Determines if the current page uses the block editor (or similar).
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	abstract public function is_block_editor();

	/**
	 * Renders the page.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	abstract public function render();

}
