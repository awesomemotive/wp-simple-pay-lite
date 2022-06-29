<?php
/**
 * Admin bar: Subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\AdminBar;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\Settings;

/**
 * AdminBarSubscriber class.
 *
 * @since 4.4.5
 */
class AdminBarSubscriber implements SubscriberInterface {

	/**
	 * Notifications.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\NotificationInbox\NotificationRepository
	 */
	private $notifications;

	/**
	 * AdminBarSubscriber.
	 *
	 * @since 4.4.5
	 *
	 * @param null|\SimplePay\Core\NotificationInbox\NotificationRepository $notifications Notifications
	 */
	public function __construct( $notifications ) {
		if ( $notifications instanceof NotificationRepository ) {
			$this->notifications = $notifications;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array();
		}

		return array(
			'admin_bar_menu' => array( 'add_menu_item', 999 ),
		);
	}

	/**
	 * Adds a menu bar item and chidlren.
	 *
	 * @since 4.4.5
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 * @return void
	 */
	public function add_menu_item( $wp_admin_bar ) {
		wp_enqueue_style(
			'simpay-admin-bar',
			SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-bar.min.css', // @phpstan-ignore-line
			array(),
			SIMPLE_PAY_VERSION // @phpstan-ignore-line
		);

		// Show the notification count on the frontend and on non-WPSP admin screens.
		$use_notification_inbox = (
			$this->notifications instanceof NotificationRepository &&
			(
				! is_admin() ||
				( is_admin() && ! simpay_is_admin_screen() ) // @phpstan-ignore-line
			)
		);
		$notifications_string   = '';
		$notifications          = 0;

		/** This filter is documented in src/Admin/AdminBranding.php */
		$use_notification_inbox = apply_filters(
			'simpay_use_notification_inbox',
			$use_notification_inbox
		);

		if ( false !== $use_notification_inbox ) {
			$notifications        = $this->notifications->get_unread_count();
			$notifications_string = $notifications > 0
				? $this->get_bubble_markup( (string) $notifications )
				: '';
		}

		$settings_url  = Settings\get_url();

		// Parent.
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'simpay-admin-bar-test-mode',
				'parent' => 'top-secondary',
				'href'   => $settings_url,
				'title'  => (
					__( 'WP Simple Pay', 'stripe' ) .
					$notifications_string .
					(
						simpay_is_test_mode()
							? ' <span class="simpay-test-mode-badge">' . __( 'Test Mode', 'stripe' ) . '</span>'
							: ''
					)
				),
				'meta'   => array( 'class' => 'simpay-admin-bar-test-mode' ),
			)
		);

		// Notifications.
		if ( false !== $use_notification_inbox && $notifications > 0 ) {
			$notifications_url = is_admin() && simpay_is_admin_screen()
				? '#notifications'
				: $settings_url . '#notifications';

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'simpay-admin-bar-test-mode',
					'id'     => 'simpay-notifications',
					'title'  => esc_html__(
						'Notifications',
						'stripe'
					) . $this->get_bubble_markup( '' ),
					'href'   => $notifications_url,
				)
			);
		}

		// All forms.
		$forms_url = add_query_arg(
			array(
				'post_type' => 'simple-pay'
			),
			admin_url( 'edit.php' )
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'simpay-admin-bar-test-mode',
				'id'     => 'simpay-forms',
				'title'  => esc_html__( 'All Forms', 'stripe' ),
				'href'   => $forms_url,
			)
		);

		// Settings.
		$wp_admin_bar->add_menu(
			array(
				'parent' => 'simpay-admin-bar-test-mode',
				'id'     => 'simpay-settings',
				'title'  => esc_html__( 'Settings', 'stripe' ),
				'href'   => $settings_url,
			)
		);

		// Support.
		$support_url = simpay_ga_url(
			'https://docs.wpsimplepay.com',
			'admin-bar',
			'Support'
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'simpay-admin-bar-test-mode',
				'id'     => 'simpay-support',
				'title'  => esc_html__( 'Support', 'stripe' ),
				'href'   => $support_url,
				'meta'   => array(
					'target' => '_blank',
				),
			)
		);
	}

	/**
	 * Returns markup for a notification bubble.
	 *
	 * @since 4.4.5
	 *
	 * @param string $inner HTML to be inside the bubble.
	 * @return string
	 */
	private function get_bubble_markup( $inner ) {
		return sprintf(
			'<span class="simpay-settings-bubble simpay-no-webhooks-bubble wp-ui-notification">%s</span>',
			$inner
		);
	}

}