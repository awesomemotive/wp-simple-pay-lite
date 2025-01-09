<?php
/**
 * Admin: Plugin rating notification
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.9
 */

namespace SimplePay\Core\NotificationInbox\Notifications;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;
use SimplePay\Core\NotificationInbox\NotificationRepository;

/**
 * PluginRatingNotification class.
 *
 * @since 4.7.9
 */
class PluginRatingNotification implements SubscriberInterface, NotificationAwareInterface {

	use NotificationAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		$installed = get_option( 'simpay_installed', '' );

		if ( empty( $installed ) ) {
			$installed = time();

			update_option( 'simpay_installed', $installed );
		}

		$subscribers = array();

		/** @var string $installed */

		if ( time() - (int) $installed < ( DAY_IN_SECONDS * 14 ) ) {
			return $subscribers;
		}

		// Alert via Notification Inbox.

		$subscribers['admin_init'][] = array( 'add_notification' );

		return $subscribers;
	}

	/**
	 * Adds a notification to leave a review.
	 *
	 * @since 4.7.9
	 *
	 * @return void
	 */
	public function add_notification() {
		$notification = $this->notifications->get_by( 'slug', 'dotorg-review' );

		if ( $notification instanceof Notification && $notification->dismissed ) {
			return;
		}

		$this->notifications->restore(
			array(
				'type'       => 'success',
				'source'     => 'internal',
				'title'      => __(
					'Are you enjoying WP Simple Pay?',
					'stripe'
				),
				'slug'       => 'dotorg-review',
				'content'    => __(
					'It looks like you have been using WP Simple Pay for a little while now &mdash; we sure hope you\'re enjoying it.<br /><br />Could you please do us a <strong>BIG favor</strong> and give it a 5-star rating on WordPress.org to help us spread the word and boost our motivation?',
					'stripe'
				),
				'actions'    => array(
					array(
						'type' => 'primary',
						'text' => __( 'Yes! I\'ll help spread the word', 'stripe' ),
						'url'  => 'https://wordpress.org/support/plugin/stripe/reviews/?filter=5#new-post',
					),
					array(
						'type' => 'secondary',
						'text' => __( 'Not really', 'stripe' ),
						'url'  => simpay_ga_url(
							'https://wpsimplepay.com/plugin-feedback/',
							'admin-notice',
							'Give Feedback'
						),
					),
				),
				'conditions' => array(),
				'start'      => date( 'Y-m-d H:i:s', time() ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				'end'        => date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS * 10 ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			)
		);
	}
}
