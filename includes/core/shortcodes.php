<?php

namespace SimplePay\Core;

use SimplePay\Core\Abstracts\Form;
use SimplePay\Core\Forms\Default_Form;
use SimplePay\Core\Payments\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Shortcodes Class
 *
 * Register and handle custom shortcodes.
 */
class Shortcodes {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Add shortcodes.
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes() {

		add_shortcode( 'simpay', array( $this, 'print_public_form' ) );
		add_shortcode( 'simpay_preview', array( $this, 'print_preview_form' ) );
		add_shortcode( 'simpay_payment_receipt', array( $this, 'print_payment_receipt' ) );
		add_shortcode( 'simpay_error', array( $this, 'print_errors' ) );

		do_action( 'simpay_add_shortcodes' );
	}

	/**
	 * Error message shortcode
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function print_errors( $attributes ) {

		$args = shortcode_atts( array(
			'show_to' => 'admin',
		), $attributes );

		$access_level = strtolower( $args['show_to'] );

		$show = false;
		$html = '';

		switch ( $access_level ) {
			case 'registered':
				if ( is_user_logged_in() ) {
					$show = true;
				}
				break;
			case 'all':
				$show = true;
				break;
			default:
				// Admin is the default access level
				if ( current_user_can( 'manage_options' ) ) {
					$show = true;
				}
				break;
		}

		if ( $show ) {

			$html = Errors::get_error_html();
		}

		Errors::clear_errors();

		return $html;
	}

	/**
	 * Shortcode to render public paymetn form
	 *
	 * @since  3.0.0
	 *
	 * @param  array $attributes
	 *
	 * @return string
	 */
	public function print_public_form( $attributes ) {

		// TODO Double check if there's any sensitive data being passed?

		$args = shortcode_atts( array(
			'id' => null,
		), $attributes );

		$id = absint( $args['id'] );

		if ( $id > 0 ) {

			$form_post = get_post( $id );

			if ( $form_post && 'publish' === $form_post->post_status ) {
				return self::form_html( $id );
			}
		}

		return '';
	}

	/**
	 * Shortcode to render payment form preview
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function print_preview_form( $attributes ) {

		$args = shortcode_atts( array(
			'id' => null,
		), $attributes );

		$id = absint( $args['id'] );

		if ( $id > 0 ) {

			$form_post = get_post( $id );

			if ( $form_post && current_user_can( 'manage_options' ) ) {
				return self::form_html( $id );
			}
		}

		return '';
	}

	/**
	 * Private function for returning payment form html common between public & preview modes.
	 *
	 * @param $form_id int
	 *
	 * @return string
	 */
	private function form_html( $form_id ) {

		global $simpay_form;

		// Hook allows Pro forms to override the core default form here.
		$simpay_form = apply_filters( 'simpay_form_view','', $form_id );

		if ( empty( $simpay_form ) ) {

			$simpay_form =  new Default_Form( $form_id );
		}

		if ( $simpay_form instanceof Form ) {

			ob_start();

			$simpay_form->html();

			return ob_get_clean();
		} else {
			return '';
		}
	}

	/**
	 * Shortcode to print the payment details
	 *
	 * @return Payments\Details|string
	 */
	public function print_payment_receipt() {

		$charge_id       = SimplePay()->session->get( 'charge_id' );
		$customer_id     = SimplePay()->session->get( 'customer_id' );

		$session_error = apply_filters( 'simpay_session_error', ( empty( $charge_id ) ? true : false ) );

		if ( $session_error ) {
			$session_error_message = '<p>' . esc_html__( 'An error occurred, but your charge may have gone through. Please contact the site admin.', 'stripe' ) . '</p>';

			return apply_filters( 'simpay_charge_error_message', $session_error_message );
		}

		global $simpay_form;

		$simpay_form = SimplePay()->session->get( 'simpay_form' );

		if ( ! ( $simpay_form instanceof Form ) ) {
			return '';
		}

		$action = '';
		$payment = apply_filters( 'simpay_payment_handler', '', $simpay_form, $action );

		if ( empty( $payment ) ) {
			$payment = new Payment( $simpay_form, $action );
		}

		if ( ! empty( $charge_id ) ) {
			$payment->set_charge( $charge_id );
		}

		if ( $customer_id ) {
			$payment->set_customer( $customer_id );
		}

		do_action( 'simpay_payment_receipt_html', $payment );

		$html = new Payments\Details( $payment );

		$html = $html->html( false );

		return $html;
	}
}
