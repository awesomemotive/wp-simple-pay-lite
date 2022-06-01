<?php
/**
 * Admin: Education plugin "Taxes" settings
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
 * PluginTaxesSettings class.
 *
 * @since 4.4.0
 */
class PluginTaxesSettings extends AbstractProductEducation implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( false === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'simpay_register_settings_subsections'   => 'register_subsections',
			'simpay_admin_page_settings_general_end' => 'upsell',
		);
	}

	/**
	 * Registers the "Taxes" settings subsections.
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\Settings\Subsection_Collection<\SimplePay\Core\Settings\Subsection> $subsections Subsections collection.
	 * @return void
	 */
	function register_subsections( $subsections ) {
		$taxes = new Settings\Subsection(
			array(
				'id'       => 'taxes',
				'section'  => 'general',
				'label'    => esc_html_x(
					'Taxes',
					'settings subsection label',
					'stripe'
				),
				'priority' => 20,
			)
		);

		$subsections->add( $taxes );
	}

	/**
	 * Outputs the "Taxes" feature upsell.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function upsell() {
		$subsection = isset( $_GET['subsection'] )
			? sanitize_text_field( $_GET['subsection'] )
			: false;

		if ( false === $subsection || 'taxes' !== $subsection ) {
			return;
		}

		add_filter(
			'simpay_admin_page_settings_general_submit',
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

		$utm_medium            = 'taxes';
		$utm_content           = 'Collect Taxes and Additional Fees';
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
		include_once SIMPLE_PAY_DIR . '/views/admin-education-plugin-taxes-settings.php'; // @phpstan-ignore-line
	}

}
