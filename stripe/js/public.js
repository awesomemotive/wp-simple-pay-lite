(function ($) {
	"use strict";
	$(function () {
		
		
		// TODO
		// Pretty sure we don't need to set the handler variables as global since we usually just modify
		// the sc_script variables. As long as we set the current form and dataAttr globally I think we will be okay
		window.stripeCheckout = {
			// Functions to run
			functions:       new Array(),
			// Form Details
			currentForm:     '',
			dataAttr:        '',
			// Validation functions
			validateRules:   {}
		};
		
		/*
		 * Call on click handler when button is clicked then use amount (need to sanitize first) and pass to the 
		 * Stripe Checkout handler
		 */	
		$( 'button.sc_checkout' ).closest( 'form' ).submit( function( event ) {

			// Set our currentForm
			stripeCheckout.currentForm = $(this);

			event.preventDefault();

			// Set our dataAttr to this current form
			stripeCheckout.dataAttr = $(this).attr('data-sc-id');

			stripeCheckout.currentForm.validate({
				rules: stripeCheckout.validateRules
			});		

			if( ! stripeCheckout.currentForm.valid() ) {
				// Cancel original form submit.
				return false;
			} else {

				// Run all functions before processing the handler
				for( var i = 0; i < stripeCheckout.functions.length; i++ ) {
					stripeCheckout.functions[i]();
				}

				var handler = StripeCheckout.configure({
					key: sc_script[stripeCheckout.dataAttr].key,
					image: ( sc_script[stripeCheckout.dataAttr].image != -1 ? sc_script[stripeCheckout.dataAttr].image : '' ),
					token: function(token, args) {

						// Set the values on our hidden elements to pass when submitting the form for payment
						stripeCheckout.currentForm.find('.sc_stripeToken').val( token.id );
						stripeCheckout.currentForm.find('.sc_amount').val( sc_script[stripeCheckout.dataAttr].amount );
						stripeCheckout.currentForm.find('.sc_stripeEmail').val( token.email );

						// Add shipping fields values if the shipping information is filled
						if( ! $.isEmptyObject( args ) ) {
							stripeCheckout.currentForm.find('.sc-shipping-name').val(args.shipping_name);
							stripeCheckout.currentForm.find('.sc-shipping-country').val(args.shipping_address_country);
							stripeCheckout.currentForm.find('.sc-shipping-zip').val(args.shipping_address_zip);
							stripeCheckout.currentForm.find('.sc-shipping-state').val(args.shipping_address_state);
							stripeCheckout.currentForm.find('.sc-shipping-address').val(args.shipping_address_line1);
							stripeCheckout.currentForm.find('.sc-shipping-city').val(args.shipping_address_city);
						}

						//Unbind right before submitting so we don't get stuck in a loop
						stripeCheckout.currentForm.unbind('submit');

						stripeCheckout.currentForm.submit();
					}
				 });

				 handler.open({
					 name: ( sc_script[stripeCheckout.dataAttr].name != -1 ? sc_script[stripeCheckout.dataAttr].name : '' ),
					 description: ( sc_script[stripeCheckout.dataAttr].description != -1 ? sc_script[stripeCheckout.dataAttr].description : '' ),
					 amount: sc_script[stripeCheckout.dataAttr].amount,
					 currency: ( sc_script[stripeCheckout.dataAttr].currency != -1 ? sc_script[stripeCheckout.dataAttr].currency : 'USD' ),
					 panelLabel: ( sc_script[stripeCheckout.dataAttr].panelLabel != -1 ? sc_script[stripeCheckout.dataAttr].panelLabel : 'Pay {{amount}}' ),
					 billingAddress: ( sc_script[stripeCheckout.dataAttr].billingAddress == 'true' || sc_script[stripeCheckout.dataAttr].billingAddress == 1 ? true : false ),
					 shippingAddress: ( sc_script[stripeCheckout.dataAttr].shippingAddress == 'true' || sc_script[stripeCheckout.dataAttr].shippingAddress == 1 ? true : false ),
					 allowRememberMe: ( sc_script[stripeCheckout.dataAttr].allowRememberMe == 1 || sc_script[stripeCheckout.dataAttr].allowRememberMe == 'true' ?  true : false ),
					 email: ( sc_script[stripeCheckout.dataAttr].email != -1 ?  sc_script[stripeCheckout.dataAttr].email : '' )
				 });
			}
		});
	});
}(jQuery));
