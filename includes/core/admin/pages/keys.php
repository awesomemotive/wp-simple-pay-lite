<?php

namespace SimplePay\Core\Admin\Pages;

use SimplePay\Core\Abstracts\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feeds settings.
 *
 * Handles form settings and outputs the settings page markup.
 *
 * @since 3.0.0
 */
class Keys extends Admin_Page {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->id           = 'keys';
		$this->option_group = 'settings';
		$this->label        = esc_html__( 'Stripe Setup', 'stripe' );
		$this->link_text    = esc_html__( 'Help docs for Stripe Keys Settings', 'stripe' );
		$this->link_slug    = ''; // TODO: Fill in slug, not in use currently (issue #301)
		$this->ga_content   = 'general-settings';

		$this->sections = $this->add_sections();
		$this->fields   = $this->add_fields();
	}

	/**
	 * Add sections.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function add_sections() {

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_sections', array(
			'connect'      => array(
				'title' => '',
			),
			'mode'      => array(
				'title' => '',
			),
			'test_keys' => array(
				'title' => '',
			),
			'live_keys' => array(
				'title' => '',
			),
			'country' => array(
				'title' => '',
			),
		) );
	}

	/**
	 * Add fields.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function add_fields() {

		$fields       = array();
		$this->values = get_option( 'simpay_' . $this->option_group . '_' . $this->id );

		if ( ! empty( $this->sections ) && is_array( $this->sections ) ) {
			foreach ( $this->sections as $section => $a ) {

				$section = sanitize_key( $section );

				if ( 'connect' == $section ) {
					$show_connect_button = false;

					$mode = simpay_is_test_mode() ? __( 'test', 'stripe' ) : __( 'live', 'stripe' );

					if( simpay_is_test_mode() && ! simpay_check_keys_exist() ) {

						$show_connect_button = true;

					} elseif( ! simpay_check_keys_exist() ) {

						$show_connect_button = true;

					}

					if( $show_connect_button ) {
						$html = '<a href="'. esc_url( simpay_get_stripe_connect_url() ) .'" class="wpsp-stripe-connect"><span>' . __( 'Connect with Stripe', 'stripe' ) . '</span></a>';
					} else {
						$html = '<p>' . sprintf(
							/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for reconnecting to Stripe, do not translate. %3$s Opening anchor tag for disconnecting Stripe, do not translate. %4$s Closing anchor tag, do not translate. */
							__( 'Your Stripe account is connected in %1$s mode. %2$sReconnect in %1$s mode%4$s, or %3$sdisconnect this account%4$s.', 'stripe' ),
							'<strong>' . $mode . '</strong>',
							'<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '">',
							'<a href="' . esc_url( simpay_get_stripe_disconnect_url() ) . '">',
							'</a>'
						) . '</p>';
					}

					$html .= '<p class="simpay-stripe-connect-help description">';
					$html .= '<span class="dashicons dashicons-editor-help"></span><span>';
					$html .= sprintf(
						/* translators: %1$s Opening anchor tag for Stripe Connect documentation, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'Have questions about connecting with Stripe? See the %1$sdocumentation%2$s.', 'stripe' ),
						'<a href="' . simpay_get_url( 'docs' ) . 'articles/stripe-setup/" target="_blank" rel="noopener noreferrer">',
						'</a>'
					);
					$html .= '</span></p>';

					if ( simpay_can_site_manage_stripe_keys() ) {
						$html .= '<p id="wpsp-api-keys-row-reveal"><button type="button" class="button button-small">' . __( 'Manage API Keys Manually', 'stripe' ) . '</button></p>';
						$html .= '<p id="wpsp-api-keys-row-hide"><button type="button" class="button button-small">' . __( 'Hide API Keys', 'stripe' ) . '</button></p>';
					}

					$fields[ $section ] = array(
						'test_mode' => array(
							'title'       => esc_html__( 'Connection Status', 'stripe' ),
							'type'        => 'custom-html',
							'html'        => $html,
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][test_mode]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-test-mode',
						),
					);
				} elseif  ( 'mode' == $section ) {
					$dashboard_message = sprintf(
						/* translators: %1$s Opening anchor tag to Stripe Dashboard, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'While in test mode no live payments are processed. Make sure Test mode is enabled in your %1$sStripe dashboard%2$s to view your test transactions.', 'stripe' ),
						'<a href="https://dashboard.stripe.com" target="_blank">',
						'</a>'
					);

					$toggle_notice = sprintf(
						'<div id="simpay-test-mode-toggle-notice" style="display: none;"><p>%1$s</p><p>%2$s</p></div>',
						esc_html__( 'You just toggled payment modes. You may be required to reconnect to Stripe when your settings are saved.', 'stripe' ),
						sprintf(
							/* translators: %1$s Stripe account mode. %2$s Link to Stripe dashboard. */
							__( 'Please also ensure you have the correct subscription, coupon, and webhook settings in your %1$s %2$s.', 'stripe' ),
							'<span id="simpay-toggle-notice-status" data-live="' . esc_attr( _x( 'live', 'Stripe account status', 'stripe' ) ) .'" data-test="' . esc_attr( _x( 'test', 'Stripe account status', 'stripe' ) ) . '"></span>',
							'<a id="simpay-toggle-notice-status-link" data-live="https://dashboard.stripe.com/live/dashboard" data-test="https://dashboard.stripe.com/test/dashboard" target="_blank">' . __( 'Stripe account', 'stripe' ) . '</a>'
						)
					);

					$fields[ $section ] = array(
						'test_mode' => array(
							'title'       => esc_html__( 'Test Mode', 'stripe' ),
							'default'     => 'enabled',
							'type'        => 'radio',
							'options'     => array(
								'enabled'  => esc_html__( 'Enabled', 'stripe' ),
								'disabled' => esc_html__( 'Disabled', 'stripe' ),
							),
							'value'       => $this->get_option_value( $section, 'test_mode' ),
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][test_mode]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-test-mode',
							'inline'      => 'inline',
							'description' => $dashboard_message,
						),
						'test_mode_toggle' => array(
							'title' => '',
							'id'    => 'simpay-test-mode-toggle',
							'type'  => 'custom-html',
							'html'  => $toggle_notice,
						),
					);
				} elseif ( 'test_keys' == $section ) {

					$fields[ $section ] = array(
						'publishable_key' => array(
							'title'   => esc_html__( 'Test Publishable Key', 'stripe' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][publishable_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-publishable-key',
							'value'   => trim( $this->get_option_value( $section, 'publishable_key' ) ),
							'class'   => array(
								'regular-text',
							),
							'description' => esc_html__( 'Starts with', 'stripe' ) . ' <code>pk_test</code>',
						),
						'secret_key'      => array(
							'title'   => esc_html__( 'Test Secret Key', 'stripe' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][secret_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-secret-key',
							'value'   => trim( $this->get_option_value( $section, 'secret_key' ) ),
							'class'   => array(
								'regular-text',
							),
							'description' => esc_html__( 'Starts with', 'stripe' ) . ' <code>sk_test</code>',
						),
					);
				} elseif ( 'live_keys' == $section ) {

					$fields[ $section ] = array(
						'publishable_key' => array(
							'title'   => esc_html__( 'Live Publishable Key', 'stripe' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][publishable_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-publishable-key',
							'value'   => trim( $this->get_option_value( $section, 'publishable_key' ) ),
							'class'   => array(
								'regular-text',
							),
							'description' => esc_html__( 'Starts with', 'stripe' ) . ' <code>pk_live</code>',
						),
						'secret_key'      => array(
							'title'   => esc_html__( 'Live Secret Key', 'stripe' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][secret_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-secret-key',
							'value'   => trim( $this->get_option_value( $section, 'secret_key' ) ),
							'class'   => array(
								'regular-text',
							),
							'description' => esc_html__( 'Starts with', 'stripe' ) . ' <code>sk_live</code>',
						),
					);
				} elseif ( 'country' == $section ) {

					$fields[ $section ] = array(
						'country'       => array(
							'title'       => esc_html__( 'Account Country', 'stripe' ),
							'type'        => 'select',
							'options'     => simpay_get_country_list(),
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][country]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-country',
							'value'       => $this->get_option_value( $section, 'country' ),
							'description' => esc_html__( 'The country associated with the connected Stripe account.', 'stripe' ) . '<br />' . '<a href="https://dashboard.stripe.com/account" target="_blank">' . esc_html__( 'View your Stripe account settings', 'stripe' ),
						),
					);

				}

			}
		}

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_fields', $fields );
	}

}
