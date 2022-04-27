/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

const { convertToDollars, formatCurrency } = window.spShared;

/**
 * Enable the Payment Form.
 *
 * @since 4.2.0
 *
 * @param {PaymentForm} paymentForm
 */
function enable( paymentForm ) {
	const { cart, __unstableLegacyFormData } = paymentForm;
	const {
		paymentButtonText,
		paymentButtonTrialText,
	} = __unstableLegacyFormData;

	// Remove a loading class indicator.
	paymentForm.removeClass( 'simpay-checkout-form--loading' );

	// Enable the form submit button.
	const submitButtonEl = paymentForm.find( '.simpay-payment-btn' );

	submitButtonEl.prop( 'disabled', false ).removeClass( 'simpay-disabled' );

	if ( 0 === cart.getTotalDueToday() ) {
		submitButtonEl.find( 'span' ).text( paymentButtonTrialText );
	} else {
		const formatted = formatCurrency(
			cart.isZeroDecimal()
				? cart.getTotalDueToday()
				: convertToDollars( cart.getTotalDueToday() ),
			true,
			cart.getCurrencySymbol(),
			cart.isZeroDecimal()
		);

		const amount = `<em class="simpay-total-amount-value">${ formatted }</span>`;

		submitButtonEl
			.find( 'span' )
			.html( paymentButtonText.replace( '{{amount}}', amount ) );
	}
}

export default enable;
