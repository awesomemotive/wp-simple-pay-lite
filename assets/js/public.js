/* global simplePayForms, spGeneral, jQuery */

/**
 * Internal dependencies.
 */
import { setup as setupStripeCheckout } from './core/frontend/stripe-checkout.js';

let simpayApp;

( function( $ ) {
	'use strict';

	let body;

	simpayApp = {

		formCount: 0,

		// Collection of DOM elements of all payment forms.
		spFormElList: {},

		// Internal organized collection of all form data.
		spFormData: {},

		/**
		 *
		 */
		init: function() {
			body = $( document.body );

			simpayApp.spFormElList = body.find( '.simpay-checkout-form' );

			// Setup Stripe Checkout when formData is available.
			body.on( 'simpayBindCoreFormEventsAndTriggers', setupStripeCheckout );

			simpayApp.spFormElList.each( function( i ) {
				const spFormElem = $( this );

				simpayApp.setupCoreForm( spFormElem );
				simpayApp.formCount++;

				body.trigger( 'simpaySetupCoreForm', [ spFormElem ] );
			} );
		},

		/**
		 * Does this payment form use the Stripe Checkout overlay?
		 *
		 * @param {Object} formData Configured form data.
		 */
		isStripeCheckoutForm: function( formData ) {
			return ( undefined === formData.formDisplayType ) || ( 'stripe_checkout' === formData.formDisplayType );
		},

		/**
		 * Setup form object properties and additional data.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 */
		setupCoreForm: function( spFormElem ) {
			// Add a unique identifier to the form for unique selectors.
			spFormElem.attr( 'data-simpay-form-instance', simpayApp.formCount );

			const formId = spFormElem.data( 'simpay-form-id' );

			// Grab the localized data for this form ID.
			const localizedFormData = simplePayForms[ formId ];

			// Merge form data from bootstrapped data (separated based on type... for some reason).
			const formData = {
				formId,
				formInstance: simpayApp.formCount,
				quantity: 1,
				isValid: true,
				stripeParams: {
					...localizedFormData.stripe.strings,
					...localizedFormData.stripe.bools,
				},
				...localizedFormData.form.bools,
				...localizedFormData.form.integers,
				...localizedFormData.form.i18n,
				...localizedFormData.form.strings,
			};

			const {
				stripeParams: {
					key,
					elementsLocale,
				}
			} = formData;

			// Attach Stripe instance to spFormElem to avoid tampering when adjusting formData.
			spFormElem.stripeInstance = Stripe( key, {
				locale: elementsLocale || 'auto',
			} );

			// Set a finalAmount setting so that we can perform all the actions on this.
			// That way if we need to reverse anything we leave the base amount untouched and can revert to it.
			// .amount & .finalAmount prop values = 1 for $1.00 USD, 100 if a zero decimal currency.
			formData.finalAmount = formData.amount;

			// Track in global namespace.
			simpayApp.spFormData[ formId ] = formData;

			body.trigger( 'simpayCoreFormVarsInitialized', [ spFormElem, formData ] );
			body.trigger( 'simpayBindCoreFormEventsAndTriggers', [ spFormElem, formData ] );
		},

		/**
		 * Set the final amount for the Payment Form.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		setCoreFinalAmount: function( spFormElem, formData ) {
			formData.finalAmount = formData.amount;

			body.trigger( 'simpayFinalizeCoreAmount', [ spFormElem, formData ] );
		},

		/**
		 * Disable Payment Form.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 * @param {bool} setSubmitAsLoading Adjust button text to Processing text state.
		 */
		disableForm: function( spFormElem, formData, setSubmitButtonAsLoading ) {
			let submitBtn = spFormElem.find( '.simpay-payment-btn' );
			let loadingText = formData.paymentButtonLoadingText;

			if ( ! window.simpayApp.isStripeCheckoutForm( formData ) ) {
				submitBtn = spFormElem.find( '.simpay-checkout-btn' );
				loadingText = formData.checkoutButtonLoadingText;
			}

			// Disable the form submit button upon initial form load or form submission.
			submitBtn
				.prop( 'disabled', true );

			if ( true === setSubmitButtonAsLoading ) {
				submitBtn
					.addClass( 'simpay-disabled' )
					.find( 'span' )
					.text( loadingText );
			}
		},

		/**
		 * Enable Payment Form.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		enableForm: function( spFormElem, formData ) {
			let submitBtn = spFormElem.find( '.simpay-payment-btn' );
			let loadingText = formData.paymentButtonLoadingText;
			let buttonText = formData.paymentButtonText;

			if ( ! window.simpayApp.isStripeCheckoutForm( formData ) ) {
				submitBtn = spFormElem.find( '.simpay-checkout-btn' );
				loadingText = formData.checkoutButtonLoadingText;
				buttonText = formData.checkoutButtonText;
			}

			// Re-enable button.
			submitBtn
				.prop( 'disabled', false )
				.removeClass( 'simpay-disabled' );

			// Reset button text back to original if needed during validation.
			if ( loadingText === submitBtn.find( 'span' ).text() ) {
				const amount = `<em class="simpay-total-amount-value">${ spShared.formatCurrency( formData.finalAmount, true ) }</span>`;

				buttonText = buttonText
					.replace( '{{amount}}', amount );

				return submitBtn
					.find( 'span' )
					.html( buttonText );
			}
		},

		/**
		 * Show an error.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 * @param {String} errorMessage Message to show.
		 */
		showError: function( spFormElem, formData, errorMessage ) {
			return spFormElem
				.find( '.simpay-errors' )
				.html( errorMessage );
		},

		/**
		 * Ref triggerBrowserValidation in https://stripe.github.io/elements-examples/
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		triggerBrowserValidation: function( spFormElem, formData ) {
			return $( '<input>' )
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

	// Initialize form.
	$( document ).ready( () => simpayApp.init() );
}( jQuery ) );

window.simpayApp = simpayApp;
