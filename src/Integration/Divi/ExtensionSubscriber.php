<?php
/**
 * Divi: Extension subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration\Divi;

use SimplePay\Core\EventManagement\EventManager;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * ExtensionSubscriber class.
 *
 * @since 4.4.3
 */
class ExtensionSubscriber implements SubscriberInterface {

	/**
	 * Event manager.
	 *
	 * @var \SimplePay\Core\EventManagement\EventManager
	 * @since 4.4.3
	 */
	private $events;

	/**
	 * ExtensionSubscriber.
	 *
	 * @since 4.4.3
	 *
	 * @param \SimplePay\Core\EventManagement\EventManager $events Event manager.
	 */
	public function __construct( EventManager $events ) {
		$this->events = $events;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'et_builder_framework_loaded'       => 'builder_loaded',
			'et_module_shortcode_output'        => array( 'render', 10, 3 ),
			'et_pb_module_shortcode_attributes' => array(
				'et_pb_cta_force_button', 10, 3
			),
		);
	}

	/**
	 * Attaches additional events when the builder is loaded.
	 *
	 * @since 4.4.3
	 *
	 * @return void
	 */
	public function builder_loaded() {
		$this->events->add_callback(
			'et_pb_all_fields_unprocessed_et_pb_button',
			array( $this, 'register_button_field' )
		);

		$this->events->add_callback(
			'et_pb_all_fields_unprocessed_et_pb_cta',
			array( $this, 'register_cta_field' )
		);

		$this->events->add_callback(
			'et_pb_all_fields_unprocessed_et_pb_pricing_table',
			array( $this, 'register_pricing_table_field' )
		);
	}

	/**
	 * Registers the field for the "Button" module.
	 *
	 * @since 4.4.3
	 *
	 * @param array<mixed> $fields Module fields.
	 * @return array<mixed> $fields
	 */
	function register_button_field( $fields ) {
		$fields['simpay_payment_form'] = array_merge(
			$this->get_default_field_args(),
			array(
				'option_category' => 'basic_option',
				'toggle_slug'     => 'link',
			)
		);

		return $fields;
	}

	/**
	 * Registers the field for the "Call to Action" module.
	 *
	 * @since 4.4.3
	 *
	 * @param array<mixed> $fields Module fields.
	 * @return array<mixed> $fields
	 */
	function register_cta_field( $fields ) {
		$fields['simpay_payment_form'] = array_merge(
			$this->get_default_field_args(),
			array(
				'option_category' => 'basic_option',
				'toggle_slug'     => 'link_options',
			)
		);

		return $fields;
	}

	/**
	 * Registers the field for the "Pricing table" module.
	 *
	 * @since 4.4.3
	 *
	 * @param array<mixed> $fields Module fields.
	 * @return array<mixed> $fields
	 */
	function register_pricing_table_field( $fields ) {
		$fields['simpay_payment_form'] = array_merge(
			$this->get_default_field_args(),
			array(
				'option_category' => 'basic_option',
				'toggle_slug'     => 'link_options',
			)
		);

		return $fields;
	}

	/**
	 * Renders a module's content.
	 *
	 * @since 4.4.3
	 *
	 * @param string|array<mixed> $content Module content.
	 * @param string              $slug Module slug.
	 * @param \ET_Builder_Element $module Divi module.
	 * @return string|array<mixed>
	 */
	public function render( $content, $slug, $module ) { // @phpstan-ignore-line
		if ( ! in_array( $slug, $this->get_modules(), true ) ) {
			return $content;
		}

		if ( is_array( $content ) ) {
			return $content;
		}

		$settings = $module->props; // @phpstan-ignore-line

		if (
			! isset( $settings['simpay_payment_form'] ) ||
			'0' === $settings['simpay_payment_form'] ||
			empty( $settings['simpay_payment_form'] )
		) {
			return $content;
		}

		$output = '';

		// Output shortcode.
		$output .= do_shortcode(
			sprintf(
				'[simpay id="%s"]',
				$settings['simpay_payment_form']
			)
		);

		// Add the JavaScript.
		wp_enqueue_script( 'jquery' );

		// Render specific CSS/JS for each module.
		$render_func = array( $this, 'render_' . $slug );

		if ( is_callable( $render_func ) ) {
			$output .= call_user_func( $render_func, $module );
		}

		$content = substr( $content, 0, -6 );
		$content .= $output;
		$content .= '</div>';

		return $content;
	}

	/**
	 * Forces the "Call to Action" module to add a `button_url` property if a payment form is selected.
	 *
	 * This helps prompt the button to appear, which only shows if there is a URL.
	 *
	 * @since 4.4.3
	 *
	 * @param array<mixed> $props Module properties.
	 * @param array<mixed> $atts Module attributes.
	 * @param string $slug Module slug.
	 * @return array<mixed>
	 */
	function et_pb_cta_force_button( $props, $atts, $slug ) {
		if ( ! in_array( $slug, $this->get_modules(), true ) ) {
			return $props;
		}

		if (
			! isset( $props['simpay_payment_form'] ) ||
			'0' === $props['simpay_payment_form'] ||
			empty( $props['simpay_payment_form'] )
		) {
			return $props;
		}

		$props['button_url'] = '#form-' . $props['simpay_payment_form'];

		return $props;
	}

	/**
	 * Renders additional script/styles for the "Button" module to use the payment form setting.
	 *
	 * @since 4.4.3
	 *
	 * @param \ET_Builder_Element $module Divi module.
	 * @return string
	 */
	private function render_et_pb_button( $module ) { // @phpstan-ignore-line
		$settings = $module->props; // @phpstan-ignore-line

		return sprintf(
			'<script>( function() {
				var wrapper = document.querySelector( "%1$s" ).parentNode;
				var form = wrapper.querySelector( "[data-id=\'simpay-form-%2$s-wrap\']" );
				form.style.display = "none";

				document.querySelector( "%1$s" ).addEventListener( "click", function( event ) {
					event.preventDefault();
					var buttons = form.querySelectorAll( \'.simpay-payment-btn, #simpay-modal-control-%2$s\' );

					if ( buttons ) {
						buttons.forEach( function( button ) {
							button.click();
						} );
					}
				} );
			} )();</script>',
			$this->get_module_selector( $module ),
			(int) $settings['simpay_payment_form']
		);
	}

	/**
	 * Renders additional script/styles for the "Call to Action" module to use the payment form setting.
	 *
	 * @since 4.4.3
	 *
	 * @param \ET_Builder_Element $module Divi module.
	 * @return string
	 */
	private function render_et_pb_cta( $module ) { // @phpstan-ignore-line
		$settings = $module->props; // @phpstan-ignore-line

		return sprintf(
			'<script>( function() {
				var form = document.querySelector( "%1$s [data-id=\'simpay-form-%2$s-wrap\']" );
				form.style.display = "none";

				document.querySelector( "%1$s .et_pb_button" ).addEventListener( "click", function( event ) {
					event.preventDefault();
					var buttons = form.querySelectorAll( \'.simpay-payment-btn, #simpay-modal-control-%2$s\' );

					if ( buttons ) {
						buttons.forEach( function( button ) {
							button.click();
						} );
					}
				} );
			} )();</script>',
			$this->get_module_selector( $module ),
			(int) $settings['simpay_payment_form']
		);
	}

	/**
	 * Renders additional script/styles for the "Pricing Table" module to use the payment form setting.
	 *
	 * @since 4.4.3
	 *
	 * @param \ET_Builder_Element $module Divi module.
	 * @return string
	 */
	private function render_et_pb_pricing_table( $module ) { // @phpstan-ignore-line
		$settings = $module->props; // @phpstan-ignore-line

		return sprintf(
			'<script>( function() {
				var wrapper = document.querySelector( "%1$s" ).parentNode;
				var form = wrapper.querySelector( "[data-id=\'simpay-form-%2$s-wrap\']" );
				form.style.display = "none";

				document.querySelector( "%1$s .et_pb_button" ).addEventListener( "click", function( event ) {
					event.preventDefault();
					var buttons = form.querySelectorAll( \'.simpay-payment-btn, #simpay-modal-control-%2$s\' );

					if ( buttons ) {
						buttons.forEach( function( button ) {
							button.click();
						} );
					}
				} );
			} )();</script>',
			$this->get_module_selector( $module ),
			(int) $settings['simpay_payment_form']
		);
	}

	/**
	 * Returns shared arguments for module field settings.
	 *
	 * @since 4.4.3
	 *
	 * @return array<mixed>
	 */
	private function get_default_field_args() {
		return array(
			'label'           => esc_html__( 'WP Simple Pay', 'stripe' ),
			'type'            => 'select',
			'options'         => $this->get_payment_form_options(),
			'description'     => esc_html__(
				'WP Simple Pay form ID.',
				'stripe'
			),
		);
	}

	/**
	 * Returns a modules selector (class name).
	 *
	 * @since 4.4.3
	 *
	 * @param \ET_Builder_Element $module Divi module.
	 */
	private function get_module_selector( $module ) { // @phpstan-ignore-line
		return '.' . implode(
			'.',
			array_filter(
				array_map(
					'trim',
					explode( ' ', $module->module_classname( $module->slug ) ) // @phpstan-ignore-line
				)
			)
		);
	}

	/**
	 * Returns a list of modules that support WP Simple Pay payment forms.
	 *
	 * @since 4.4.3
	 *
	 * @return array<string>
	 */
	private function get_modules() {
		return array(
			'et_pb_button',
			'et_pb_cta',
			'et_pb_pricing_table',
		);
	}

	/**
	 * Returns the available payment forms as options for the widget control.
	 *
	 * @since 4.4.3
	 *
	 * @return array<string, string>
	 */
	public function get_payment_form_options() {
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
