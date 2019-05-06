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
class Display extends Admin_Page {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->id           = 'display';
		$this->option_group = 'settings';
		$this->label        = esc_html__( 'Payment Confirmation', 'stripe' );
		$this->link_text    = esc_html__( 'Help docs for Payment Confirmation Settings', 'stripe' );
		$this->link_slug    = '';
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
			'payment_confirmation_messages' => array(
				'title' => esc_html__( 'Payment Confirmation Messages', 'stripe' ),
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

				if ( 'payment_confirmation_messages' == $section ) {

					// Default template for one time details
					$one_time_details_template = simpay_get_editor_default( 'one_time' );
					$one_time_details_value    = $this->get_option_value( $section, 'one_time_payment_details' );

					$custom_html = '<div>';
					$custom_html .= __( 'Configure your payment confirmation <em>page</em> below.', 'stripe' );
					$custom_html .= ' <a href="' . simpay_docs_link( '', 'email-receipts-stripe', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'See how to configure email receipts in Stripe.', 'stripe' ) . '</a></p>';
					$custom_html .= '</div>';

					$fields[ $section ] = array(
						'note_html'                => array(
							'type' => 'custom-html',
							'html' => $custom_html,
							'name' => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][note_html]',
							'id'   => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-note-html',
						),
						'one_time_payment_details' => array(
							'title'       => esc_html__( 'One-Time Payment', 'stripe' ),
							'type'        => 'editor',
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][one_time_payment_details]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-one-time-payment-details',
							'value'       => isset( $one_time_details_value ) && ! empty( $one_time_details_value ) ? $one_time_details_value : $one_time_details_template,
							'escaping'    => array( $this, 'escape_editor' ),
							'description' => $this->one_time_payment_details_description(),
						),
					);
				}
			}
		}

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_fields', $fields );
	}

	/**
	 * Special function to escape the wp_editor how we want
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function escape_editor( $value ) {
		return wp_kses_post( $value );
	}

	/**
	 * Default One-time payment details template
	 *
	 * @return string
	 */
	public function one_time_payment_details_description() {

		$html = '<div class="simpay-payment-details-description">';
		$html .= '<p class="description">' . esc_html__( 'Enter what your customers will see after a successful one-time payment.', 'stripe' ) . '</p>';
		$html .= '<p><strong>' . esc_html__( 'Available template tags:', 'stripe' ) . '</strong></p>';
		$html .= '<p><code>{item-description}</code> - ' . esc_html__( "The form's Item Description value.", 'stripe' ) . '</p>';
		$html .= '<p><code>{company-name}</code> - ' . esc_html__( "The form's Company Name value.", 'stripe' ) . '</p>';
		$html .= '<p><code>{total-amount}</code> - ' . esc_html__( 'The total price of the payment.', 'stripe' ) . '</p>';
		$html .= '<p><code>{charge-date}</code> - ' . esc_html__( 'The charge date returned from Stripe.', 'stripe' ) . '</p>';
		$html .= '<p><code>{charge-id}</code> - ' . esc_html__( 'The unique charge ID returned from Stripe.', 'stripe' ) . '</p>';
		$html .= '</div>';

		return apply_filters( 'simpay_payment_details_tag_descriptions', $html );
	}
}
