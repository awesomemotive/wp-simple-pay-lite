/**
 * Internal dependencies
 */
import { doAction } from '@wpsimplepay/hooks';
import { __unstableUpdatePaymentFormCart } from '@wpsimplepay/payment-forms';
import { Cart } from './cart';

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Setup Stripe Checkout Payment Form.
 *
 * @since 4.2.0
 *
 * @param {PaymentForm} paymentForm
 */
function setup( paymentForm ) {
	const { enable: enableForm, disable: disableForm } = paymentForm;

	// Disable while setting up.
	disableForm();

	// Bind cart to PaymentForm.
	paymentForm.cart = __unstableUpdatePaymentFormCart(
		paymentForm,
		new Cart( { paymentForm } )
	);

	// Bind submit button.
	const submitButtonEl = paymentForm.find( '.simpay-payment-btn' )[ 0 ];

	if ( ! submitButtonEl ) {
		return;
	}

	submitButtonEl.addEventListener( 'click', ( e ) => {
		e.preventDefault();

		// HTML5 validation check.
		const { triggerBrowserValidation } = window.simpayApp;

		if ( ! paymentForm[ 0 ].checkValidity() ) {
			triggerBrowserValidation( paymentForm );

			return;
		}

		/**
		 * Allows processing during a Payment Form submission.
		 *
		 * @since 4.2.0
		 *
		 * @param {PaymentForm} paymentForm
		 */
		doAction( 'simpaySubmitPaymentForm', paymentForm );
	} );

	enableForm();
}

export default setup;
