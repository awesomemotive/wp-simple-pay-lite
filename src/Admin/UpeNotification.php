<?php
/**
 * Admin: UPE notification
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\Admin;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\Settings;

/**
 * UpeNotification
 *
 * @since 4.7.10
 */
class UpeNotification implements SubscriberInterface, NotificationAwareInterface, LicenseAwareInterface {

	use NotificationAwareTrait;
	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		$subscribers = array();

		if ( $this->license->is_lite() ) {
			return $subscribers;
		}

		// Alert via Notification Inbox if available.
		if ( $this->notifications instanceof NotificationRepository ) {
			if ( ! simpay_is_upe() ) {
				$subscribers['admin_init'][] = array( 'add_notification' );
			}

			$subscribers['pre_update_option_simpay_settings'] = array( 'dismiss_notification' );
		}

		return $subscribers;
	}

	/**
	 * Adds a notification to leave a review.
	 *
	 * @since 4.7.10
	 *
	 * @return void
	 */
	public function add_notification() {
		$notification = $this->notifications->get_by( 'slug', 'upe-4710' );

		if ( $notification instanceof Notification && $notification->dismissed ) {
			return;
		}

		$settings_url = Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'advanced',
				'setting'    => 'is_upe',
			)
		);

		$this->notifications->restore(
			array(
				'type'       => 'success',
				'source'     => 'internal',
				'title'      => __(
					'A New Payment Experience is Available',
					'stripe'
				),
				'slug'       => 'upe-4710',
				'content'    => 'Join the other WP Simple Pay users who have already embraced the new payment experience to offer Stripe Link and other powerful payment form features. With the new smarter payment forms, you get access to:

🔗&nbsp;&nbsp;Stripe Link support for <strong>9x faster payments</strong><br />
💳&nbsp;&nbsp;Access to <strong>more payment methods</strong><br />
📍&nbsp;&nbsp;Streamlined fields with <strong>automatic address suggestions</strong><br />
🤖&nbsp;&nbsp;Additional anti-spam functionality<br />
💯&nbsp;&nbsp;+ <strong>new features available each update</strong><br />

Don\'t wait any longer to harness the power of WP Simple Pay\'s latest and greatest features.',
				'actions'    => array(
					array(
						'type' => 'primary',
						'text' => __( 'Enable Now', 'stripe' ),
						'url'  => $settings_url,
					),
					array(
						'type' => 'secondary',
						'text' => __( 'Learn More', 'stripe' ),
						'url'  => simpay_docs_link(
							'',
							'how-to-enable-the-new-payment-experience',
							'notification-inbox',
							true
						),
					),
				),
				'conditions' => array(),
				'start'      => date( 'Y-m-d H:i:s', time() ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'end'        => date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS * 10 ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			)
		);
	}

	/**
	 * Dismisses all UPE notifications when the UPE is enabled.
	 *
	 * @since 4.7.10
	 *
	 * @param array<string, mixed> $settings Settings.
	 * @return array<string, mixed>
	 */
	public function dismiss_notification( $settings ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $settings;
		}

		if ( isset( $settings['is_upe'] ) && 'yes' === $settings['is_upe'] ) {
			$this->notifications->dismiss( 'upe-4710' );
		}

		return $settings;
	}

}
