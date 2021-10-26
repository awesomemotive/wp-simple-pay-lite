/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Disable the Payment Form.
 *
 * @since 4.2.0
 *
 * @param {PaymentForm} paymentForm
 */
function disable( paymentForm ) {
	const { __unstableLegacyFormData } = paymentForm;
	const { paymentButtonLoadingText } = __unstableLegacyFormData;

	// Add a loading class indicator.
	paymentForm.addClass( 'simpay-checkout-form--loading' );

	// Disable the form submit button.
	paymentForm
		.find( '.simpay-payment-btn' )
		.prop( 'disabled', true )
		.addClass( 'simpay-disabled' )
		.find( 'span' )
		.html( paymentButtonLoadingText );
}

export default disable;
