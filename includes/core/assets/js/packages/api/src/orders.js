/**
 * "Order" refers to an internal order, not a Stripe Order object.
 */

/**
 * Internal dependencies.
 */
import { apiRequest } from './api-request.js';
import { createToken } from '../../../frontend/utils/recaptcha.js';

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Creates and returns an order preview.
 *
 * @since 4.6.0
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @return {jqXHR} jQuery XMLHttpRequest object.
 */
export async function preview( data ) {
	// Create a token for the reCAPTCHA.
	const recaptcha = await createToken(
		`simple_pay_form_${ data.form_id }_order_preview`
	);

	return apiRequest( 'v2/order/preview', {
		...data,
		captcha: {
			recaptcha,
		},
	} );
}

/**
 * Submits an Order.
 *
 * @since 4.6.0
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @return {jqXHR} jQuery XMLHttpRequest object.
 */
export async function submit( data ) {
	// Create a token for the reCAPTCHA.
	const recaptcha = await createToken(
		`simple_pay_form_${ data.form_id }_order_submit`
	);

	return apiRequest( 'v2/order/submit', {
		...data,
		captcha: {
			recaptcha,
		},
	} );
}
