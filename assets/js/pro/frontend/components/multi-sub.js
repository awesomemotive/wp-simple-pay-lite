/* global spShared, _ */

/**
 * Update form data when a plan changes.
 *
 * @param {Event} e Change event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function update( e, spFormElem, formData ) {
	const wrapperElem = spFormElem.find( '.simpay-plan-wrapper' );

	if ( 0 === wrapperElem.length ) {
		return;
	}

	const errorEl = spFormElem.find( '.simpay-errors' );
	const customAmountInput = spFormElem.find( '.simpay-custom-amount-input' );
	const options = wrapperElem.find( '.simpay-multi-sub' );

	let selectedOption = '';

	let 
		planId,
		planSetupFee,
		planAmount,
		planInterval,
		planIntervalCount,
		planTrial,
		planMaxCharges;

	// Check if it is a dropdown or a radio button setup and act accordingly
	if ( options.first().is( 'option' ) ) {
		selectedOption = options.filter( ':selected' );
	} else {
		selectedOption = options.filter( ':checked' );
	}

	planId = selectedOption.data( 'plan-id' ) || '';
	planSetupFee = selectedOption.data( 'plan-setup-fee' ) || 0;
	planAmount = selectedOption.data( 'plan-amount' )  || 0;
	planInterval = selectedOption.data( 'plan-interval' ) || '';
	planIntervalCount = selectedOption.data( 'plan-interval-count' ) || 1;
	planTrial = ( undefined !== selectedOption.data( 'plan-trial' ) );
	planMaxCharges = selectedOption.data( 'plan-max-charges' ) || 0;

	if ( planTrial ) {
		formData.amount = 0;
	}

	// Update formData plan attributes.
	formData = _.extend( formData, {
		planId,
		planSetupFee,
		planAmount,
		planInterval,
		planIntervalCount,
		isTrial: planTrial,
	} );

	// Update custom amount checker
	if ( selectedOption.hasClass( 'simpay-custom-plan-option' ) ) {
		spFormElem.find( '.simpay-has-custom-plan' ).val( 'true' );
	} else {
		spFormElem.find( '.simpay-has-custom-plan' ).val( '' );
	}

	formData.useCustomPlan = ( 'simpay_custom_plan' === selectedOption.val() );

	// Reset custom amount validation.
	errorEl.empty();
	customAmountInput.removeClass( 'simpay-input-error' );

	// If the custom amount plan is selected, focus input & blank out value.
	// If an existing plan is selected, don't focus input & set input to plan value.
	if ( formData.useCustomPlan ) {
		customAmountInput.val( '' );
		formData.amount = 0;

		if ( ! customAmountInput.is( ':focus' ) ) {
			customAmountInput.focus();
		}
	} else {
		customAmountInput.val( window.spShared.formatCurrency( planAmount ) );
	}

	// Update hidden fields.
	spFormElem.find( '.simpay-multi-plan-id' ).val( planId );
	spFormElem.find( '.simpay-multi-plan-setup-fee' ).val( planSetupFee );
	spFormElem.find( '.simpay-max-charges' ).val( planMaxCharges );

	// Custom trigger after completed.
	spFormElem.trigger( 'simpayMultiPlanChanged' );

	// Alert the rest of the components they need to update.
	spFormElem.trigger( 'totalChanged', [ spFormElem, formData ] );
};
