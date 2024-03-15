/**
 * Internal dependencies
 */
// Payment Form types.
import './stripe-checkout';

import { addAction } from '@wpsimplepay/hooks';
import { createToken } from '../utils/recaptcha.js';

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Sets up the relevant Payment Form type when a Payment Form is initialized.
 *
 * @param {PaymentForm} paymentForm
 */
function setupPaymentForm( { setup } ) {
	return setup();
}
addAction( 'simpaySetupPaymentForm', 'wpsp/paymentForm', setupPaymentForm );

/**
 * Submits a Payment Form.
 *
 * @param {PaymentForm} paymentForm
 */
async function submitPaymentForm( paymentForm ) {
	const {
		id,
		setState,
		submit,
		disable: disableForm,
		enable: enableForm,
		error: onError,
		__unstableLegacyFormData,
	} = paymentForm;

	// Disable while processing.
	disableForm();

	// Allow further validation.
	// Backwards compatibility.
	paymentForm.trigger( 'simpayBeforeStripePayment', [
		paymentForm,
		__unstableLegacyFormData,
	] );

	if ( ! __unstableLegacyFormData.isValid ) {
		enableForm();

		return;
	}

	try {
		// Generate reCAPTCHA tokens before proceeding.
		//
		// @todo This is still tightly coupled with the form submission...
		// but maybe that's fine. It is no longer duplicated between Payment Form
		// types, so that's better.
		//
		// doAction can't be used here because all assigned callees are executed at once.
		await createToken( `simple_pay_form_${ id }_customer` ).then(
			( token ) => {
				setState( {
					customerCaptchaToken: token,
				} );
			}
		);

		await createToken( `simple_pay_form_${ id }_payment` ).then(
			( token ) => {
				setState( {
					paymentCaptchaToken: token,
				} );
			}
		);

		submit();
	} catch ( error ) {
		onError( error );
	}
}
addAction( 'simpaySubmitPaymentForm', 'wpsp/paymentForm', submitPaymentForm );
