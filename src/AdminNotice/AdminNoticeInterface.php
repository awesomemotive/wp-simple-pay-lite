<?php
/**
 * Admin notice: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\AdminNotice;

/**
 * AdminNoticeInterface interface.
 *
 * @since 4.4.1
 */
interface AdminNoticeInterface {

	/**
	 * Returns the admin notice ID.
	 *
	 * @since 4.4.1
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Returns the admin notice type.
	 *
	 * @since 4.4.1
	 *
	 * @return 'error'|'warning'|'success'|'info'
	 */
	public function get_type();

	/**
	 * Determines if the notice can be dismissed.
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	public function is_dismissible();

	/**
	 * Returns the length of time (in seconds) the notice should be dismissed before reappearing.
	 *
	 * @since 4.4.1
	 *
	 * @return int
	 */
	public function get_dismissal_length();

	/**
	 * Determines if the admin notice has been dismissed.
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	public function is_dismissed();

	/**
	 * Determines if the admin notice should be displayed.
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	public function should_display();

	/**
	 * Returns the notice's data.
	 *
	 * @since 4.4.1
	 *
	 * @return array<mixed>
	 */
	public function get_notice_data();

	/**
	 * Returns the full path to the notice view.
	 *
	 * @since 4.4.1
	 *
	 * @return string
	 */
	public function get_view();

	/**
	 * Renders the admin notice view.
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function render();

}
