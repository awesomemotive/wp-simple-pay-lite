<?php
/**
 * Admin: Instant payouts education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 */

namespace SimplePay\Core\Admin\Education;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Settings;

/**
 * InstantPayouts class.
 *
 * @since 4.4.0
 */
class InstantPayouts implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			// Global settings.
			'simpay_register_settings_subsections' => 'register_settings_subsection',
			'simpay_admin_page_settings_keys_end'  => 'output_global_settings_page',

			// Form settings.
			'simpay_form_settings_meta_payment_options_panel' => array(
				'output_form_settings_notice',
				4,
			),
		);
	}

	/**
	 * Registers the "Instant Payouts" settings subsections.
	 *
	 * @since 4.4.4
	 *
	 * @param \SimplePay\Core\Settings\Subsection_Collection<\SimplePay\Core\Settings\Subsection> $subsections Subsections collection.
	 * @return void
	 */
	function register_settings_subsection( $subsections ) {
		if ( false === $this->has_account_support() ) {
			return;
		}

		$subsections->add(
			new Settings\Subsection(
				array(
					'id'       => 'instant-payouts',
					'section'  => 'stripe',
					'label'    => esc_html_x(
						'Instant Payouts',
						'settings subsection label',
						'stripe'
					),
					'priority' => 60,
				)
			)
		);
	}

	/**
	 * Outputs the "Instant Payouts" product education in the global settings.
	 *
	 * @since 4.4.4
	 *
	 * @return void
	 */
	public function output_global_settings_page() {
		if ( false === $this->has_account_support() ) {
			return;
		}

		$subsection = isset( $_GET['subsection'] )
			? sanitize_text_field( $_GET['subsection'] )
			: false;

		if ( false === $subsection || 'instant-payouts' !== $subsection ) {
			return;
		}

		add_filter(
			'simpay_admin_page_settings_keys_submit',
			'__return_false'
		);

		$docs_url = simpay_docs_link(
			'Instant Payouts documentation',
			'stripe-instant-payouts',
			'instant-payout-settings',
			true
		);

		$enroll_url = simpay_ga_url(
			'https://wpsimplepay.com/stripe-instant-payouts-refer',
			'instant-payout-settings',
			'Enable Stripe Instant Payouts'
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-plugin-instant-payouts-settings.php'; // @phpstan-ignore-line
	}

	/**
	 * Outputs the "Instant Payouts" product education in the form settings.
	 *
	 * @since 4.4.4
	 *
	 * @return void
	 */
	public function output_form_settings_notice() {
		if ( false === $this->has_account_support() ) {
			return;
		}

		// Dismissed temporary notice.
		$dismissed_notice = (bool) Persistent_Dismissible::get(
			array(
				'id' => 'simpay-form-settings-instant-payouts-education',
			)
		);

		if ( true === $dismissed_notice ) {
			return;
		}

		$enroll_url = simpay_ga_url(
			'https://wpsimplepay.com/stripe-instant-payouts-refer',
			'form-payment-settings',
			'Enable Stripe Instant Payouts'
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-instant-payouts-settings.php'; // @phpstan-ignore-line
	}

	/**
	 * Determines if the current account country supports instant payouts.
	 *
	 * @since 4.4.4
	 *
	 * @return bool
	 */
	private function has_account_support() {
		$instant_payout_countries = array(
			'ca',
			'sg',
			'us',
			'gb',
		);

		/** @var string $account_country */
		$account_country = simpay_get_setting( 'account_country', 'US' );
		$account_country = strtolower( $account_country );

		return in_array( $account_country, $instant_payout_countries, true );
	}

}
