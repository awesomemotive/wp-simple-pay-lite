/* global spShared, simpayApp, simpayAppPro */

/**
 * Update all labels.
 *
 * @param {Event} e Change event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function update( e, spFormElem, formData ) {
	totalAmount( spFormElem, formData );
	recurringAmount( spFormElem, formData );
	taxAmount( spFormElem, formData );
};

/**
 * Update "Total Amount" label, and Submit Button label.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function totalAmount( spFormElem, formData ) {
	window.simpayApp.setCoreFinalAmount( spFormElem, formData );

	// Convert amount to dollars (decimals) & add currency symbol, etc.
	const totalLabelText = window.spShared.formatCurrency( formData.finalAmount, true );
	spFormElem.find( '.simpay-total-amount-value' ).text( totalLabelText );
}

/**
 * Update "Recurring Amount" label.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function recurringAmount( spFormElem, formData ) {
	const recurringBaseAmount = formData.planAmount * formData.quantity;
	const recurringTaxAmount = window.simpayAppPro.calculateTaxAmount( recurringBaseAmount, formData.taxPercent );
	const recurringAmountFinal = recurringBaseAmount + recurringTaxAmount;

	let recurringAmountFormatted = window.spShared.formatCurrency( recurringAmountFinal, true );

	if ( formData.planIntervalCount > 1 ) {
		recurringAmountFormatted += ' every ' + formData.planIntervalCount + ' ' + formData.planInterval + 's';
	} else {
		recurringAmountFormatted +=  '/' + formData.planInterval ;
	}

	spFormElem.find( '.simpay-total-amount-recurring-value' ).text( recurringAmountFormatted );
};

/**
 * Update "Tax Amount" label.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function taxAmount( spFormElem, formData ) {
	spFormElem.find( '.simpay-tax-amount-value' ).text( window.spShared.formatCurrency( formData.taxAmount, true ) );
};
