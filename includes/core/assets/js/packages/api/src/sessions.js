/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
import { apiRequest } from './api-request.js';

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Creates a Checkout Session.
 *
 * @since 4.2.0
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {PaymentForm} paymentForm
 * @return {jqXHR} jQuery XMLHttpRequest object.
 */
export function create( data = {}, paymentForm ) {
	const { id, state, __unstableLegacyFormData } = paymentForm;

	return apiRequest( 'v2/checkout-session', {
		form_values: serialize( paymentForm[ 0 ], { hash: true } ),
		form_data: JSON.stringify( {
			...__unstableLegacyFormData,
			...state,
		} ),
		form_id: id,
		...data,
	} );
}
