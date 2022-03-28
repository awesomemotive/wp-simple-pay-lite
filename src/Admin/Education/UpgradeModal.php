<?php
/**
 * Admin: Upgrade modal
 *
 * Ensures each WP Simple Pay page outputs the markup for the upgrade modal.
 * The client uses this modal markup with the jQuery UI Dialog plugin where
 * non-React JavaScript is being used.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * UpgradeModal class.
 *
 * @since 4.4.4
 */
class UpgradeModal implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_print_footer_scripts'  => 'maybe_output_upgrade_modal',
		);
	}

	/**
	 * Outputs markup (a hidden div) for the upgrade modal on WP Simple Pay pages.
	 *
	 * @since 4.4.4
	 *
	 * @return void
	 */
	public function maybe_output_upgrade_modal() {
		if ( false === simpay_is_admin_screen() ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		$license = $this->license;

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-upgrade-modal.php'; // @phpstan-ignore-line
	}

}
