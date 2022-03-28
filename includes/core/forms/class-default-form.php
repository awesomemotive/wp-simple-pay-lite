<?php
/**
 * Form: Default
 *
 * @package SimplePay\Core\Forms
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Forms;

use SimplePay\Core\Abstracts\Form;
use SimplePay\Core\Forms\Fields;
use SimplePay\Core\Assets;
use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default_Form class.
 *
 * @since 3.0.0
 */
class Default_Form extends Form {

	/**
	 * Total amount.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	public $total_amount = '';

	/**
	 * Default_Form constructor.
	 *
	 * @param int $id Payment Form ID.
	 */
	public function __construct( $id ) {

		// Construct our base form from the parent class.
		parent::__construct( $id );

		if ( null === $this->post ) {
			return;
		}

		// Shim a few properties that are referenced later without checking existence.
		// @todo Update implementation of these properties to check validitiy.
		$this->is_one_time_custom_amount = false;
	}

	/**
	 * Add hooks and filters for this form instance.
	 *
	 * Hooks get run once per form instance.
	 *
	 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/617
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		add_action( 'wp_footer', array( $this, 'set_script_variables' ), 0 );
		add_filter( 'simpay_form_' . $this->id . '_custom_fields', array( $this, 'get_custom_fields_html' ), 10, 2 );
	}

	/**
	 * Set the JS script variables specifically for this form
	 *
	 * @since 3.0.0
	 */
	public function set_script_variables() {

		$temp[ $this->id ] = array(
			'id'     => $this->id,
			'type'   => 'stripe_checkout' === $this->get_display_type()
				? 'stripe-checkout'
				: 'stripe-elements',
			'form'   => $this->get_form_script_variables(),
			'stripe' => array_merge(
				array(
					'amount'  => $this->total_amount,
					'country' => $this->country,
				),
				$this->get_stripe_script_variables()
			),
		);

		$temp = apply_filters( 'simpay_form_' . absint( $this->id ) . '_script_variables', $temp, $this->id );

		/**
		 * Filters Payment Form's script variables.
		 *
		 * @since 3.9.0
		 *
		 * @param \SimplePay\Core\Abstracts\Form[] $forms List of Payment Forms and associated script variables.
		 * @param \SimplePay\Core\Abstracts\Form   $this  Current Payment Form.
		 */
		$temp = apply_filters( 'simpay_form_script_variables', $temp, $this );

		// Add this temp script variables to our assets so if multiple forms are on the page they will all be loaded at once and be specific to each form.
		Assets::get_instance()->script_variables( $temp );
	}

	/**
	 * Output for the form.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		$id                = 'simpay-form-' . $this->id;
		$form_display_type = simpay_get_saved_meta( $this->id, '_form_display_type', 'stripe_checkout' );

		do_action( 'simpay_before_form_display', $this );

		$classes = array(
			'simpay-form-wrap',
			"simpay-{$form_display_type}-form-wrap",
		);

		printf(
			'<div id="simpay-%1$s-form-wrap-%2$s" data-id="simpay-form-%2$s-wrap" class="%3$s">',
			$form_display_type,
			$this->id,
			esc_attr( implode( ' ', $classes ) )
		);

			do_action( 'simpay_form_' . absint( $this->id ) . '_before_payment_form', $this );

			// Can add additional form tag attributes here using a filter.
			$more_form_atts = apply_filters( 'simpay_more_form_attributes', '' );

			echo '<form action="" method="post" class="' . $this->get_form_classes( $this->id ) . '" id="' . esc_attr( $id ) . '" data-simpay-form-id="' . esc_attr( $this->id ) . '" ' . esc_attr( $more_form_atts ) . '>';

				do_action( 'simpay_form_' . absint( $this->id ) . '_before_form_top', $this );

				/**
				 * Allow additional output at the top of all forms.
				 *
				 * @since 3.5.0
				 *
				 * @param int    $form_id Current form ID.
				 * @param object $this Current form object.
				 */
				do_action( 'simpay_form_before_form_top', $this->id, $this );

				if ( ! empty( $this->custom_fields ) && is_array( $this->custom_fields ) ) {
					echo $this->print_custom_fields();
				}

				// TODO Append these hidden inputs to form in public.js?
				echo '<input type="hidden" name="simpay_form_id" value="' . esc_attr( $this->id ) . '" />';
				echo '<input type="hidden" name="simpay_amount" value="" class="simpay-amount" />';

				// Form validation error message container.
				echo '<div class="simpay-errors" id="' . esc_attr( $id ) . '-error" aria-live="assertive" aria-relevant="additions text" aria-atomic="true"></div>';

				if ( true === $this->test_mode ) {
					echo simpay_get_test_mode_badge();
				}

				do_action( 'simpay_form_' . absint( $this->id ) . '_before_form_bottom', $this );

				/**
				 * Allow additional output at the bottom of all forms.
				 *
				 * @since 3.5.0
				 *
				 * @param int    $form_id Current form ID.
				 * @param object $this Current form object.
				 */
				do_action( 'simpay_form_before_form_bottom', $this->id, $this );

				wp_nonce_field( 'simpay_payment_form' );

			echo '</form>';

			do_action( 'simpay_form_' . absint( $this->id ) . '_after_form_display', $this );

		echo '</div>';

		do_action( 'simpay_after_form_display', $this );
	}

	/**
	 * Returns the form class.
	 *
	 * @since 3.0.0
	 *
	 * @param int $id Payment Form ID.
	 * @return string
	 */
	private function get_form_classes( $id ) {
		$classes = array(
			'simpay-checkout-form',
			'simpay-form-' . absint( $this->id ),
		);

		if ( 'disabled' !== simpay_get_setting( 'default_plugin_styles', 'enabled' ) ) {
			$classes[] = 'simpay-styled';
		}

		/**
		 * Filters the classlist applied to the payment form.
		 *
		 * @since 3.0.0
		 *
		 * @param array $classes List of CSS class names.
		 */
		$classes = apply_filters( 'simpay_form_' . absint( $this->id ) . '_classes', $classes );

		return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

	}

	/**
	 * Print out the custom fields.
	 *
	 * @return string
	 */
	public function print_custom_fields() {

		$html = '';

		if ( ! empty( $this->custom_fields ) && is_array( $this->custom_fields ) ) {

			foreach ( $this->custom_fields as $k => $v ) {
				$html = apply_filters( 'simpay_custom_field_html', $html, $v );
				$html = apply_filters( 'simpay_custom_fields', $html, $v );
			}
		}

		$html = apply_filters( 'simpay_form_' . absint( $this->id ) . '_custom_fields', $html, $this );
		$html = apply_filters( 'simpay_form_custom_fields', $html, $this );

		return $html;
	}

	/**
	 * Output hidden fields to capture address information if enabled.
	 *
	 * Stripe doesn't map the collected billing information to the Customer object,
	 * which we want, so we need to track it here.
	 *
	 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/506
	 *
	 * @since unknown
	 */
	public function output_address_fields() {
		$form_display_type = simpay_get_saved_meta( $this->id, '_form_display_type', 'stripe_checkout' );

		if ( 'stripe_checkout' !== $form_display_type ) {
			return;
		}

		$html = '';

		if ( $this->enable_billing_address ) {
			$html .= '<input type="hidden" name="simpay_billing_customer_name" class="simpay-customer-name" />';
			$html .= '<input type="hidden" name="simpay_billing_address_country" class="simpay-billing-country" />';
			$html .= '<input type="hidden" name="simpay_billing_address_postal_code" class="simpay-billing-zip" />';
			$html .= '<input type="hidden" name="simpay_billing_address_state" class="simpay-billing-state" />';
			$html .= '<input type="hidden" name="simpay_billing_address_line1" class="simpay-billing-address-line1" />';
			$html .= '<input type="hidden" name="simpay_billing_address_city" class="simpay-billing-city" />';
		}

		if ( $this->enable_shipping_address ) {
			$html .= '<input type="hidden" name="simpay_shipping_customer_name" class="simpay-customer-name" />';
			$html .= '<input type="hidden" name="simpay_shipping_address_country" class="simpay-shipping-country" />';
			$html .= '<input type="hidden" name="simpay_shipping_address_postal_code" class="simpay-shipping-zip" />';
			$html .= '<input type="hidden" name="simpay_shipping_address_state" class="simpay-shipping-state" />';
			$html .= '<input type="hidden" name="simpay_shipping_address_line1" class="simpay-shipping-address-line1" />';
			$html .= '<input type="hidden" name="simpay_shipping_address_city" class="simpay-shipping-city" />';
		}

		echo $html;
	}

	/**
	 * Place to set our script variables for this form.
	 *
	 * @return array
	 */
	public function get_form_script_variables() {
		$prices = simpay_get_payment_form_prices( $this );
		$prices = array_map(
			function( $price ) {
				return $price->to_array();
			},
			$prices
		);

		$custom_fields = simpay_get_saved_meta( $this->id, '_custom_fields' );

		$payment_text         = __( 'Pay with Card', 'stripe' );
		$payment_trial_text   = __( 'Start Trial', 'stripe' );
		$payment_loading_text = __( 'Please Wait...', 'stripe' );

		// Payment Button (Embed + Stripe Checkout).
		if ( isset( $custom_fields['payment_button'] ) && is_array( $custom_fields['payment_button'] ) ) {
			// There can only be one Checkout Button, but it's saved in an array.
			$payment_button = current( $custom_fields['payment_button'] );

			// Base.
			if ( ! empty( $payment_button['text'] ) ) {
				$payment_text = $payment_button['text'];
			}

			// Trial.
			if ( ! empty( $payment_button['trial_text'] ) ) {
				$payment_trial_text = $payment_button['trial_text'];
			}

			// Processing.
			if ( ! empty( $payment_button['processing_text'] ) ) {
				$payment_loading_text = $payment_button['processing_text'];
			}
		}

		$integers['integers'] = array(
			'amount' => $this->amount,
		);

		$strings['strings'] = array(
			'paymentButtonText'        => esc_html( $payment_text ),
			'paymentButtonTrialText'   => esc_html( $payment_trial_text ),
			'paymentButtonLoadingText' => esc_html( $payment_loading_text ),
		);

		// @since 3.9.0 start with a less complex configuration object.
		$config = array(
			'livemode' => $this->is_livemode(),
			'prices'   => $prices,
			'i18n'     => array(
				'stripeErrorMessages' => i18n\get_localized_error_messages(),
				'unknownError'        => __(
					'Unable to complete request. Please try again.',
					'stripe'
				),
			),
		);

		$form_variables = array_merge( $integers, $strings, $config );

		return $form_variables;
	}

	/**
	 * Default custom fields handler.
	 *
	 * @since 3.4.0
	 *
	 * @param string $html Form HTML.
	 * @param object $form The current form.
	 * @return string $html Form HTML.
	 */
	public function get_custom_fields_html( $html, $form ) {
		foreach ( $this->custom_fields as $key => $value ) {
			switch ( $value['type'] ) {
				case 'payment_button':
					$html .= Fields\Payment_Button::html(
						$value,
						'payment-button',
						$form
					);
					break;
			}
		}

		return $html;
	}

	/**
	 * Check if this form has subscriptions enabled or not.
	 *
	 * @since unknown
	 *
	 * @return bool
	 */
	public function is_subscription() {
		return false;
	}
}
