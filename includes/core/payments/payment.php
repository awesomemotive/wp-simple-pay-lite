<?php

namespace SimplePay\Core\Payments;

use SimplePay\Core\Abstracts\Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Payment
 *
 * @package SimplePay\Payments
 *
 * A container class to hold all of the various data that might be tied to one single payment.
 */
class Payment {

	// Private
	protected $token = null;
	protected $email = null;

	// Public
	public $customer = null;
	public $customer_id = null;
	public $charge = null;
	public $form_id = null;
	public $amount = 0;
	public $description = null;
	public $company_name = null;
	public $currency = null;
	public $locale = null;
	public $test_mode = null;
	public $metadata = array();
	public $decimal_places = '';
	public $form = null;
	public $success = false;

	// TODO DRY/Simplify contructor logic between core & pro.

	/**
	 * Payment constructor.
	 *
	 * @param Form|null $form
	 * @param string    $action
	 */
	public function __construct( Form $form, $action = '' ) {

		// Run this always so our main Payment class has the right attributes set up that other classes can take advantage of
		$this->set_attributes();

		// If the Form object is not set then we want to skip any processing
		if ( 'charge' === $action ) {

			// Fire action before processing form.
			do_action( 'simpay_pre_process_form' );

			// Process CORE form
			$this->process_form();

			$this->set_payment_session();
			$this->handle_redirect();
		}
	}

	/**
	 * Set all of the attributes we need to attach to this Payment.
	 */
	public function set_attributes() {

		global $simpay_form;

		// Currency options
		$this->currency       = $simpay_form->currency;
		$this->decimal_places = simpay_get_decimal_places();

		// Set store name and locale from general settings
		$this->company_name = $simpay_form->company_name;
		$this->locale       = $simpay_form->locale;

		do_action( 'simpay_payment_attributes', $this );
	}

	/**
	 * Process the form for payment
	 */
	public function process_form() {

		global $simpay_form;

		// Set the amount for the Payment to the same as the form amount
		if ( isset( $_POST['simpay_amount'] ) && ! empty( $_POST['simpay_amount'] ) ) {
			// Get the total set on the form (if changes were made using JS)
			$this->amount = floatval( $_POST['simpay_amount'] );
		} else {
			// Fallback to our set total amount
			$this->amount = $simpay_form->total_amount;
		}

		// Set the description
		$this->description = get_post_meta( $simpay_form->id, '_item_description', true );

		// Set token and email needed for charges
		$this->token = $_POST['simpay_stripe_token'];
		$this->email = $_POST['simpay_stripe_email'];

		if ( $simpay_form->enable_shipping_address ) {
			$this->process_shipping_meta();
		}

		// Create a new Customer object
		$this->customer    = new Customer( $this );
		$this->customer_id = $this->customer->get_id();

		do_action( 'simpay_process_form', $this );

		// Create the charge
		$charge = apply_filters( 'simpay_charge', '' );

		if ( empty( $charge ) ) {
			new Charge( $this );
		}
	}

	/**
	 * Process shipping meta
	 */
	public function process_shipping_meta() {

		$meta = array();

		// Check all of these and only add ones that are added

		if ( isset( $_POST['simpay_shipping_name'] ) && ! empty( $_POST['simpay_shipping_name'] ) ) {
			$meta['shipping_name'] = sanitize_text_field( $_POST['simpay_shipping_name'] );
		}

		if ( isset( $_POST['simpay_shipping_address_line1'] ) && ! empty( $_POST['simpay_shipping_address_line1'] ) ) {
			$meta['shipping_address_line1'] = sanitize_text_field( $_POST['simpay_shipping_address_line1'] );
		}

		if ( isset( $_POST['simpay_shipping_country'] ) && ! empty( $_POST['simpay_shipping_country'] ) ) {
			$meta['shipping_country'] = sanitize_text_field( $_POST['simpay_shipping_country'] );
		}

		if ( isset( $_POST['simpay_shipping_zip'] ) && ! empty( $_POST['simpay_shipping_zip'] ) ) {
			$meta['shipping_postal_code'] = sanitize_text_field( $_POST['simpay_shipping_zip'] );
		}

		if ( isset( $_POST['simpay_shipping_state'] ) && ! empty( $_POST['simpay_shipping_state'] ) ) {
			$meta['shipping_state'] = sanitize_text_field( $_POST['simpay_shipping_state'] );
		}

		if ( isset( $_POST['simpay_shipping_city'] ) && ! empty( $_POST['simpay_shipping_city'] ) ) {
			$meta['shipping_city'] = sanitize_text_field( $_POST['simpay_shipping_city'] );
		}

		$this->metadata = array_merge( $meta, $this->metadata );
	}

	/**
	 * Store payment data in sessions.
	 */
	public function set_payment_session() {

		global $simpay_form;

		// TODO Convert form object to array and clean recursively before saving to session?
		// TODO Cast using `(array)` or use get_object_vars() ?
		\SimplePay\Core\SimplePay()->session->set( 'simpay_form', $simpay_form );
		//\SimplePay\Core\SimplePay()->session->set( 'simpay_form', (array) simpay_clean( $simpay_form ) );
	}

	/**
	 * Process the redirect after a form submission
	 */
	public function handle_redirect() {

		global $simpay_form;

		// With the way our Stripe API error catching works if we made it this far then we should be successful.

		if ( has_filter( 'simpay_form_' . $simpay_form->id . '_payment_success_page' ) || 'redirect' === simpay_get_saved_meta( $simpay_form->id, '_success_redirect_type' ) ) {

			wp_redirect( $simpay_form->payment_success_page );
			exit;
		} else {

			wp_safe_redirect( $simpay_form->payment_success_page );
			exit;
		}
	}

	/**
	 * This may look confusing at first since we are "getting" a charge inside this function.
	 * But what is actually happening is that we are setting the charge of this class from somewhere else based off of
	 * a Charge ID.
	 */
	public function set_charge( $id ) {
		$this->charge = Charge::get_charge( $id );
	}

	/**
	 * Set the customer property tied to this instance to a specific customer id
	 *
	 * @param $id
	 */
	public function set_customer( $id ) {
		$this->customer = Customer::get_customer_by_id( $id );
	}

	/**
	 * Get the current token set
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Get the current email set
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get the description applying any user filters first
	 */
	public function get_description() {
		return apply_filters( 'simpay_payment_description', $this->description );
	}

	/**
	 * Get the currency applying any user filters first
	 */
	public function get_currency() {
		global $simpay_form;

		return $simpay_form->currency;
	}

	/**
	 * Get the metadata applying any user filters first
	 */
	public function get_metadata() {
		return apply_filters( 'simpay_payment_metadata', $this->metadata );
	}
}


