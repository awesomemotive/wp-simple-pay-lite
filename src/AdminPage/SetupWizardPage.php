<?php
/**
 * Admin: "Setup Wizard" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\AdminPage;

use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Settings;

/**
 * SetupWizardPage class.
 *
 * @since 4.4.2
 */
class SetupWizardPage extends AbstractAdminPage implements AdminSecondaryPageInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 99;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_capability_requirement() {
		return 'manage_options';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_title() {
		return __( 'Setup Wizard', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'Setup Wizard', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'simpay-setup-wizard';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_parent_slug() {
		return 'edit.php?post_type=simple-pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_block_editor() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
		$asset = SIMPLE_PAY_INC . 'core/assets/js/simpay-admin-page-setup-wizard.min.asset.php'; // @phpstan-ignore-line

		if ( file_exists( $asset ) ) {
			$asset_data = include_once $asset;

			wp_enqueue_script(
				'simpay-setup-wizard',
				SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-admin-page-setup-wizard.min.js', // @phpstan-ignore-line
				$asset_data['dependencies'],
				$asset_data['version'],
				true
			);

			wp_localize_script(
				'simpay-setup-wizard',
				'simpaySetupWizard',
				array(
					'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
					'adminUrl'            => Settings\get_url(
						array(
							'section' => 'general',
						)
					),
					'adminEmail'          => get_option( 'admin_email', '' ),
					'stripeConnectUrl'    => simpay_get_stripe_connect_url(
						$this->get_wizard_url()
					),
					'upgradeUrl'          => simpay_pro_upgrade_url(
						'setup-wizard'
					),
					'accountLicensesUrl'  => simpay_ga_url(
						'https://wpsimplepay.com/my-account/licenses/',
						'setup-wizard',
						'WP Simple Pay account'
					),
					'newFormUrl'          => add_query_arg(
						array(
							'post_type' => 'simple-pay',
						),
						admin_url( 'post-new.php' )
					),
					'taxSettingsUrl'      => Settings\get_url(
						array(
							'section'    => 'general',
							'subsection' => 'taxes',
							'setting'    => 'taxes',
						)
					),
					'captchaSettingsUrl'  => Settings\get_url(
						array(
							'section'    => 'general',
							'subsection' => 'recaptcha',
						)
					),
					'currencySettingsUrl' => Settings\get_url(
						array(
							'section'    => 'general',
							'subsection' => 'currency',
							'setting'    => 'currency',
						)
					),
					'receiptSettingsUrl'  => Settings\get_url(
						array(
							'section'    => 'payment-confirmations',
							'subsection' => 'one-time',
						)
					),
					'donationsDocsUrl'    => simpay_docs_link(
						'Accept Donations',
						'accepting-donations-form-setup',
						'setup-wizard',
						true
					),
					'invoicesDocsUrl'     => simpay_docs_link(
						'Reconcile Invoices',
						'invoice-payment-form-set-up',
						'setup-wizard',
						true
					),
					'analyticsDocsUrl'    => simpay_docs_link(
						'usage analytics',
						'advanced-settings',
						'setup-wizard',
						true
					),
					'license'             => $this->license->to_array(),
					'subscribeNonce'      => wp_create_nonce( 'simpay-setup-wizard-subscribe' ),
					'licenseNonce'        => wp_create_nonce( 'simpay-manage-license' ),
				)
			);

			wp_set_script_translations(
				'simpay-setup-wizard',
				'simple-pay',
				SIMPLE_PAY_DIR . '/languages' // @phpstan-ignore-line
			);

			wp_enqueue_style(
				'simpay-setup-wizard',
				SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-page-setup-wizard.min.css', // @phpstan-ignore-line
				array(
					'wp-components',
				),
				$asset_data['version']
			);
		}

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-page-setup-wizard.php'; // @phpstan-ignore-line
	}

	/**
	 * Returns the URL to the Setup Wizard.
	 *
	 * @todo Should this be a public method so the page can be used as a dependency?
	 *
	 * @since 4.4.2
	 *
	 * @return string
	 */
	private function get_wizard_url() {
		return add_query_arg(
			array(
				'post_type'           => 'simple-pay',
				'page'                => 'simpay-setup-wizard',
				'step'                => 'stripe',
				'stripe-is-connected' => true,
			),
			admin_url( 'edit.php' )
		);
	}

}
