/* global simplePayForms, spGeneral, jQuery */

var simpayApp = {};

(function( $ ) {
	'use strict';

	var body;

	simpayApp = {

		// Collection of DOM elements of all payment forms
		spFormElList: {},

		// Internal organized collection of all form data
		spFormData: {},

		// Stripe Data?
		spStripeData: {},

		init: function() {

			// Set main vars on init.
			body = $( document.body );

			this.spFormElList = body.find( '.simpay-checkout-form' );

			this.spFormElList.each( function() {

				var spFormElem = $( this );
				simpayApp.processForm( spFormElem );

				body.trigger( 'simpayProcessFormElements', [ spFormElem ] );
			} );

			body.trigger( 'simpayLoaded' );
		},

		processForm: function( spFormElem ) {

			// Set the form ID
			var formId = spFormElem.data( 'simpay-form-id' );

			// Grab the localized data for this form ID
			var localizedFormData = simplePayForms[ formId ];

			// Set a local variable to hold all of the form information.
			var formData = this.spFormData[ formId ];

			// Variable to hold the Stripe configuration
			var stripeHandler = null;

			// Set formData array index of the current form ID to match the localized data passed over for form settings.
			formData = $.extend( {},  localizedFormData.form.integers, localizedFormData.form.bools, localizedFormData.form.strings );

			formData.formId = formId;

			// Set a finalAmount setting so that we can perform all the actions on this. That way if we need to reverse anything we leave the base amount untouched and can revert to it.
			formData.finalAmount = formData.amount;

			// Set the default quantity to 1
			formData.quantity = 1;

			// Add a new object called stripeParams to the spFormData object. This contains only the stripeParams that need to be sent. This is so we don't have to manually set all the stripeParams
			// And we can just use what was passed from PHP so we only include the minimum needed and let Stripe defaults take care of anything that's missing here.
			formData.stripeParams = $.extend( {}, localizedFormData.stripe.strings, localizedFormData.stripe.bools );

			// Set a fallback button label
			formData.oldPanelLabel = undefined !== formData.stripeParams.panelLabel ? formData.stripeParams.panelLabel : '';

			body.trigger( 'simpayFormVarsInitialized', [ spFormElem, formData ] );

			// Stripe Checkout handler configuration.
			// Only token callback function set here. All other params set in stripeParams.
			// Chrome on iOS needs handler set before click event or else checkout won't open in a new tab.
			// See "How do I prevent the Checkout popup from being blocked?"
			// Full docs: https://stripe.com/docs/checkout#integration-custom
			stripeHandler = StripeCheckout.configure( {

				// Key param MUST be sent here instead of stripeHandler.open(). Discovered 8/11/16.
				key: formData.stripeParams.key,

				token: handleStripeToken,

				opened: function() {
				},
				closed: function() {
				}
			} );

			// Internal Strike token callback function for StripeCheckout.configure
			function handleStripeToken( token, args ) {

				// At this point the Stripe Checkout overlay is validated and submitted.
				// Set values to hidden elements to pass via POST when submitting the form for payment.
				spFormElem.find( '.simpay-stripe-token' ).val( token.id );
				spFormElem.find( '.simpay-stripe-email' ).val( token.email );

				// Handle args
				simpayApp.handleStripeArgs( spFormElem, args );

				// Disable original payment button and change text for UI feedback while POST-ing to Stripe.
				spFormElem.find( '.simpay-payment-btn' )
					.prop( 'disabled', true )
					.find( 'span' )
					.text( formData.loadingText );

				// Unbind original form submit trigger before calling again to "reset" it and submit normally.
				spFormElem.unbind( 'submit', [ spFormElem, formData ] );

				spFormElem.submit();
			}

			// Page-level initial payment button clicked. Use over form submit for more control/validation.
			spFormElem.find( '.simpay-payment-btn' ).on( 'click.simpayPaymentBtn', function( e ) {
				e.preventDefault();

				// Trigger custom event right before executing payment
				spFormElem.trigger( 'simpayBeforeStripePayment', [ spFormElem, formData ] );

				simpayApp.submitPayment( spFormElem, formData, stripeHandler );
			} );

			this.spFormData[ formId ] = formData;

			/** Event handlers for form elements **/

			body.trigger( 'simpayBindEventsAndTriggers', [ spFormElem, formData ] );
		},

		handleStripeArgs: function( spFormElem, args ) {

			// Check and add only the ones that are found

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

		submitPayment: function( spFormElem, formData, stripeHandler ) {

			simpayApp.setFinalAmount( spFormElem, formData );

			// Add in the final amount to Stripe params
			formData.stripeParams.amount = parseInt( formData.finalAmount );

			// If everything checks out then let's open the form
			if ( spFormElem.valid() ) {

				body.trigger( 'simpaySubmitPayment', [ spFormElem, formData ] );

				spShared.debugLog( 'stripeParams', formData.stripeParams );

				stripeHandler.open( formData.stripeParams );
			}
		},

		// Run this to process and get the final amount when the payment button is clicked
		setFinalAmount: function( spFormElem, formData ) {

			var finalAmount = formData.amount;

			formData.finalAmount = finalAmount.toFixed( 0 );

			body.trigger( 'simpayFinalizeAmount', [ spFormElem, formData ] );

			// Update hidden amount field for processing
			spFormElem.find( '.simpay-amount' ).val( formData.finalAmount );

		},

		formatMoney: function( amount ) {

			// Default format is to the left with no space
			var format = '%s%v';
			var options;

			// Convert our amount from cents to a formatted amount
			amount = simpayApp.convertFromCents( amount );

			// Set currency position based on settings
			if ( 'left_space' === spGeneral.strings.currencyPosition ) {

				//1 Left with a space
				format = '%s %v';
			} else if ( 'right' === spGeneral.strings.currencyPosition ) {

				// Right side no space
				format = '%v%s';
			} else if ( 'right_space' === spGeneral.strings.currencyPosition ) {

				// Right side with space
				format = '%v %s';
			}

			options = {
				symbol: spGeneral.strings.currencySymbol,
				decimal: spGeneral.strings.decimalSeparator,
				thousand: spGeneral.strings.thousandSeparator,
				precision: spGeneral.integers.decimalPlaces,
				format: format
			};

			return accounting.formatMoney( amount, options );
		},

		convertFromCents: function( amount ) {

			if ( spGeneral.booleans.isZeroDecimal ) {
				return amount;
			} else {
				return ( amount / 100 ).toFixed( 2 );
			}
		},

		convertToCents: function( amount ) {

			if ( spGeneral.booleans.isZeroDecimal ) {
				return amount;
			} else {
				return ( amount * 100 ).toFixed( 2 );
			}
		}
	};

	$( document ).ready( function( $ ) {

		simpayApp.init();
	} );

}( jQuery ) );
