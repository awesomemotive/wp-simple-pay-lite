<?php
/**
 * Webhook: None received notice
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\Webhook;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\Settings;
use SimplePay\Pro\Webhooks\Database\Query as WebhookDatabase;

/**
 * NoneReceivedNotice class.
 *
 * @since 4.4.1
 */
class NoneReceivedNotice implements SubscriberInterface, LicenseAwareInterface, NotificationAwareInterface {

	use LicenseAwareTrait;
	use NotificationAwareTrait;

	/**
	 * The most recent event timestamp.
	 *
	 * @since 4.4.1
	 * @var int|null|false
	 */
	private $most_recent_event = false;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		$check_received_events = true;

		/**
		 * Filters if the webhooks should be checked for received events to notify
		 * the site admin about configuration issues.
		 *
		 * @since 4.4.1
		 *
		 * @param bool $check_received_events Whether to check for received events.
		 */
		$check_received_events = apply_filters(
			'simpay_webhooks_check_received_events',
			$check_received_events
		);

		$dismissed = (bool) get_option(
			'simpay_webhook_event_expected_dismiss',
			false
		);

		if ( false === $check_received_events || true === $dismissed ) {
			return array();
		}

		$subscribers = array(
			// Log when an incoming event should be expected.
			'simpay_after_checkout_session_from_payment_form_request' =>
				'log_expected_event',
			'simpay_after_paymentintent_from_payment_form_request'    =>
				'log_expected_event',
			'simpay_after_subscription_from_payment_form_request'     =>
				'log_expected_event',
			'simpay_after_charge_from_payment_form_request'           =>
				'log_expected_event',

			// Show a notice in the setting.
			'__unstable_simpay_before_webhook_setting'                =>
				'maybe_show_setting_notice',

			// Clear the "expected" flag when a user claims they have verified their settings.
			'wpsp_transition_notification_dismissed' =>
				array( 'clear_expected_event_flag', 10, 3 ),

			// Allow permanently dismissing the notice/notification.
			'admin_init'                                              =>
				array(
					array( 'dismiss_expected_event' )
				),
		);

		// Alert via Notification Inbox if available.
		if ( $this->notifications instanceof NotificationRepository ) {
			$subscribers['admin_init'][] = array( 'maybe_add_notification' );
		}

		return $subscribers;
	}

	/**
	 * Logs that a webhook event should be received in the near future.
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function log_expected_event() {
		$option_key = sprintf(
			'simpay_webhook_event_expected_%s',
			simpay_is_test_mode() ? 'test' : 'live'
		);

		update_option( $option_key, time() );
	}

	/**
	 * Adds a notification if an event has not been received.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function maybe_add_notification() {
		if ( true === $this->received_expected_event() ) {
			return;
		}

		$this->notifications->restore(
			array(
				'type'           => 'error',
				'source'         => 'internal',
				'title'          => __(
					'An expected webhook event was not received.',
					'stripe'
				),
				'slug'           => 'webhook-event-expected',
				'content'        => __(
					'An expected webhook event has not been received. Please ensure you have properly configured your webhook endpoint in Stripe to avoid interruption of functionality.',
					'stripe'
				),
				'actions'        => array(
					array(
						'type' => 'primary',
						'text' => __( 'Webhook Settings', 'stripe' ),
						'url'  => Settings\get_url(
							array(
								'section'    => 'stripe',
								'subsection' => 'webhooks',
							)
						)
					),
					array(
						'type' => 'secondary',
						'text' => __( 'Learn More', 'stripe' ),
						'url'  => 'https://docs.wpsimplepay.com/article/webhooks',
					),
				),
				'conditions'     => array(),
				'start'          => date( 'Y-m-d H:i:s', time() ),
				'end'            => date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
			)
		);
	}

	/**
	 * Ooutputs a notice in the settings if no expected event was received.
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function maybe_show_setting_notice() {
		if ( true === $this->received_expected_event() ) {
			return;
		}

		$docs_url = simpay_docs_link(
			'Learn more',
			'webhooks',
			'webhook-settings',
			true
		);

		$base_url = Settings\get_url(
			array(
				'section'    => 'stripe',
				'subsection' => 'webhooks',
			)
		);

		$verify_url = add_query_arg(
			array(
				'action' => 'simpay_verify_webhook',
				'nonce'  => wp_create_nonce( 'simpay_verify_webhook' ),
			),
			$base_url
		);

		$dismiss_url = add_query_arg(
			array(
				'action' => 'simpay_dismiss_webhook',
				'nonce'  => wp_create_nonce( 'simpay_dismiss_webhook' ),
			),
			$base_url
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-webhooks-none-received.php'; // @phpstan-ignore-line
	}

	/**
	 * Clears the expected event flag when a user dismisses the notification.
	 *
	 * This will hide the notices until the next expected event is received.
	 *
	 * @since 4.4.1
	 *
	 * @param mixed $old_value Old value.
	 * @param mixed $new_value New value.
	 * @param int $notification_id Notification ID.
	 * @return void
	 */
	public function clear_expected_event_flag( $old_value, $new_value, $notification_id ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notification = $this->notifications->get( $notification_id );

		if ( ! $notification instanceof Notification ) {
			return;
		}

		// Ensure we are dismissing the relevant notification.
		if ( 'webhook-event-expected' !== $notification->slug ) {
			return;
		}

		if ( '0' !== $old_value ) {
			return;
		}

		// Clear the flag.
		$option_key = sprintf(
			'simpay_webhook_event_expected_%s',
			simpay_is_test_mode() ? 'test' : 'live'
		);

		delete_option( $option_key );
	}

	/**
	 * Permanately dismisses the "No webhooks received" notice.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function dismiss_expected_event() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (
			! isset( $_GET['action'] ) ||
			'simpay_dismiss_webhook' !== sanitize_text_field( $_GET['action'] )
		) {
			return;
		}

		$nonce = isset( $_GET['nonce'] )
			? sanitize_text_field( $_GET['nonce'] )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'simpay_dismiss_webhook' ) ) {
			return;
		}

		// Clear expected event.
		$option_key = sprintf(
			'simpay_webhook_event_expected_%s',
			simpay_is_test_mode() ? 'test' : 'live'
		);

		delete_option( $option_key );

		// Flag permanent dismissal.
		update_option( 'simpay_webhook_event_expected_dismiss', true );

		$settings_url = Settings\get_url(
			array(
				'section'    => 'stripe',
				'subsection' => 'webhooks',
			)
		);

		wp_safe_redirect( esc_url_raw( $settings_url ) );
		exit;
	}

	/**
	 * Determines if the most recently received webhook event falls within the expected
	 * timeframe after the last time an event was expected.
	 *
	 * @since 4.1.1
	 *
	 * @return bool
	 */
	public function received_expected_event() {
		$timeframe = MINUTE_IN_SECONDS * 15;
		$expected  = $this->get_most_recent_expected_event();

		// No event was expected, then we can't assume anything went wrong.
		if ( null === $expected ) {
			return true;
		}

		// The timeframe from the expected event has not passed yet, assume things will still work.
		if ( time() < ( $expected + $timeframe ) ) {
			return true;
		}

		// If there is no received event after the expected event log but the timeframe
		// has passed then something went wrong.
		$received = $this->get_most_recent_received_event();

		if ( null === $received ) {
			return false;
		}

		return $received > $expected;
	}

	/**
	 * Returns the timestamp of the latest received webhook event.
	 *
	 * @since 4.4.1
	 *
	 * @return null|int
	 */
	private function get_most_recent_received_event() {
		if ( false !== $this->most_recent_event ) {
			return $this->most_recent_event;
		}

		$expected = $this->get_most_recent_expected_event();

		// No event is expected so we can't use that as our starting point.
		if ( null === $expected ) {
			$this->most_recent_event = null;
			return $this->most_recent_event;
		}

		// @todo Use dependency injection when there is a proper repository model.
		$db       = new WebhookDatabase();
		$livemode = simpay_is_livemode();

		$webhooks = $db->query(
			array(
				'number'     => 1,
				'livemode'   => $livemode,
				'date_query' => array(
					'after' => date( 'Y-m-d H:i:s', $expected ),
				),
			)
		);

		if ( empty( $webhooks ) ) {
			$this->most_recent_event = null;
			return $this->most_recent_event;
		}

		/** @var array<\SimplePay\Pro\Webhooks\Database\Row> $webhooks */

		$created = current( $webhooks )
			? current( $webhooks )->date_created
			: null;

		if ( null === $created ) {
			$this->most_recent_event = null;
			return $this->most_recent_event;
		}

		/** @var string $created */

		$this->most_recent_event = strtotime( $created ) ? strtotime( $created ) : null;

		return $this->most_recent_event;
	}

	/**
	 * Returns the timestamp of when the latest event that expects a webhook event occured.
	 *
	 * @since 4.4.1
	 *
	 * @return null|int
	 */
	private function get_most_recent_expected_event() {
		$option_key = sprintf(
			'simpay_webhook_event_expected_%s',
			simpay_is_test_mode() ? 'test' : 'live'
		);

		$expected = get_option( $option_key, null );

		if ( null === $expected ) {
			return null;
		}

		/** @var string $expected */

		return (int) $expected;
	}

}
