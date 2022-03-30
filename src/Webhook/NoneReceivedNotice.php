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
use SimplePay\Core\Settings;
use SimplePay\Pro\Webhooks\Database\Query as WebhookDatabase;

/**
 * NoneReceivedNotice class.
 *
 * @since 4.4.1
 */
class NoneReceivedNotice implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

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

		return array(
			// Log when an incoming event should be expected.
			'simpay_after_checkout_session_from_payment_form_request' =>
				'log_expected_event',
			'simpay_after_paymentintent_from_payment_form_request'    =>
				'log_expected_event',
			'simpay_after_subscription_from_payment_form_request'     =>
				'log_expected_event',
			'simpay_after_charge_from_payment_form_request'           =>
				'log_expected_event',

			// Show a notice bubble in the admin bar.
			'admin_bar_menu'                                          =>
				array( 'maybe_show_admin_bar_bubble', 20 ),

			// Show a notice bubble in the admin menu.
			'simpay_settings_menu_name'                               =>
				'maybe_show_menu_bubble',

			// Show a notice bubble in the settings tab/subtabs.
			'simpay_register_settings_sections'                       =>
				'maybe_show_settings_section_bubble',
			'simpay_register_settings_subsections'                    =>
				'maybe_show_settings_subsection_bubble',

			// Show a notice in the setting.
			'__unstable_simpay_before_webhook_setting'                =>
				'maybe_show_setting_notice',

			// Clear the "expected" flag when a user claims they have verified their settings.
			'admin_init'                                              =>
				array(
					array( 'clear_expected_event' ),
					array( 'dismiss_expected_event' )
				),
		);
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
	 * Shows a notice bubble and submenu item in the admin bar if no expected
	 * webhook event was received.
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function maybe_show_admin_bar_bubble() {
		if (
			false === is_admin() ||
			true === $this->received_expected_event()
		) {
			return;
		}

		global $wp_admin_bar;

		$webhooks_url = Settings\get_url( array(
			'section'    => 'stripe',
			'subsection' => 'webhooks',
		) );

		// We are already showing the parent item in test mode.
		if ( simpay_is_test_mode() ) {
			$label = (
				__( 'WP Simple Pay', 'stripe' ) .
				' <span class="simpay-test-mode-badge">' . __( 'Test Mode', 'stripe' ) . '</span>' .
				$this->get_bubble_markup( '1' )
			);

			$url = Settings\get_url( array(
				'section'    => 'stripe',
				'subsection' => 'account',
				'setting'    => 'test_mode-enabled',
			) );
		} else {
			$label = (
				__( 'WP Simple Pay', 'stripe' ) .
				$this->get_bubble_markup( '1' )
			);

			$url = $webhooks_url;
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'simpay-admin-bar-test-mode',
				'href'   => $url,
				'parent' => 'top-secondary',
				'title'  => $label,
				'meta'   => array( 'class' => 'simpay-admin-bar-test-mode' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'simpay-admin-bar-test-mode',
				'id'     => 'simpay-webhook-none-received',
				'title'  => esc_html__(
					'Notifications',
					'stripe'
				) . $this->get_bubble_markup( '' ),
				'href'   => $webhooks_url,
			)
		);
	}

	/**
	 * Shows a notice bubble in the admin menu if no expected webhook event was
	 * received.
	 *
	 * @since 4.4.1
	 *
	 * @param string $name Menu item name.
	 * @return string
	 */
	public function maybe_show_menu_bubble( $name ) {
		if ( true === $this->received_expected_event() ) {
			return $name;
		}

		// Show 2 if the license is not valid.
		// @todo use a true inbox system count.
		if ( false === $this->license->is_valid() ) {
			return $name;
		}

		return $name . $this->get_bubble_markup( '1' );
	}

	/**
	 * Reregisters the "Stripe" settings section with a new label, including a
	 * bubble, if no expected webhook event was received.
	 *
	 * @since 4.4.1
	 *
     * @param \SimplePay\Core\Settings\Section_Collection<\SimplePay\Core\Settings\Section> $sections Sections collection.
	 * @return void
	 */
	public function maybe_show_settings_section_bubble( Settings\Section_Collection $sections ) {
		if ( true === $this->received_expected_event() ) {
			return;
		}

		// Don't show a redundant bubble when viewing the section.
		if ( isset( $_GET['tab'] ) && 'stripe' === sanitize_text_field( $_GET['tab'] ) ) {
			return;
		}

		// Reregister with the bubble.
		$sections->add(
			new Settings\Section(
				array(
					'id'       => 'stripe',
					'label'    => esc_html_x(
						'Stripe',
						'settings section label',
						'stripe'
					) . $this->get_bubble_markup( '' ),
					'priority' => 20,
				)
			)
		);
	}

	/**
	 * Reregisters the "Stripe > Webhook" settings subsection with a new label,
	 * including a bubble, if no expected webhook event was received.
	 *
	 * @since 4.4.1
	 *
     * @param \SimplePay\Core\Settings\Subsection_Collection<\SimplePay\Core\Settings\Subsection> $subsections Subsections collection.
	 * @return void
	 */
	public function maybe_show_settings_subsection_bubble(
		Settings\Subsection_Collection $subsections
	) {
		if ( true === $this->received_expected_event() ) {
			return;
		}

		// Reregister with the bubble.
		$subsections->add(
			new Settings\Subsection(
				array(
					'id'       => 'webhooks',
					'section'  => 'stripe',
					'label'    => esc_html_x(
						'Webhooks',
						'settings subsection label',
						'stripe'
					) . $this->get_bubble_markup( '1' ),
					'priority' => 20,
				)
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
	 * Clears the expected event flag when a user clicks "I have configured the Stripe webhooks".
	 *
	 * This will hide the notices until the next expected event is received.
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function clear_expected_event() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (
			! isset( $_GET['action'] ) ||
			'simpay_verify_webhook' !== sanitize_text_field( $_GET['action'] )
		) {
			return;
		}

		$nonce = isset( $_GET['nonce'] )
			? sanitize_text_field( $_GET['nonce'] )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'simpay_verify_webhook' ) ) {
			return;
		}

		$option_key = sprintf(
			'simpay_webhook_event_expected_%s',
			simpay_is_test_mode() ? 'test' : 'live'
		);

		delete_option( $option_key );

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

	/**
	 * Returns the markup for the notice bubble.
	 *
	 * @since 4.4.1
	 *
	 * @param string $content Bubble content.
	 * @return string
	 */
	private function get_bubble_markup( $content ) {
		return sprintf(
			'<span class="simpay-settings-bubble simpay-no-webhooks-bubble wp-ui-notification">%1$s</span>',
			$content
		);
	}

}
