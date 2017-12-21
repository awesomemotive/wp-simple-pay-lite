<?php

namespace SimplePay\Core\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Charge
 *
 * @package SimplePay\Payments
 *
 * Wrapper for Stripe API Charge class. Handle non-subscription based charges.
 */
class Charge {

	// Our charge object
	public $charge = null;

	// If the charge was successful or not
	public $success = null;

	// The Payment object to associate this charge with
	public $payment = null;

	/**
	 * Charge constructor.
	 *
	 * @param Payment $payment The Payment object to identify this charge object with.
	 */
	public function __construct( Payment $payment ) {

		$this->payment = $payment;

		$this->charge();
	}

	/**
	 * Process a charge
	 */
	public function charge() {

		global $simpay_form;

		// Fall back to USD if no currency set
		$currency = $this->payment->get_currency();

		// Charge arguments to be sent to Stripe
		$charge_args = array(
			'amount'      => $this->payment->amount, // amount in cents, again
			'currency'    => ! empty( $currency ) ? $currency : 'USD',
			'customer'    => $this->payment->customer->get_id(),
			'description' => $this->payment->get_description(),
			'metadata'    => $this->payment->get_metadata(),
		);

		if ( ! empty( $simpay_form->statement_descriptor ) ) {

			$illegal = array( '<', '>', '"', "'" );

			// Remove slashes
			$descriptor = stripslashes( $simpay_form->statement_descriptor );

			// Remove illegal characters
			$descriptor = str_replace( $illegal, '', $descriptor );

			// Trim to 22 characters max
			$descriptor = substr( $descriptor, 0, 22 );

			$charge_args['statement_descriptor'] = $descriptor;
		}

		// Save our charge response
		$this->charge = Stripe_API::request( 'Charge', 'create', $charge_args );

		if ( false !== $this->charge ) {
			// Fires immediately after Stripe charge object created.
			do_action( 'simpay_charge_created', $this->charge, $this->payment->metadata );

			// Update WP Session variables to store the form ID and the charge ID
			\SimplePay\Core\SimplePay()->session->set( 'form_id', $simpay_form->id );
			\SimplePay\Core\SimplePay()->session->set( 'charge_id', $this->charge->id );
		}
	}

	/**
	 * @param $charge_id The ID of the Stripe charge we want to get the data for
	 *
	 * @return mixed Returns the entire Stripe Charge object data for the specified ID
	 */
	public static function get_charge( $charge_id ) {
		return Stripe_API::request( 'Charge', 'retrieve', $charge_id );
	}

	/**
	 * @return string Returns the charge ID saved to this Charge object
	 */
	public function get_charge_id() {
		return $this->charge->id;
	}
}
