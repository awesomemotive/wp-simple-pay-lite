<?php
/**
 * Admin: Page branding
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use WP_Screen;

/**
 * AdminBranding class.
 *
 * @since 4.4.0
 */
class AdminBranding implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_notices' => 'add_page_branding',
		);
	}

	/**
	 * Outputs a WP Simple Pay branding bar if we are on a plugin page.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function add_page_branding() {
		$current_screen = get_current_screen();

		if ( ! $current_screen instanceof WP_Screen ) {
			return;
		}

		// Not on a WP Simple Pay page, show nothing.
		if (
			'simple-pay' !== $current_screen->post_type &&
			'edit.php?post_type=simple-pay' !== $current_screen->parent_file
		) {
			return;
		}

		$logo_url = '';

		if ( true === $this->license->is_lite() ) {
			$logo_url = simpay_pro_upgrade_url( 'header-logo' );
		}

		$this->enqueue_notification_inbox();

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-branding-bar.php'; // @phpstan-ignore-line
	}

	/**
	 * Enqueues notification inbox scripts and styles.
	 *
	 * @todo This probably should go somewhere better.
	 *
	 * @since 4.x.x
	 *
	 * @return void
	 */
	private function enqueue_notification_inbox() {
		$use_notification_inbox = true;

		/**
		 * Filters the notification inbox output.
		 *
		 * @since 4.x.x
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
	}

}
