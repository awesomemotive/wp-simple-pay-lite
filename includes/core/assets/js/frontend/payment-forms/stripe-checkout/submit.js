/* global _ */

/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
import { onPaymentFormError } from '@wpsimplepay/core/frontend/payment-forms';
import { create as createCustomer } from '@wpsimplepay/core/frontend/payments/customer.js';
import { create as createSession } from '@wpsimplepay/core/frontend/payments/session.js';

/**
 * Submit Stripe Checkout Payment Form.
 *
 * @todo DRY presubmission validation checks?
 *
 * @since 3.8.0
 *
 * @param {Element} cardElementInstance Stripe Elements card.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export async function submit( spFormElem, formData ) {
	const {
		enableForm,
		disableForm,
		triggerBrowserValidation,
	} = window.simpayApp;

	// Disable form while processing.
	disableForm( spFormElem, formData, true );

	// HTML5 validation check.
	if ( ! spFormElem[ 0 ].checkValidity() ) {
		triggerBrowserValidation( spFormElem, formData );
		enableForm( spFormElem, formData );

		return;
	}

	// Allow further validation.
	//
	// jQuery( document.body ).on( 'simpayBeforeStripePayment', function( e, spFormElem, formData ) {
	//  formData.isValid = false;
	// } );
	spFormElem.trigger( 'simpayBeforeStripePayment', [ spFormElem, formData ] );

	if ( ! formData.isValid ) {
		enableForm( spFormElem, formData );

		return;
	}

	try {

		// @todo Implement as a "Stripe Checkout" Payment Method?

		let customer_id = null;
		const { hasCustomerFields } = formData;

		// Only generate a custom Customer if we need to map on-page form fields.
		if ( hasCustomerFields ) {
			const { id } = await createCustomer( {}, spFormElem, formData );
			customer_id = id;
		}

		// Generate a Checkout Session.
		const session = await createSession(
			{
				customer_id,
			},
			spFormElem,
			formData
		);

		// Redirect to Stripe.
		spFormElem.stripeInstance
			.redirectToCheckout( {
				sessionId: session.sessionId,
			} )
			.then( ( { error } ) => {
				if ( error ) {
					onPaymentFormError( error, spFormElem, formData );
				}
			} );

	} catch ( error ) {
		onPaymentFormError( error, spFormElem, formData );
	}
}
