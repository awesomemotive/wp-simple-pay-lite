/* global wpApiSettings */

/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
import { apiRequest } from 'core/utils';

/**
 * Create a Stripe\PaymentIntent object based on current formData.
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 * @return {Promise} AJAX promise.
 */
export function create( data, spFormElem, formData ) {
	return apiRequest( 'v2/paymentintent/create', {
		form_values: serialize( spFormElem[0], { hash: true } ),
		form_data: formData,
		form_id: formData.formId,
		...data,
	} );
}

/**
 * Verify a PaymentIntent after any required actions.
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 * @return {Promise} AJAX promise.
 */
export function confirm( data = {}, spFormElem, formData ) {
	return apiRequest( 'v2/paymentintent/confirm', {
		form_values: serialize( spFormElem[0], { hash: true } ),
		form_data: formData,
		form_id: formData.formId,
		...data,
	} );
}

/**
 * Handle server response/next actions for a PaymentIntent.
 *
 * @param {Object} response Server response object.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function handleServerResponse( data, spFormElem, formData ) {
	const { 
		payment_intent: paymentIntent,
		customer_id,
	} = data;

	if ( ! paymentIntent.requires_action ) {
		return false;
	}

	// Handle a PaymentIntent that requires action.
	return spFormElem.stripeInstance
		.handleCardAction( paymentIntent.payment_intent_client_secret )
		.then( async ( result ) => {
			const { error, paymentIntent: paymentIntentAction } = result;

			if ( error ) {
				throw error;
			}

			const paymentIntentConfirmation = await confirm(
				{
					payment_intent_id: paymentIntentAction.id,
					customer_id,
				},
				spFormElem,
				formData
			);

			return handleServerResponse(
				{
					payment_intent: paymentIntentConfirmation,
					customer_id,
				},
				spFormElem,
				formData
			);
		} );
}
