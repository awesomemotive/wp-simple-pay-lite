<?php
/**
 * Admin: UPE Migration notification
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.14.0
 */

namespace SimplePay\Core\NotificationInbox\Notifications;

use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;

/**
 * Admin: UPE Migration notification
 *
 * @since 4.13.0
 */
class UpeMigration implements SubscriberInterface, NotificationAwareInterface, LicenseAwareInterface {
	use NotificationAwareTrait;
	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		$subscribers = array();
		if ( 'no' === simpay_get_setting( 'is_upe' ) ) {
			$subscribers['admin_init'][] = array( 'add_upe_migration_notification' );
		}
		return $subscribers;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function add_upe_migration_notification() {
		$notification = $this->notifications->get_by( 'slug', 'upe-migration-notification-4140' );

		if ( $notification instanceof Notification && $notification->dismissed ) {
			return;
		}

		$this->notifications->restore(
			array(
				'type'       => 'success',
				'source'     => 'internal',
				'title'      => __(
					'🚀 Exciting Update: Safer and More Powerful Payment Forms',
					'stripe'
				),
				'slug'       => 'upe-migration-notification-4140',
				'content'    => __(
					"We're thrilled to announce that we've upgraded your site to use WP Simple Pay's new payment experience.

<strong>✨ What's New?</strong>

⭐️ <strong>Simplified Checkout:</strong> A sleek, intuitive interface for smoother transactions.
💳 <strong>More Payment Options:</strong> Access the latest payment methods WP Simple Pay has added.
🔒 <strong>Enhanced Security:</strong> Better fraud protection with the latest Stripe technology.

Your payments are now faster, safer, and more flexible than ever! 🎉

If you have any questions about this update, feel free to contact us. Thank you for choosing us!",
					'stripe'
				),
				'actions'    => array(),
				'conditions' => array(),
				'start'      => date( 'Y-m-d H:i:s', time() ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'end'        => date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS * 10 ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			)
		);
	}
}
