<?php
/**
 * Settings Registration: Stripe
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 *
 * @todo This should be inside of a "stripe" module.
 * Currently other related things exist inside of includes/core/stripe-connect
 */

namespace SimplePay\Core\Settings\Stripe;

use SimplePay\Core\Utils;
use SimplePay\Core\Settings;
use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the settings section.
 *
 * @param \SimplePay\Core\Settings\Section_Collection $sections Sections collection.
 */
function register_section( $sections ) {
	// Stripe.
	$sections->add(
		new Settings\Section(
			array(
				'id'       => 'stripe',
				'label'    => esc_html_x( 'Stripe', 'settings section label', 'stripe' ),
				'priority' => 10,
			)
		)
	);
}
add_action( 'simpay_register_settings_sections', __NAMESPACE__ . '\\register_section' );

/**
 * Registers settings subsections.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	// Account.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'account',
				'section'  => 'stripe',
				'label'    => esc_html_x( 'Account', 'settings subsection label', 'stripe' ),
				'priority' => 10,
			)
		)
	);

	// Locale.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'locale',
				'section'  => 'stripe',
				'label'    => esc_html_x( 'Locale', 'settings subsection label', 'stripe' ),
				'priority' => 20,
			)
		)
	);
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers the settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	register_account_settings( $settings );
	register_locale_settings( $settings );
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Registers settings for Stripe/Account subsection.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_account_settings( $settings ) {
	// Account.
	$settings->add(
		new Settings\Setting(
			array(
				'id'         => 'stripe_account',
				'section'    => 'stripe',
				'subsection' => 'account',
				'label'      => esc_html_x(
					'Connection Status',
					'setting label',
					'stripe'
				),
				'output'     => function() {
					$html = '';

					$mode = simpay_is_test_mode()
						? __( 'test', 'stripe' )
						: __( 'live', 'stripe' );

					// Need some sort of key (from a Connect account or manual) to check status.
					if ( simpay_check_keys_exist() ) {
						$html .= '<div id="simpay-stripe-account-info" class="simpay-stripe-account-info" data-account-id="' . simpay_get_account_id() . '" data-nonce="' . wp_create_nonce( 'simpay-stripe-connect-information' ) . '"><p><span class="spinner is-active"></span> <em>' . esc_html__( 'Retrieving account information...', 'stripe' ) . '</em></p></div>';
					}

					if ( false === simpay_get_account_id() || ! simpay_check_keys_exist() ) {
						$html .= '<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '" class="wpsp-stripe-connect"><span>' . __( 'Connect with Stripe', 'stripe' ) . '</span></a>';
					} else {
						$html .= '<p id="simpay-stripe-auth-error-account-actions" style="display: none;">' . sprintf(
							/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for reconnecting to Stripe, do not translate. %3$s Opening anchor tag for disconnecting Stripe, do not translate. %4$s Closing anchor tag, do not translate. */
							__( '%2$sReconnect in %1$s mode%4$s or %3$sdisconnect this account%5$s.', 'stripe' ),
							'<strong>' . $mode . '</strong>',
							'<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '" class="simpay-external-link">',
							'<a href="' . esc_url( simpay_get_stripe_disconnect_url() ) . '">',
							Utils\get_external_link_markup() . '</a>',
							'</a>'
						) . '</p>';

						$html .= '<p id="simpay-stripe-activated-account-actions" style="display: none;">' . sprintf(
							/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for reconnecting to Stripe, do not translate. %3$s Opening anchor tag for disconnecting Stripe, do not translate. %4$s Closing anchor tag, do not translate. */
							__( 'Your Stripe account is connected in %1$s mode. %2$sReconnect in %1$s mode%4$s or %3$sdisconnect this account%5$s.', 'stripe' ),
							'<strong>' . $mode . '</strong>',
							'<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '" class="simpay-external-link">',
							'<a href="' . esc_url( simpay_get_stripe_disconnect_url() ) . '">',
							Utils\get_external_link_markup() . '</a>',
							'</a>'
						) . '</p>';

						$html .= '<p id="simpay-stripe-unactivated-account-actions" style="display: none;"><a href="' . esc_url( simpay_get_stripe_disconnect_url() ) . '">' .
						__( 'Disconnect temporary account', 'stripe' ) .
						'</a></p>';
					}

					$html .= '<p class="simpay-stripe-connect-help description">';
					$html .= '<span class="dashicons dashicons-editor-help"></span><span>';
					$html .= sprintf(
						/* translators: %1$s Opening anchor tag for Stripe Connect documentation, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'Have questions about connecting with Stripe? %1$sView the Stripe Connect documentation%2$s', 'stripe' ),
						'<a href="' . simpay_docs_link( '', 'stripe-setup', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
						Utils\get_external_link_markup() . '</a>'
					);
					$html .= '</span></p>';

					// Only show buttons if we are managing keys, but none exist.
					// Otherwise the fields are auto shown.
					if ( simpay_can_site_manage_stripe_keys() ) {
						$html .= '<p id="wpsp-api-keys-row-reveal"><button type="button" class="button-link"><small>' . __( 'Manage API keys manually', 'stripe' ) . '</small></button></p>';
						$html .= '<p id="wpsp-api-keys-row-hide"><button type="button" class="button-link"><small>' . __( 'Hide API keys', 'stripe' ) . '</small></button></p>';

						$html .= '<div class="notice inline notice-warning wpsp-manual-key-warning" style="margin: 15px 0 -10px; display: none;">';
						$html .= wpautop(
							esc_html__(
								'Although you can add your API keys manually, we recommend using Stripe Connect: an easier and more secure way of connecting your Stripe account to your website. Stripe Connect prevents issues that can arise when copying and pasting account details from Stripe into WP Simple Pay\'s settings. With Stripe Connect you\'ll be ready to go with just a few clicks.',
								'stripe'
							)
						);
						$html .= '</div>';
					}

					return $html;
				},
			)
		)
	);

	// Keys.
	$keys = array(
		'test_secret_key'      => esc_html__( 'Test Secret Key', 'stripe' ),
		'test_publishable_key' => esc_html__( 'Test Publishable Key', 'stripe' ),
		'live_secret_key'      => esc_html__( 'Live Secret Key', 'stripe' ),
		'live_publishable_key' => esc_html__( 'Live Publishable Key', 'stripe' ),
	);

	$priority = 20;

	foreach ( $keys as $key => $label ) {
		$settings->add(
			new Settings\Setting_Input(
				array(
					'id'         => $key,
					'section'    => 'stripe',
					'subsection' => 'account',
					'label'      => $label,
					'value'      => simpay_get_setting( $key, '' ),
					'classes'    => array(
						'regular-text',
					),
					'priority'   => $priority,
				)
			)
		);

		$priority++;
	}

	// Test Mode.
	$toggle_notice = sprintf(
		'<p>%1$s</p>',
		esc_html__(
			'You just toggled payment modes. You may be required to reconnect to Stripe when your settings are saved.',
			'stripe'
		)
	);

	/**
	 * Filter the notice to be displayed when switching payment mode from Live to Test (or opposite).
	 *
	 * @since 3.5.0
	 *
	 * @param string $toggle_notice Toggle notice inner HTML.
	 */
	$toggle_notice = apply_filters(
		'simpay_payment_mode_toggle_notice',
		$toggle_notice
	);

	$settings->add(
		new Settings\Setting_Radio(
			array(
				'id'          => 'test_mode',
				'section'     => 'stripe',
				'subsection'  => 'account',
				'label'       => esc_html_x(
					'Test Mode',
					'setting label',
					'stripe'
				),
				'options'     => array(
					'enabled'  => esc_html_x( 'Enabled', 'setting label', 'stripe' ),
					'disabled' => esc_html_x( 'Disabled', 'setting label', 'stripe' ),
				),
				'value'       => simpay_get_setting( 'test_mode', 'enabled' ),
				'description' => wpautop(
					sprintf(
						/* translators: %1$s Opening anchor tag to Stripe Dashboard, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'While in Test Mode no live payments are processed. Make sure Test mode is enabled in your %1$sStripe dashboard%2$s to view your test transactions.', 'stripe' ),
						'<a href="https://dashboard.stripe.com" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
						Utils\get_external_link_markup() . '</a>'
					) .
					sprintf(
						'<div id="simpay-test-mode-toggle-notice" class="hidden" style="margin-top: 15px;">%s</div>',
						$toggle_notice
					)
				),
				'priority'    => 40,
			)
		)
	);

	// Country.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'account_country',
				'section'     => 'stripe',
				'subsection'  => 'account',
				'label'       => esc_html_x( 'Country', 'setting label', 'stripe' ),
				'options'     => i18n\get_stripe_countries(),
				'value'       => simpay_get_setting( 'account_country', 'US' ),
				'description' => wpautop(
					esc_html__(
						'The country associated with the connected Stripe account.',
						'stripe'
					)
				),
				'priority'    => 60,
			)
		)
	);
}

/**
 * Registers settings for Stripe/Locale subsection.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_locale_settings( $settings ) {
	// Stripe Checkout locale.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'stripe_checkout_locale',
				'section'     => 'stripe',
				'subsection'  => 'locale',
				'label'       => esc_html_x( 'Stripe Checkout', 'setting label', 'stripe' ),
				'options'     => i18n\get_stripe_checkout_locales(),
				'value'       => simpay_get_setting( 'stripe_checkout_locale', '' ),
				'description' => wpautop(
					esc_html__(
						'Specify "Auto-detect" to display Stripe Checkout in the user\'s preferred language, if available.',
						'stripe'
					)
				),
			)
		)
	);
}
