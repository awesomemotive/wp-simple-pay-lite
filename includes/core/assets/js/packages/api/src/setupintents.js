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
 * Creates a SetupIntent.
 *
 * @since 4.2.0
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {PaymentForm} paymentForm
 * @return {jqXHR} jQuery XMLHttpRequest object.
 */
export function create( data = {}, paymentForm ) {
	const { id, state } = paymentForm;

	return apiRequest( 'v2/setupintent/create', {
		form_values: serialize( paymentForm[ 0 ], { hash: true } ),
		form_data: JSON.stringify( state ),
		form_id: id,
		...data,
	} );
}
