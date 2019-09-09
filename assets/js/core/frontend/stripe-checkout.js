/* global _, simpayApp, spShared */

/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
import { create as createCustomer } from 'core/frontend/payments/customer.js';
import { create as createSession } from 'core/frontend/payments/session.js';

/**
 * Submit payment form.
 *
 * @param {Event} e Form submit Event.
 * @param {Element} cardElementInstance Stripe Elements card.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
async function submitForm( e, spFormElem, formData ) {
	e.preventDefault();

	// Remove existing errors.
	window.simpayApp.showError( spFormElem, formData, '' );

	// Disable form while processing.
	window.simpayApp.disableForm( spFormElem, formData, true );

	// HTML5 validation check.
	if ( ! spFormElem[0].checkValidity() ) {
		window.simpayApp.triggerBrowserValidation( spFormElem, formData );
		window.simpayApp.enableForm( spFormElem, formData );

		return;
	}

	// Allow further validation.
	//
	// jQuery( document.body ).on( 'simpayBeforeStripePayment', function( e, spFormElem, formData ) {
	//  formData.isValid = false;
	// } );
	spFormElem.trigger( 'simpayBeforeStripePayment', [ spFormElem, formData ] );

	if ( ! formData.isValid ) {
		window.simpayApp.enableForm( spFormElem, formData );

		return;
	}

	try {
		const customer = await createCustomer(
			{},
			spFormElem,
			formData
		);

		// Generate a Checkout Session.
		const session = await createSession(
			{
				customer_id: customer.id,
			},
			spFormElem,
			formData
		);

		spFormElem.stripeInstance.redirectToCheckout( {
			sessionId: session.sessionId,
		} ).then( ( result ) => {
			throw result.error;
		} );
	} catch ( error ) {
		if ( _.isObject( error ) ) {
			const { responseJSON, responseText, message } = error;
			const errorMessage = message ? message : ( responseJSON && responseJSON.message ? responseJSON.message : responseText );

			window.simpayApp.showError( spFormElem, formData, errorMessage );
		}

		window.spShared.debugLog( 'Payment Form Error:', error );
		window.simpayApp.enableForm( spFormElem, formData );
	}
}

/**
 * Bind events for Stripe Checkout.
 *
 * @param {Event} e simpayBindCoreFormEventsAndTriggers Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	// Don't continue if this form is not using Stripe Checkout.
	if ( ! window.simpayApp.isStripeCheckoutForm( formData ) ) {
		return;
	}

	const submitBtn = spFormElem.find( '.simpay-payment-btn' );

	if ( 0 === submitBtn.length ) {
		return;
	}

	// Submit form when button is clicked.
	submitBtn[0].addEventListener( 'click', ( e ) => submitForm( e, spFormElem, formData ) );
};
