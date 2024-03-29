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
			'in_admin_header' => 'add_page_branding',
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
		if ( ! simpay_is_admin_screen() ) {
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
