<?php
/**
 * Admin education: Settings upgrade
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * SettingsUpgrade class.
 *
 * @since 4.4.0
 */
class SettingsUpgrade extends AbstractProductEducation implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'__unstable_simpay_admin_page_settings_end' => 'settings_upgrade',
		);
	}

	/**
	 * Outputs a settings upgrade view if using Lite.
	 *
	 * @todo use a ViewLoader
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function settings_upgrade() {
		if ( false === $this->license->is_lite() ) {
			return;
		}

		// Dismissed temporary notice.
		$dismissed_notice = (bool) Persistent_Dismissible::get(
			array(
				'id' => 'simpay-settings-license-upgrade',
			)
		);

		if ( true === $dismissed_notice ) {
			return;
		}

		if ( false === $this->display_upgrade_notice() ) {
			return;
		}

		$upgrade_text          = $this->get_upgrade_button_text();
		$utm_medium            = 'global-settings';
		$utm_content           = $upgrade_text;
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);

		include_once SIMPLE_PAY_DIR . 'views/admin-settings-upgrade.php'; // @phpstan-ignore-line
	}

	/**
	 * Determines if the upgrade notice should be displayed.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	private function display_upgrade_notice() {
		$blocklist = $this->get_settings_display_blocklist();

		$section = isset( $_GET['tab'] )
			? sanitize_text_field( $_GET['tab'] )
			: 'general';

		$subsection = isset( $_GET['subsection'] )
			? sanitize_text_field( $_GET['subsection'] )
			: 'general';

		// Tab (section) is in the blocklist.
		if ( true === array_key_exists( $section, $blocklist ) ) {

			if (
				empty( $blocklist[ $section ] ) || // Empty subsections, block all.
				in_array( $subsection, $blocklist[ $section ], true ) // Block specific subsection.
			) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a list of settings tabs (sections) and subtabs (subsections) that
	 * should not show the upgrade notice. These tabs already have a feature teaser/upsell.
	 *
	 * @since 4.4.0
	 *
	 * @return array<string, array<mixed>>
	 */
	private function get_settings_display_blocklist() {
		return array(
			'general'   => array( 'taxes' ),
			'emails'    => array(),
			'customers' => array(),
		);
	}

}
