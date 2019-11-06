<?php

namespace SimplePay\Core;

use SimplePay\Core\Abstracts\Form;
use SimplePay\Core\Forms\Default_Form;
use SimplePay\Core\Payments\Payment;
use SimplePay\Core\Payments\Payment_Confirmation;
use SimplePay\Core\Payments\Stripe_API;

use function SimplePay\Core\SimplePay;

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
		add_shortcode( 'simpay_payment_receipt', array( $this, 'print_payment_receipt' ), 10, 2 );

		// Deprecated.
		add_shortcode( 'simpay_error', array( $this, 'print_errors' ) );

		do_action( 'simpay_add_shortcodes' );
	}

	/**
	 * Error message shortcode
	 *
	 * @since unknown
	 * @since 3.6.0 Deprecated. Prints nothing.
	 *
	 * @return string
	 */
	public function print_errors() {
		return '';
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
		$has_keys = simpay_check_keys_exist();

		// Show a notice to admins if they have not setup Stripe.
		if ( ! $has_keys && current_user_can( 'manage_options' ) ) {
			return wp_kses_post( sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Please complete your %1$sStripe Setup%2$s to view the payment form.', 'stripe' ),
				sprintf(
					'<a href="%s">',
					add_query_arg(
						array(
							'page' => 'simpay_settings',
							'tab'  => 'keys',
						),
						admin_url( 'admin.php' )
					)
				),
				'</a>'
			) );
		// Show nothing to guests if Stripe is not setup.
		} else if ( ! $has_keys && ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		try {

			/**
			 * Filter the form type used to generate a Stripe PaymentIntent.
			 *
			 * @since unknown
			 *
			 * @param string $form_instance Form instance. Blank by default to load Default_Form.
			 * @param int    $form_id Form ID.
			 */
			$simpay_form = apply_filters( 'simpay_form_view', '', $form_id );

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
		} catch( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Prints payment details.
	 *
	 * Since 3.6.0 the shortcode can be a wrapped shortcode with content inside
	 * that will be parsed like the content in Settings > Payment Confirmation.
	 *
	 * @todo Maybe split out some of grunt work involved in collecting the necessary information.
	 * @todo Try to expand any available objects to reduce extra API calls.
	 *
	 * @since unknown
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string
	 */
	public function print_payment_receipt( $atts = array(), $content = '' ) {
		$atts = shortcode_atts(
			array(),
			$atts,
			'simpay_payment_confirmation'
		);

		// Check for a Checkout Session ID or Customer ID.
		// This area blurs the lines between Lite/Pro code since Lite can only use Checkout Session.
		$session_id  = isset( $_GET['session_id'] ) ? esc_attr( $_GET['session_id' ] ) : false;
		$customer_id = isset( $_GET['customer_id'] ) ? esc_attr( $_GET['customer_id' ] ) : false;

		// Do nothing if we can't find a Checkout Session or Customer to reference.
		if ( ! ( $session_id || $customer_id ) ) {
			return Payment_Confirmation\get_error();
		}

		try {
			// Using the available identifier, find the relevant customer.
			if ( $session_id ) {
				$session = Stripe_API::request( 'Checkout\Session', 'retrieve', array(
					'id'     => $session_id,
					'expand' => array(
						'customer',
					)
				) );

				$customer = $session->customer;
			} else {
				$customer = Stripe_API::request( 'Customer', 'retrieve', $customer_id );
			}

			// Retrieve the PaymentIntent the Customer is linked to.
			$paymentintents = Stripe_API::request(
				'PaymentIntent',
				'all',
				array(
					'customer' => $customer->id,
					'limit'    => 1,
					'expand'   => array(
						'data.payment_method',
					)
				)
			);

			$payment_confirmation_data = array(
				'customer'       => $customer,
				'paymentintents' => $paymentintents->data,
			);

			/**
			 * Filters the payment confirmation data.
			 *
			 * @since 3.6.0
			 *
			 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
			 */
			$payment_confirmation_data = apply_filters( 'simpay_payment_confirmation_data', $payment_confirmation_data );

			// Find the used Payment Form.
			$form_id = isset( $payment_confirmation_data['customer']->metadata->simpay_form_id )
				? $payment_confirmation_data['customer']->metadata->simpay_form_id
				: false;

			// If the Customer was generated by Checkout\Session look in the PaymentIntent for the form ID.
			if ( false === $form_id ) {
				$form_id = isset( current( $payment_confirmation_data['paymentintents'] )->metadata->simpay_form_id )
					? current( $payment_confirmation_data['paymentintents'] )->metadata->simpay_form_id
					: false;
			}

			/**
			 * Filters the ID of the form to retrieve.
			 *
			 * Allows Pro to find the ID via Subscription metadata.
			 *
			 * @since 3.6.2
			 *
			 * @param int   $form_id ID of the form the payment was created from.
			 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
			 */
			$form_id = apply_filters( 'simpay_payment_confirmation_form_id', $form_id, $payment_confirmation_data );

			if ( false === $form_id ) {
				return Payment_Confirmation\get_error();
			}

			/** This filter is documented in includes/core/shortcodes.php */
			$form = apply_filters( 'simpay_form_view', '', $form_id );

			if ( empty( $form ) ) {
				$form = new Default_Form( $form_id );
			}

			// Attach form to confirmation data.
			$payment_confirmation_data['form'] = $form;

			// Retrieve default content if nothing has been passed to the shortcode.
			if ( '' === $content ) {
				$content = Payment_Confirmation\get_content();
			}

			/**
			 * Filters the content of the confirmation shortcode.
			 *
			 * This allows different form types to parse the confirmation template tags differently.
			 *
			 * @since 3.6.0
			 *
			 * @param string                         $content Payment confirmation shortcode content.
			 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
			 * @param \Stripe\Customer               $customer Stripe Customer.
			 */
			$content = apply_filters( 'simpay_payment_confirmation_content', $content, $payment_confirmation_data );

			$content = Payment_Confirmation\Template_Tags\parse_content( $content, $payment_confirmation_data );

			/**
			 * Internal hook to allow legacy hooks that rely on "complete" payments.
			 */
			do_action( '_simpay_payment_confirmation', $payment_confirmation_data, $form, $_GET );
		} catch ( \Exception $e ) {
			if ( current_user_can( 'manage_options' ) ) {
				$content = $e->getMessage();
			} else {
				$content = Payment_Confirmation\get_error();
			}
		}

		/**
		 * Filters the HTML output before the payment confirmation.
		 *
		 * @since unknown
		 *
		 * @param string
		 */
		$before_html = apply_filters( 'simpay_before_payment_details', '<div class="simpay-payment-receipt-wrap">' );

		/**
		 * Filters the HTML output after the payment confirmation.
		 *
		 * @since unknown
		 *
		 * @param string
		 */
		$after_html = apply_filters( 'simpay_after_payment_details', '</div>' );

		return $before_html . wpautop( $content ) . $after_html;
	}
}
