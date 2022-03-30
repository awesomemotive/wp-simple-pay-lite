<?php
/**
 * Elementor: Payement form control
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration\Elementor;

use Elementor;

/**
 * PaymentFormControl class.
 *
 * @since 4.4.3
 */
class PaymentFormControl {

	/**
	 * Adds the "WP Simple Pay" control to the widget.
	 *
	 * @since 4.4.3
	 *
	 * @param \Elementor\Widget_Base $widget Widget.
	 * @return void
	 */
	public function add_control( $widget ) { // @phpstan-ignore-line
		$widget->start_controls_section( // @phpstan-ignore-line
			sprintf( 'simpay-%s', $widget->get_name() ), // @phpstan-ignore-line
			array(
				'label' => esc_html__( 'WP Simple Pay', 'stripe' ),
				'tab'   => Elementor\Controls_Manager::TAB_CONTENT, // @phpstan-ignore-line
			)
		);

		$widget->add_control( // @phpstan-ignore-line
			'simpay_payment_form',
			array(
				'label'       => __( 'Payment Form', 'stripe' ),
				'type'        => Elementor\Controls_Manager::SELECT, // @phpstan-ignore-line
				'separator'   => 'after',
				'label_block' => true,
				'default'     => '0',
				'options'     => $this->get_payment_form_options(),
			)
		);

		$widget->end_controls_section(); // @phpstan-ignore-line
	}

	/**
	 * Outputs the widget content with additional launching code if required.
	 *
	 * @since 4.4.3
	 *
	 * @param string                 $content Widget content.
	 * @param \Elementor\Widget_Base $widget Widget.
	 * @return string
	 */
	public function render_widget( $content, $widget ) { // @phpstan-ignore-line
		$settings = $widget->get_settings(); // @phpstan-ignore-line

		if (
			! isset( $settings['simpay_payment_form'] ) ||
			'0' === $settings['simpay_payment_form'] ||
			empty( $settings['simpay_payment_form'] )
		) {
			return $content;
		}

		// Output shortcode.
		$content .= do_shortcode(
			sprintf(
				'[simpay id="%s"]',
				$settings['simpay_payment_form']
			)
		);

		// Hide shortcode with CSS.
		$content .= sprintf(
			'<style>%s .simpay-form-wrap { display: none; }</style>',
			$widget->get_unique_selector() // @phpstan-ignore-line
		);

		// Add the JavaScript.
		wp_enqueue_script( 'jquery' );

		$content .= sprintf(
			'<script>( function( $ ) { $( \'%1$s .elementor-button\' ).click( function( e ) { e.preventDefault(); $( \'%1$s .simpay-payment-btn\' ).click(); $( \'%1$s #simpay-modal-control-%2$d\' ).click(); } ); } )( jQuery );</script>',
			$widget->get_unique_selector(), // @phpstan-ignore-line
			(int) $settings['simpay_payment_form']
		);

		return $content;
	}

	/**
	 * Returns the available payment forms as options for the widget control.
	 *
	 * @since 4.4.3
	 *
	 * @return array<string, string>
	 */
	private function get_payment_form_options() {
		static $options = array();

		if ( empty( $options ) ) {
			$forms = get_posts(
				array(
					'post_type'      => 'simple-pay',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			$options = array(
				'0' => __( 'Select a form&hellip;', 'stripe' ),
			);

			foreach ( $forms as $form_id ) {
				if ( false === $this->is_form_type_valid( $form_id ) ) {
					continue;
				}

				$options[ $form_id ] = get_the_title( $form_id );
			};
		}

		return $options;
	}

	/**
	 * Determines if a payment form can be used with the widget.
	 *
	 * Only accepts Stripe Checkout with no custom fields, or Overlay.
	 *
	 * @since 4.4.3
	 *
	 * @param int $form_id Payment form ID.
	 * @return bool
	 */
	private function is_form_type_valid( $form_id ) {
		// Only accept Stripe Checkout or Overlay.
		$type = simpay_get_saved_meta(
			$form_id,
			'_form_display_type',
			'stripe_checkout'
		);

		switch ( $type ) {
			case 'embedded':
				return false;
			case 'overlay':
				return true;
			default:
				/** @var array<string, array<string, array<string>>> $custom_fields */
				$custom_fields = simpay_get_saved_meta(
					$form_id,
					'_custom_fields',
					array() // @phpstan-ignore-line
				);

				$_custom_fields = array();

				foreach ( $custom_fields as $type => $fields ) {
					/** @var array<string, array<string>> $fields */
					foreach ( $fields as $k => $field ) {
						/** @var array<string> $field */
						$field['type']    = $type;
						$_custom_fields[] = $field;
					}
				}

				return count( $_custom_fields ) <= 2;
		}
	}

}
