<?php
/**
 * Admin: "Notification Inbox" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.x
 */

namespace SimplePay\Core\AdminPage;

/**
 * SystemReportPage class.
 *
 * @since 4.4.x
 */
class SystemReportPage extends AbstractAdminPage implements AdminSecondaryPageInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 90;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_capability_requirement() {
		return 'manage_options';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_title() {
		return __( 'System Report', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'System Report', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'system-report';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_parent_slug() {
		return 'edit.php?post_type=simple-pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_block_editor() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
	}

}
