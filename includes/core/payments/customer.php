<?php

namespace SimplePay\Core\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Customer
 *
 * @package SimplePay\Payments
 *
 * Wrapper for Stripe API Customer class. Handles any processing dealing with customer information.
 */
class Customer {

	// The Payment object to associate with this Customer object
	private $payment = null;

	// Our Customer object
	public $customer = null;

	/**
	 * Customer constructor.
	 *
	 * @param Payment $payment The Payment object to identify this customer object with.
	 */
	public function __construct( Payment $payment ) {

		// Set our class payment variable to the Payment object passed in
		$this->payment = $payment;

		// Create a customer
		$this->create_customer();
	}

	/**
	 * Create a new custom to use for Stripe transactions
	 */
	public function create_customer() {

		// Get the customer ID if set via filter
		$customer_id = apply_filters( 'simpay_customer_id', '' );

		// Create new customer unless there's an existing customer ID set through filters.
		if ( empty( $customer_id ) ) {

			// Create and save a new customer with the appropriate data
			$this->customer = Stripe_API::request( 'Customer', 'create', apply_filters( 'simpay_stripe_customer_args', array(
				'email' => $this->payment->get_email(),
				'card'  => $this->payment->get_token(),
			) ) );
		} else {
			// Retrieve a customer if one already exists
			$this->customer = Stripe_API::request( 'Customer', 'retrieve', $customer_id );
		}
	}

	/**
	 * Get the ID of the current Customer object
	 *
	 * @return mixed The customer ID
	 */
	public function get_id() {

		if ( $this->customer ) {
			return $this->customer->id;
		}

		return false;
	}

	/**
	 * Get a specific customer by their ID
	 *
	 * @param $id The unique customer ID
	 *
	 * @return mixed Stripe Customer object
	 */
	public static function get_customer_by_id( $id ) {
		return Stripe_API::request( 'Customer', 'retrieve', $id );
	}
}
