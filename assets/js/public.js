/* global simplePayForms, spGeneral, jQuery */

var simpayApp = {};

( function( $ ) {
	'use strict';

	var body;

	simpayApp = {

		// Collection of DOM elements of all payment forms
		spFormElList: {},

		// Internal organized collection of all form data
		spFormData: {},

		init: function() {

			// Set main vars on init.
			body = $( document.body );

			simpayApp.spFormElList = body.find( '.simpay-checkout-form' );

			simpayApp.spFormElList.each( function() {

				var spFormElem = $( this );
				simpayApp.setupCoreForm( spFormElem );

				body.trigger( 'simpaySetupCoreForm', [ spFormElem ] );
			} );
		},

		// Does this payment form use the Stripe Checkout overlay?
		isStripeCheckoutForm: function( formData ) {

			return ( undefined === formData.formDisplayType ) || ( 'stripe_checkout' === formData.formDisplayType );
		},

		setupCoreForm: function( spFormElem ) {

			var formId = spFormElem.data( 'simpay-form-id' );

			// Grab the localized data for this form ID.
			var localizedFormData = simplePayForms[ formId ];

			// Set formData array index of the current form ID to match the localized data passed over for form settings.
			var formData = $.extend( {}, localizedFormData.form.bools, localizedFormData.form.integers, localizedFormData.form.i18n, localizedFormData.form.strings );

			// Set form ID from data attribute.
			formData.formId = formId;

			// Set a finalAmount setting so that we can perform all the actions on this.
			// That way if we need to reverse anything we leave the base amount untouched and can revert to it.
			// .amount & .finalAmount prop values = 1 for $1.00 USD, 100 if a zero decimal currency.
			formData.finalAmount = formData.amount;

			// Set the default quantity to 1.
			formData.quantity = 1;

			// Add a new object called stripeParams to the spFormData object. This contains only the stripeParams that need to be sent. This is so we don't have to manually set all the stripeParams
			// And we can just use what was passed from PHP so we only include the minimum needed and let Stripe defaults take care of anything that's missing here.
			formData.stripeParams = $.extend( {}, localizedFormData.stripe.strings, localizedFormData.stripe.bools );

			// Set a fallback button label.
			formData.oldPanelLabel = ( undefined !== formData.stripeParams.panelLabel ) ? formData.stripeParams.panelLabel : '';

			// Set button element class that will trigger payment form submit.
			// Different for Pro custom forms implementation.
			formData.submitBtnClass = 'simpay-payment-btn';

			body.trigger( 'simpayCoreFormVarsInitialized', [ spFormElem, formData ] );

			if ( simpayApp.isStripeCheckoutForm( formData ) ) {
				simpayApp.setupStripeCheckout( spFormElem, formData );
			}

			simpayApp.spFormData[ formId ] = formData;

			body.trigger( 'simpayBindCoreFormEventsAndTriggers', [ spFormElem, formData ] );
		},

		setupStripeCheckout: function( spFormElem, formData ) {

			var submitBtn = spFormElem.find( '.' + formData.submitBtnClass );

			// Variable to hold the Stripe configuration.
			var stripeHandler = null;

			if ( submitBtn.length ) {

				// Stripe Checkout handler configuration.
				// Only token callback function set here. All other params set in stripeParams.
				// Chrome on iOS needs handler set before click event or else checkout won't open in a new tab.
				// See "How do I prevent the Checkout popup from being blocked?"
				// Full docs: https://stripe.com/docs/checkout#integration-custom
				stripeHandler = StripeCheckout.configure( {

					// Key param MUST be sent here instead of stripeHandler.open().
					key: formData.stripeParams.key,
					token: function( token, args ) {
						handleStripeCheckoutToken( token, args );
					},
					opened: function() {
					},
					closed: function() {
					}
				} );
			}

			/** Form submitted through checkout button click or Enter key. */

			function submitCoreForm() {

				// Init flag for form validation state.
				formData.isValid = true;

				// Trigger custom event right before executing payment.
				// For Pro version client-side validation and other client-side changes.
				spFormElem.trigger( 'simpayBeforeStripePayment', [ spFormElem, formData ] );

				// Now check validation state flag before continuing.
				// There are no validation checks in the Lite version natively.
				// But this is needed for Pro and/or custom code.
				if ( !formData.isValid ) {
					return;
				}

				simpayApp.setCoreFinalAmount( spFormElem, formData );

				// Send the final amount to Stripe params.
				// Stripe expects amounts in cents (100 for $1.00 USD / no decimals), so convert here.
				formData.stripeParams.amount = spShared.convertToCents( formData.finalAmount );

				// Set the same cents value to the hidden input for submitting form for processing.
				spFormElem.find( '.simpay-amount' ).val( formData.stripeParams.amount );

				stripeHandler.open( formData.stripeParams );
			}

			/**
			 * Stripe Checkout token handler
			 *
			 * https://stripe.com/docs/checkout#integration-custom
			 *
			 * @param token Stripe Token object - https://stripe.com/docs/api#tokens
			 * @param args Object containing the billing and shipping addresses, if enabled.
			 */

			function handleStripeCheckoutToken( token, args ) {

				var submitBtn = spFormElem.find( '.' + formData.submitBtnClass );

				// Append hidden inputs to hold Stripe Checkout token values to sumbit with form POST.

				$( '<input>' ).attr( {
					type: 'hidden',
					name: 'simpay_stripe_token',
					value: token.id
				} ).appendTo( spFormElem );

				$( '<input>' ).attr( {
					type: 'hidden',
					name: 'simpay_stripe_email',
					value: token.email
				} ).appendTo( spFormElem );

				// Handle extra (shipping) args.
				if ( args ) {
					simpayApp.handleStripeShippingArgs( spFormElem, args );
				}

				// Disable original form submit button and change text for UI feedback while POST-ing to Stripe.
				submitBtn
					.prop( 'disabled', true )
					.find( 'span' )
					.text( formData.loadingText );

				// Reset form submit handler to prevent an infinite loop.
				// Then finally submit the form.
				spFormElem.off( 'submit' );
				spFormElem.submit();
			}

			/** Original form submit handler */

			spFormElem.on( 'submit', function( e ) {
				e.preventDefault();
				submitCoreForm();
			} );
		},

		// Check & add extra shipping values if found.
		handleStripeShippingArgs: function( spFormElem, args ) {

			if ( args.shipping_name ) {
				spFormElem.find( '.simpay-shipping-name' ).val( args.shipping_name );
			}

			if ( args.shipping_address_country ) {
				spFormElem.find( '.simpay-shipping-country' ).val( args.shipping_address_country );
			}

			if ( args.shipping_address_zip ) {
				spFormElem.find( '.simpay-shipping-zip' ).val( args.shipping_address_zip );
			}

			if ( args.shipping_address_state ) {
				spFormElem.find( '.simpay-shipping-state' ).val( args.shipping_address_state );
			}

			if ( args.shipping_address_line1 ) {
				spFormElem.find( '.simpay-shipping-address-line1' ).val( args.shipping_address_line1 );
			}

			if ( args.shipping_address_city ) {
				spFormElem.find( '.simpay-shipping-city' ).val( args.shipping_address_city );
			}
		},

		// Set the internal final amount property value as well as the hidden form field.
		// .amount & .finalAmount prop values = 1 for $1.00 USD, 100 if a zero decimal currency.
		setCoreFinalAmount: function( spFormElem, formData ) {

			formData.finalAmount = formData.amount;

			// Fire trigger to do additional calculations in Pro.
			body.trigger( 'simpayFinalizeCoreAmount', [ spFormElem, formData ] );
		}
	};

	$( document ).ready( function( $ ) {
		simpayApp.init();
	} );

}( jQuery ) );
