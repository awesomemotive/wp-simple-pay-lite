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

		// Not on a WP Simple Pay page, show nothing.
		if (
			false === isset( $current_screen->post_type ) ||
			'simple-pay' !== $current_screen->post_type
		) {
			return;
		}

		$logo_url = '';

		if ( true === $this->license->is_lite() ) {
			$logo_url = simpay_pro_upgrade_url( 'header-logo' );
		}

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-branding-bar.php'; // @phpstan-ignore-line
	}

}
