<?php
/**
 * Admin: "Notification Inbox" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\AdminPage;

use SimplePay\Core\NotificationInbox\NotificationRepository;

/**
 * NotificationInboxPage class.
 *
 * @since 4.4.5
 */
class NotificationInboxPage extends AbstractAdminPage implements AdminSecondaryPageInterface {

	/**
	 * Notifications.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\NotificationInbox\NotificationRepository
	 */
	private $notifications;

	/**
	 * NotificationInboxPage.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\NotificationInbox\NotificationRepository $notifications Notification repository.
	 */
	public function __construct( NotificationRepository $notifications ) {
		$this->notifications = $notifications;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 0;
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
		$unread = $this->notifications->get_unread_count();

		if ( 0 === $unread ) {
			return __( 'Notifications', 'stripe' );
		}

		return sprintf(
			__( 'Notifications <span class="simpay-admin-menu-notification-indicator"></span>', 'stripe' ),
			$unread
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'Notifications', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'simpay_settings#notifications';
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
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
	}

}
