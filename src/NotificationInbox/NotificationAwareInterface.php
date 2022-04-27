<?php
/**
 * Notification inbox: Notification aware interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox;

/**
 * NotificationAwareInterface interface.
 *
 * @since 4.4.5
 */
interface NotificationAwareInterface {

	/**
	 * Sets the notification repository.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\NotificationInbox\NotificationRepository $notifications
	 * @return void
	 */
	public function set_notifications( NotificationRepository $notifications );

}
