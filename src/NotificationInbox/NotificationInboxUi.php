<?php
/**
 * Notification Inbox: UI
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\NotificationInbox;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * NotificationInboxUi class.
 *
 * @since 4.4.6
 */
class NotificationInboxUi implements SubscriberInterface, LicenseAwareInterface, NotificationAwareInterface {

	use NotificationAwareTrait;
	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'simpay_admin_branding_bar_actions' => 'output',
		);
	}

	/**
	 * Enqueues notification inbox scripts and styles, creating the UI.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	public function output() {
		$use_notification_inbox = $this->notifications instanceof NotificationRepository;

		/**
		 * Filters the notification inbox output.
		 *
		 * @since 4.4.5
		 *
		 * @param bool $use_notification_inbox If the notification inbox should be utilized.
		 */
		$use_notification_inbox = apply_filters(
			'simpay_use_notification_inbox',
			$use_notification_inbox
		);

		if ( false === $use_notification_inbox ) {
			return;
		}

		$asset_file = SIMPLE_PAY_INC . '/core/assets/js/simpay-admin-notifications.min.asset.php'; // @phpstan-ignore-line

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset_data = require $asset_file;

		wp_enqueue_script(
			'simpay-admin-notifications',
			SIMPLE_PAY_INC_URL . '/core/assets/js/simpay-admin-notifications.min.js', // @phpstan-ignore-line
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);

		wp_localize_script(
			'simpay-admin-notifications',
			'simpayNotifications',
			array(
				'isLite' => $this->license->is_lite() ? 1 : 0,
			)
		);

		wp_enqueue_style(
			'simpay-admin-notifications',
			SIMPLE_PAY_INC_URL . '/core/assets/css/simpay-admin-notifications.min.css', // @phpstan-ignore-line
			array(
				'wp-components',
			),
			$asset_data['version']
		);

		echo '<div id="simpay-branding-bar-notifications"></div>';
	}

}
