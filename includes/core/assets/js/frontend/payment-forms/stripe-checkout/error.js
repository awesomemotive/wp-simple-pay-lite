/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Display an error message below the Payment Form.
 *
 * @since 4.2.0
 *
 * @param {PaymentForm} paymentForm
 * @param {Object|string} _error Error message or object.
 */
function error( paymentForm, _error ) {
	const { enable: enableForm, __unstableLegacyFormData } = paymentForm;
	const { stripeErrorMessages, unknownError } = __unstableLegacyFormData;

	let errorMessage;

	// Passed empty to clear the error.
	if ( _error && '' === _error ) {
		errorMessage = '';

		// Error is not undefined.
	} else if ( undefined !== _error ) {
		const { message, code } = _error;
		errorMessage = message ? message : _error;

		// Use localized message if code exists.
		if ( code && stripeErrorMessages[ code ] ) {
			errorMessage = stripeErrorMessages[ code ];
		}

		// Unable to determine error.
	} else {
		errorMessage = unknownError;
	}

	// Show message in UI.
	paymentForm.find( '.simpay-errors' ).html( errorMessage );

	// Enable form.
	enableForm();
}

export default error;
