<?php
/**
 * Notification inbox: Notification aware trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox;

/**
 * NotificationAwareInterface trait.
 *
 * @since 4.4.5
 */
trait NotificationAwareTrait {

	/**
	 * Notifications
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\NotificationInbox\NotificationRepository
	 */
	protected $notifications;

	/**
	 * {@inheritdoc}
	 */
	public function set_notifications( NotificationRepository $notifications ) {
		$this->notifications = $notifications;
	}

}
