<?php
/**
 * Admin: Education plugin "Subscription Management" settings
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Settings;

/**
 * PluginCustomersSettings class.
 *
 * @since 4.4.0
 */
class PluginCustomersSettings extends AbstractProductEducation implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		// Lite or Personal.
		if (
			true === $this->license->is_lite() ||
			false === $this->license->is_pro( 'personal', '>' )
		) {
			return array(
				'simpay_register_settings_sections'        => 'register_section',
				'simpay_register_settings_subsections'     => 'register_subsections',
				'simpay_admin_page_settings_customers_end' => 'upsell',
			);
		}

		// Non-Personal Pro.
		return array();
	}

	/**
	 * Registers the "Subscription Management" settings section (tab).
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\Settings\Section_Collection<\SimplePay\Core\Settings\Section> $sections Section collection.
	 * @return void
	 */
	function register_section( $sections ) {
		$emails = new Settings\Section(
			array(
				'id'       => 'customers',
				'label'    => esc_html_x(
					'Subscription Management',
					'settings subsection label',
					'stripe'
				),
				'priority' => 60,
			)
		);

		$sections->add( $emails );
	}

	/**
	 * Registers the "Subscription Management" settings subsections.
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\Settings\Subsection_Collection<\SimplePay\Core\Settings\Subsection> $subsections Subsections collection.
	 * @return void
	 */
	function register_subsections( $subsections ) {
		// General.
		$general = new Settings\Subsection(
			array(
				'id'      => 'general',
				'section' => 'emails',
				'label'   => esc_html_x(
					'General',
					'settings subsection label',
					'stripe'
				),
			)
		);

		$subsections->add( $general );
	}

	/**
	 * Outputs the "Emails" feature upsell.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function upsell() {
		add_filter(
			'simpay_admin_page_settings_customers_submit',
			'__return_false'
		);

		// Lightweight, accessible and responsive lightbox.
		wp_enqueue_style(
			'simpay-lity',
			SIMPLE_PAY_INC_URL . 'core/assets/css/vendor/lity/lity.min.css', // @phpstan-ignore-line
			array(),
			'3.0.0'
		);

		wp_enqueue_script(
			'simpay-lity',
			SIMPLE_PAY_INC_URL . 'core/assets/js/vendor/lity.min.js', // @phpstan-ignore-line
			array( 'jquery' ),
			'3.0.0',
			true
		);

		$utm_medium            = 'subscription-management-settings';
		$utm_content           = 'Allow Customers to Manage Subscriptions';
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

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-plugin-customers-settings.php'; // @phpstan-ignore-line
	}

}
