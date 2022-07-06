<?php
/**
 * Admin: Education plugin "Email" settings
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
use SimplePay\Core\Utils;

/**
 * PluginEmailSettings class.
 *
 * @since 4.4.0
 */
class PluginEmailSettings extends AbstractProductEducation implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( false === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'simpay_register_settings_sections'     => 'register_section',
			'simpay_register_settings_subsections'  => 'register_subsections',
			'simpay_admin_page_settings_emails_end' => 'upsell',
		);
	}

	/**
	 * Registers the "Email" settings section (tab).
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\Settings\Section_Collection<\SimplePay\Core\Settings\Section> $sections Section collection.
	 * @return void
	 */
	function register_section( $sections ) {
		$emails = new Settings\Section(
			array(
				'id'       => 'emails',
				'label'    => esc_html_x(
					'Emails',
					'settings subsection label',
					'stripe'
				),
				'priority' => 60,
			)
		);

		$sections->add( $emails );
	}

	/**
	 * Registers the "Email" settings subsections.
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

		// Emails.
		$emails = $this->get_registered_emails();

		foreach ( $emails as $email_id => $email ) {
			$subsection = new Settings\Subsection(
				array(
					'id'      => $email_id,
					'section' => 'emails',
					'label'   => $email,
				)
			);

			$subsections->add( $subsection );
		}
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
			'simpay_admin_page_settings_emails_submit',
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

		/** @var \SimplePay\Core\Settings\Subsection_Collection<\SimplePay\Core\Settings\Subsection> $subsections */
		$subsections = Utils\get_collection( 'settings-subsections' );
		$subsection  = isset( $_GET['subsection'] )
			? sanitize_text_field( $_GET['subsection'] )
			: 'general';

		$title = __( '️📨 Customize Emails Receipts and More', 'stripe' );

		if ( 'general' !== $subsection && $subsections->get_item( $subsection ) ) {
			/** @var \SimplePay\Core\Settings\Subsection $obj */
			$obj = $subsections->get_item( $subsection );

			if ( $obj instanceof \SimplePay\Core\Settings\Subsection ) {
				$title = sprintf(
					/* translators: Email label. */
					__( 'Customize the "%s" Email', 'stripe' ),
					esc_html( $obj->label )
				);
			}
		}

		$utm_medium            = 'email-settings';
		$utm_content           = 'Customize Email Receipts and More';
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
		include_once SIMPLE_PAY_DIR . '/views/admin-education-plugin-email-settings.php'; // @phpstan-ignore-line
	}

	/**
	 * Returns a list of registered emails.
	 *
	 * The true registry is not available in the Lite plugin, so we create a
	 * manual list here.
	 *
	 * @since 4.4.0
	 *
	 * @return array<string, string>
	 */
	private function get_registered_emails() {
		return array(
			'payment-receipt'      => __( 'Payment Confirmation', 'stripe' ),
			'payment-notification' => __( 'Payment Notification', 'stripe' ),
			'upcoming-invoice'     => __( 'Upcoming Invoice', 'stripe' ),
		);
	}

}
