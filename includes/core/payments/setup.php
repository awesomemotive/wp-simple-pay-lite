<?php

namespace SimplePay\Core\Payments;

use SimplePay\Core\Abstracts\Form;
use SimplePay\Core\Forms\Default_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Setup
 *
 * @package SimplePay\Payments
 *
 * Main class to call all the classes needed for Payments
 */
class Setup {


	/**
	 * Setup constructor.
	 */
	public function __construct() {

		// We need to wait for functions.php to load so we can check if a filter exists for form settings before processing a payment.
		// This is the earliest we can do this and still get the correct functionality of redirects.
		add_action( 'after_setup_theme', array( $this, 'setup' ) );
	}

	/**
	 * Setup the Payment class with the correct Form ID
	 */
	public function setup() {

		global $simpay_form;

		// Only setup everything if a Stripe Token has been submitted (meaning the Form has been submitted for processing)
		if ( isset( $_POST['simpay_stripe_token'] ) && ! empty( $_POST['simpay_stripe_token'] ) ) {

			$id = intval( $_POST['simpay_form_id'] );

			$simpay_form = apply_filters( 'simpay_form_view', '', $id );

			if ( empty( $simpay_form ) ) {
				$simpay_form = new Default_Form( $id );
			}

			if ( $simpay_form instanceof Form ) {

				$action = 'charge';

				$payment = apply_filters( 'simpay_payment_handler', '', $simpay_form, $action );

				if ( empty( $payment ) ) {
					new Payment( $simpay_form, $action );
				}
			}
		}
	}

}
