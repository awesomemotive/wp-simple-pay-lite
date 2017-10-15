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
		$this->label        = esc_html__( 'Stripe Keys', 'stripe' );
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
			'mode'      => array(
				'title' => '',
			),
			'test_keys' => array(
				'title' => '',
			),
			'live_keys' => array(
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

				if ( 'mode' == $section ) {
					$fields[ $section ] = array(
						'test_mode' => array(
							'title'       => esc_html__( 'Test Mode', 'stripe' ),
							'default'     => 'enabled',
							'type'        => 'radio',
							'options'     => array(
								'enabled'  => esc_html__( 'Enabled', 'stripe' ),
								'disabled' => esc_html__( 'Disabled', 'stripe' ),
							),
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][test_mode]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-test-mode',
							'value'       => $this->get_option_value( $section, 'test_mode' ),
							'inline'      => 'inline',
							'description' => sprintf( wp_kses( __( 'While in test mode no live payments are processed. Make sure Test mode is enabled in your <a href="%1$s" target="_blank">Stripe dashboard</a> to view your test transactions.', 'stripe' ), array(
									'a' => array( 'href' => array(), 'target' => array() ),
								) ), esc_url( 'https://dashboard.stripe.com/' ) ) . '<br/><br/>' . sprintf( wp_kses( __( '<a href="%1$s" target="_blank">Retrieve your Stripe API test and live keys.</a>', 'stripe' ), array(
									'a' => array( 'href' => array(), 'target' => array() ),
								) ), esc_url( 'https://dashboard.stripe.com/account/apikeys' ) ),
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
				}

			}
		}

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_fields', $fields );
	}

}
