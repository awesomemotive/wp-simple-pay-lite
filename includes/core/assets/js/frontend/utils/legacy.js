/* global _, jQuery */

/**
 * Internal dependencies
 */
const { convertToDollars, formatCurrency } = window.spShared;

export default {
	init: _.noop,
	setupCoreForm: _.noop,

	/**
	 * Does this payment form use the Stripe Checkout overlay?
	 *
	 * @param {Object} formData Configured form data.
	 */
	isStripeCheckoutForm( formData ) {
		return (
			undefined === formData.formDisplayType ||
			'stripe_checkout' === formData.formDisplayType
		);
	},

	/**
	 * Set the final amount for the Payment Form.
	 *
	 * @param {jQuery} spFormElem Form element jQuery object.
	 * @param {Object} formData Configured form data.
	 */
	setCoreFinalAmount( spFormElem, formData ) {
		// Backwards compat.
		formData.finalAmount = spFormElem.cart.getTotalDueToday();

		jQuery( document.body ).trigger( 'simpayFinalizeCoreAmount', [
			spFormElem,
			formData,
		] );
	},

	/**
	 * Disable Payment Form.
	 *
	 * @param {jQuery} spFormElem Form element jQuery object.
	 * @param {Object} formData Configured form data.
	 * @param {bool} setSubmitAsLoading Adjust button text to Processing text state.
	 * @param setSubmitButtonAsLoading
	 */
	disableForm( spFormElem, formData, setSubmitButtonAsLoading ) {
		let submitBtn = spFormElem.find( '.simpay-payment-btn' );
		let loadingText = formData.paymentButtonLoadingText;

		spFormElem.addClass( 'simpay-checkout-form--loading' );

		if ( ! window.simpayApp.isStripeCheckoutForm( formData ) ) {
			submitBtn = spFormElem.find( '.simpay-checkout-btn' );
			loadingText = formData.checkoutButtonLoadingText;
		}

		// Disable the form submit button upon initial form load or form submission.
		submitBtn.prop( 'disabled', true );

		if ( true === setSubmitButtonAsLoading ) {
			submitBtn
				.addClass( 'simpay-disabled' )
				.find( 'span' )
				.html( loadingText );
		}
	},

	/**
	 * Enable Payment Form.
	 *
	 * @param {jQuery} spFormElem Form element jQuery object.
	 * @param {Object} formData Configured form data.
	 */
	enableForm( spFormElem, formData ) {
		const { cart } = spFormElem;

		// Do nothing if Cart is unavailable.
		if ( undefined === cart ) {
			return;
		}

		let submitBtn = spFormElem.find( '.simpay-payment-btn' );
		let loadingText = formData.paymentButtonLoadingText;
		let buttonText = formData.paymentButtonText;

		spFormElem.removeClass( 'simpay-checkout-form--loading' );

		if ( ! window.simpayApp.isStripeCheckoutForm( formData ) ) {
			submitBtn = spFormElem.find( '.simpay-checkout-btn' );
			loadingText = formData.checkoutButtonLoadingText;
			buttonText = formData.checkoutButtonText;
		}

		// Re-enable button.
		submitBtn.prop( 'disabled', false ).removeClass( 'simpay-disabled' );

		// Embed in to an arbitrary node to retrieve parsed entities.
		const embeddedHtml = document.createElement( 'div' );
		embeddedHtml.innerHTML = loadingText;

		// Reset button text back to original if needed during validation.
		if (
			jQuery( embeddedHtml ).html() === submitBtn.find( 'span' ).html()
		) {
			if ( 0 === cart.getTotalDueToday() ) {
				const { checkoutButtonTrialText } = formData;
				submitBtn.find( 'span' ).text( checkoutButtonTrialText );
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

				buttonText = buttonText.replace( '{{amount}}', amount );

				submitBtn.find( 'span' ).html( buttonText );
			}
		}
	},

	/**
	 * Show an error.
	 *
	 * @param {jQuery} spFormElem Form element jQuery object.
	 * @param {Object} formData Configured form data.
	 * @param {string} errorMessage Message to show.
	 */
	showError( spFormElem, formData, errorMessage ) {
		return spFormElem.find( '.simpay-errors' ).html( errorMessage );
	},

	/**
	 * Ref triggerBrowserValidation in https://stripe.github.io/elements-examples/
	 *
	 * @param {jQuery} spFormElem Form element jQuery object.
	 * @param {Object} formData Configured form data.
	 */
	triggerBrowserValidation( spFormElem, formData ) {
		return jQuery( '<input>' )
			.attr( {
				type: 'submit',
				style: {
					display: 'none',
				},
			} )
			.appendTo( spFormElem )
			.click()
			.remove();
	},
};
