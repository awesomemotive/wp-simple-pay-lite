<?php
/**
 * Admin: Subscription cancelled payment confirmation
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.14.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Settings;

/**
 * Plugin cancellation confirmation education.
 *
 * @since 4.14.0
 */
class PluginCancellationConfirmation extends AbstractProductEducation implements SubscriberInterface {


	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		$events = array();

		if ( true === $this->license->is_lite() ) {
			$events['simpay_admin_page_settings_display_end'] = 'upsell';
		}

		return $events;
	}



	/**
	 * Upsell the plugin.
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function upsell() {

		$upgrade_text           = $this->get_upgrade_button_text();
		$utm_medium             = 'payment-confirmation-settings';
		$utm_content            = 'Add a confirmation message when a subscription is cancelled';
		$upgrade_url            = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_subtext        = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url  = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);
		$already_purchased_text = esc_html__( 'Already purchased?', 'stripe' );

		include_once SIMPLE_PAY_DIR . '/views/admin-education-plugin-cancelled-payment-confirmation.php'; // @phpstan-ignore-line
	}
}
