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
 * Creates a Subscription.
 *
 * @since 4.2.0
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {PaymentForm} paymentForm
 * @return {jqXHR} jQuery XMLHttpRequest object.
 */
export function create( data = {}, paymentForm ) {
	const { id, state, __unstableLegacyFormData } = paymentForm;

	return apiRequest( 'v2/subscription', {
		form_values: serialize( paymentForm[ 0 ], { hash: true } ),
		form_data: JSON.stringify( {
			...__unstableLegacyFormData,
			...state,
		} ),
		form_id: id,
		...data,
	} );
}

/**
 * Update a Subscription's payment method.
 *
 * Requires a Subscription's key and linked Customer for verification.
 * Takes direct arguments vs. spFormElem and full form data.
 *
 * @param {number} customerId ID of the Customer.
 * @param {string} customerNonce Customer nonce.
 * @param {string} subscriptionId ID of the Subscription.
 * @param {number} formId ID of the Payment Form.
 * @param {Object} args {
 * @return {Promise} AJAX promise.
 */
export function updatePaymentMethod(
	customerId,
	customerNonce,
	subscriptionId,
	formId,
	args
) {
	return apiRequest(
		`v2/subscription/payment_method/${ subscriptionId }/${ customerId }`,
		{
			customer_nonce: customerNonce,
			form_values: args,
			form_id: formId,
		}
	);
}
