<?php
/**
 * Admin: Stripe Connect notification
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2024, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.12
 */

namespace SimplePay\Core\NotificationInbox\Notifications;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;

/**
 * StripeConnectNotification
 *
 * @since 4.7.12
 */
class StripeConnectNotification implements SubscriberInterface, NotificationAwareInterface {

	use NotificationAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( simpay_get_secret_key() && false !== simpay_get_account_id() ) {
			return array();
		}

		return array(
			'admin_init'                      => array( 'add_notification' ),
			'simpay_stripe_account_connected' => array( 'dismiss_notification' ),
		);
	}

	/**
	 * Adds a notification to connect with Stripe if the user has not connected,
	 * but has a secret key.
	 *
	 * @since 4.7.12
	 *
	 * @return void
	 */
	public function add_notification() {
		$notification = $this->notifications->get_by( 'slug', 'stripe-connect-4120' );

		if ( $notification instanceof Notification && $notification->dismissed ) {
			return;
		}

		$this->notifications->restore(
			array(
				'type'       => 'info',
				'source'     => 'internal',
				'title'      => __(
					'🔐 Stay Secure with Stripe Connect',
					'stripe'
				),
				'slug'       => 'stripe-connect-4120',
				'content'    => 'Join the other WP Simple Pay users who have used Stripe Connect to even <strong>more securely</strong> connect their Stripe account to WP SimplePay. Stripe Connect uses a limited API key that can\'t interact with unrelated parts of Stripe\'s API to reduce risk.',
				'actions'    => array(
					array(
						'type' => 'primary',
						'text' => __( 'Connect Now', 'stripe' ),
						'url'  => simpay_get_stripe_connect_url(),
					),
					array(
						'type' => 'secondary',
						'text' => __( 'Learn More', 'stripe' ),
						'url'  => simpay_docs_link(
							'',
							'stripe-setup',
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
	 * Dismisses the Stripe Connect notification once connected.
	 *
	 * @since 4.7.12
	 *
	 * @return void
	 */
	public function dismiss_notification() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->notifications->dismiss( 'stripe-connect-4120' );
	}
}
