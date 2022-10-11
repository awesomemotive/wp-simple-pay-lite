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
 * Creates a PaymentIntent.
 *
 * @since 4.2.0
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {PaymentForm} paymentForm
 * @return {jqXHR} jQuery XMLHttpRequest object.
 */
export function create( data = {}, paymentForm ) {
	const { id, getFormData } = paymentForm;

	return apiRequest( 'v2/paymentintent/create', {
		form_values: serialize( paymentForm[ 0 ], { hash: true } ),
		form_data: getFormData(),
		form_id: id,
		...data,
	} );
}
