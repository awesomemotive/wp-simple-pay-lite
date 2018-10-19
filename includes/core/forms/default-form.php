<?php

namespace SimplePay\Core\Forms;

use SimplePay\Core\Abstracts\Form;
use SimplePay\Core\Forms\Fields;
use SimplePay\Core\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Form.
 *
 * The default form view bundled with the plugin.
 *
 * @since 3.0.0
 */
class Default_Form extends Form {

	public $total_amount = '';

	/**
	 * Default_Form constructor.
	 *
	 * @param $id int
	 */
	public function __construct( $id ) {

		// Construct our base form from the parent class
		parent::__construct( $id );

		add_action( 'wp_footer', array( $this, 'set_script_variables' ), 0 );
	}

	/**
	 * Set the JS script variables specifically for this form
	 */
	public function set_script_variables() {

		$temp[ $this->id ] = array(
			'form'   => $this->get_form_script_variables(),
			'stripe' => array_merge( array(
				'amount' => $this->total_amount,
			), $this->get_stripe_script_variables() ),
		);

		$temp = apply_filters( 'simpay_form_' . absint( $this->id ) . '_script_variables', $temp, $this->id );

		// Add this temp script variables to our assets so if multiple forms are on the page they will all be loaded at once and be specific to each form
		Assets::get_instance()->script_variables( $temp );
	}

	/**
	 * Output for the form
	 */
	public function html() {

		$html = '';
		$id   = 'simpay-form-' . $this->id;

		// Can add additional form tag attributes here using a filter.
		$more_form_atts = apply_filters( 'simpay_more_form_attributes', '' );

		$html .= '<form action="" method="post" class="simpay-checkout-form ' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" data-simpay-form-id="' . esc_attr( $this->id ) . '" ' . esc_attr( $more_form_atts ) . '>';

		if ( ! empty( $this->custom_fields ) && is_array( $this->custom_fields ) ) {
			$html .= $this->print_custom_fields();
		}

		$html .= '<input type="hidden" name="simpay_stripe_token" value="" class="simpay-stripe-token" />';
		$html .= '<input type="hidden" name="simpay_stripe_email" value="" class="simpay-stripe-email" />';
		$html .= '<input type="hidden" class="simpay_form_id" name="simpay_form_id" value="' . esc_attr( $this->id ) . '" />';

		$html .= '<input type="hidden" name="simpay_amount" value="" class="simpay-amount" />';

		if ( $this->enable_shipping_address ) {
			$html .= $this->shipping_fields();
		}

		do_action( 'simpay_before_form_display' );

		echo $html;

		do_action( 'simpay_before_form_close' );

		// We echo the </form> instead of appending it so that the action hook can work correctly if they try to output something before the form close.
		echo  '</form>';

		do_action( 'simpay_after_form_display' );
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

				switch ( $v['type'] ) {
					case 'payment_button':
						$html .= Fields\Payment_Button::html( $v );
						break;
					case has_filter( 'simpay_custom_fields' ):
						$html .= apply_filters( 'simpay_custom_fields', $html, $v );
						break;
				}
			}
		}

		return $html;
	}

	/**
	 * Output hidden fields to capture shipping information if enabled
	 *
	 * @return string
	 */
	public function shipping_fields() {

		$html = '';

		$html .= '<input type="hidden" name="simpay_shipping_name" class="simpay-shipping-name" />';
		$html .= '<input type="hidden" name="simpay_shipping_country" class="simpay-shipping-country" />';
		$html .= '<input type="hidden" name="simpay_shipping_zip" class="simpay-shipping-zip" />';
		$html .= '<input type="hidden" name="simpay_shipping_state" class="simpay-shipping-state" />';
		$html .= '<input type="hidden" name="simpay_shipping_address_line1" class="simpay-shipping-address-line1" />';
		$html .= '<input type="hidden" name="simpay_shipping_city" class="simpay-shipping-city" />';

		return $html;
	}

	/**
	 * Place to set our script variables for this form.
	 *
	 * @return array
	 */
	public function get_form_script_variables() {

		$custom_fields = simpay_get_saved_meta( $this->id, '_custom_fields' );
		$loading_text  = '';

		if ( isset( $custom_fields['payment_button'] ) && is_array( $custom_fields['payment_button'] ) ) {

			foreach ( $custom_fields['payment_button'] as $k => $v ) {
				if ( is_array( $v ) && array_key_exists( 'processing_text', $v ) ) {
					if ( isset( $v['processing_text'] ) && ! empty( $v['processing_text'] ) ) {
						$loading_text = $v['processing_text'];
						break;
					}
				}
			}
		}

		if ( empty( $loading_text ) ) {
			$loading_text = esc_html__( 'Please wait...', 'stripe' );
		}

		$integers['integers'] = array(
			'amount'            => round( $this->amount ),
		);

		$strings['strings'] = array(
		    'loadingText' => $loading_text,
		);

		$form_variables = array_merge( $integers, $strings );

		return $form_variables;
	}
}
