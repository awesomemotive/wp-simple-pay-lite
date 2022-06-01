<?php
/**
 * Admin education: License settings upgrade
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
 * PluginLicenseSettings class.
 *
 * @since 4.4.0
 */
class PluginLicenseSettings extends AbstractProductEducation implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'__unstable_simpay_license_field' => 'license_upgrade',
		);
	}

	/**
	 * Outputs a license upgrade view if using Personal.
	 *
	 * @todo use a ViewLoader
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function license_upgrade() {
		if ( true === $this->license->is_pro( 'personal', '>' ) ) {
			return;
		}

		// Dismissed temporary notice.
		$dismissed_notice = (bool) Persistent_Dismissible::get(
			array(
				'id' => 'simpay-settings-pro-license-upgrade',
			)
		);

		if ( true === $dismissed_notice ) {
			return;
		}

		$utm_medium            = 'license-settings';
		$utm_content           = 'See Upgrade Options';
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_text          = $this->get_upgrade_button_text();
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);

		include_once SIMPLE_PAY_DIR . 'views/admin-license-settings-upgrade.php'; // @phpstan-ignore-line
	}

}
