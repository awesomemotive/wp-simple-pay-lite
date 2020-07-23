/* global simplePayForms, spGeneral, jQuery */

/**
 * Internal dependencies.
 */
import hooks from '@wpsimplepay/hooks';
import { getPaymentForms } from '@wpsimplepay/core/frontend/payment-forms';

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
		spFormElems: {},

		/**
		 *
		 */
		init() {
			body = $( document.body );

			// Skip "Update Payment Method Forms"
			// We need to keep .simpay-checkout-form so styles are applied.
			simpayApp.spFormElList = body.find( '.simpay-checkout-form:not(.simpay-update-payment-method)' );

			const stripeCheckout = getPaymentForms()['stripe-checkout'];
			const { setup: setupPaymentForm } = stripeCheckout;

			// Setup Stripe Checkout when formData is available.
			body.on( 'simpayCoreFormVarsInitialized', setupPaymentForm );

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
		isStripeCheckoutForm( formData ) {
			return ( undefined === formData.formDisplayType ) || ( 'stripe_checkout' === formData.formDisplayType );
		},

		/**
		 * Setup form object properties and additional data.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 */
		setupCoreForm( spFormElem ) {
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
				...localizedFormData.form.config,
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

			// Track in global namespace.
			window.simpayApp.spFormData[ formId ] = formData;
			window.simpayApp.spFormElems[ formId ] = spFormElem;

			body.trigger( 'simpayCoreFormVarsInitialized', [ spFormElem, formData ] );
			body.trigger( 'simpayBindCoreFormEventsAndTriggers', [ spFormElem, formData ] );
		},

		/**
		 * Set the final amount for the Payment Form.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		setCoreFinalAmount( spFormElem, formData ) {
			// Backwards compat.
			formData.finalAmount = spFormElem.cart.getTotal();

			body.trigger( 'simpayFinalizeCoreAmount', [ spFormElem, formData ] );
		},

		/**
		 * Disable Payment Form.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 * @param {bool} setSubmitAsLoading Adjust button text to Processing text state.
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
			submitBtn
				.prop( 'disabled', true );

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
			submitBtn
				.prop( 'disabled', false )
				.removeClass( 'simpay-disabled' );

			// Embed in to an arbitrary node to retrieve parsed entities.
			const embeddedHtml = document.createElement( 'div' );
			embeddedHtml.innerHTML = loadingText;

			// Reset button text back to original if needed during validation.
			if ( $( embeddedHtml ).html() === submitBtn.find( 'span' ).html() ) {
				const {
					convertToDollars,
					formatCurrency,
				} = window.spShared;

				const total = convertToDollars( cart.getTotal() );
				const formatted = formatCurrency( total, true );
				const amount = `<em class="simpay-total-amount-value">${ formatted }</span>`;

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
		 * @param {string} errorMessage Message to show.
		 */
		showError( spFormElem, formData, errorMessage ) {
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
		triggerBrowserValidation( spFormElem, formData ) {
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

/**
 * Globally accessible object of WP Simple Pay-related functionality.
 */
window.wpsp = {
	hooks,
};
