(function ($) {
	"use strict";
	$(function () {
			/*
			 * Call on click handler when button is clicked then use amount (need to sanitize first) and pass to the 
			 * Stripe Checkout handler
			 */	
			$( 'button.sc_checkout' ).closest( 'form' ).submit( function( event ) {
		
				// Cancel original form submit.
				//console.log( 'cancelling form submit' );
				//event.preventDefault();
				//return false;

				// To proceed with form submit.
				var currentForm = $( this );
				console.log( 'form to submit', currentForm );
				
				
				console.log( 'Button Clicked - called from SC');
				
				event.preventDefault();
				
				var dataAttr = $(this).attr( 'data-sc-id' );
				
				console.log( 'Data: ', dataAttr );
				
				// Show options passed
				console.log( 'key: ', sc_script[dataAttr].key );
				console.log( 'image: ', sc_script[dataAttr].image );
				console.log( 'name: ', sc_script[dataAttr].name );
				console.log( 'description: ', sc_script[dataAttr].description );
				console.log( 'amount: ', sc_script[dataAttr].amount );
				console.log( 'currency: ', sc_script[dataAttr].currency );
				console.log( 'panelLabel: ', sc_script[dataAttr].panelLabel );
				console.log( 'billingAddress: ', sc_script[dataAttr].billingAddress );
				console.log( 'shippingAddress: ', sc_script[dataAttr].shippingAddress );
				console.log( 'allowRememberMe: ', sc_script[dataAttr].allowRememberMe );
				console.log( 'email', sc_script[dataAttr].email );
				
				var handler = StripeCheckout.configure({
					key: sc_script[dataAttr].key,
					image: ( sc_script[dataAttr].image != -1 ? sc_script[dataAttr].image : '' ),
					token: function(token, args) {
					  
					  // Set the values on our hidden elements to pass when submitting the form for payment
					 /* $(currentForm + ' #sc_stripeToken').val( token.id );
					  $(currentForm + ' #sc_amount').val( sc_script[dataAttr].amount );
					  $(currentForm + ' #sc_stripeEmail').val( token.email );*/
						
						currentForm.find('#sc_stripeToken').val( token.id );
						currentForm.find('#sc_amount').val( sc_script[dataAttr].amount );
						currentForm.find('#sc_stripeEmail').val( token.email );

						//console.log( args );
						//console.log( $.isEmptyObject( args ) );
					
					
						// Add shipping fields values if the shipping information is filled
						if( ! $.isEmptyObject( args ) ) {
							currentForm.find('#sc-shipping-name').val(args.shipping_name);
							currentForm.find('#sc-shipping-country').val(args.shipping_address_country);
							currentForm.find('#sc-shipping-zip').val(args.shipping_address_zip);
							currentForm.find('#sc-shipping-state').val(args.shipping_address_state);
							currentForm.find('#sc-shipping-address').val(args.shipping_address_line1);
							currentForm.find('#sc-shipping-city').val(args.shipping_address_city);
						}
					
					 
						currentForm.unbind('submit');

						currentForm.submit();
					}
				 });
				 
				 handler.open({
					 name: ( sc_script[dataAttr].name != -1 ? sc_script[dataAttr].name : '' ),
					 description: ( sc_script[dataAttr].description != -1 ? sc_script[dataAttr].description : '' ),
					 amount: sc_script[dataAttr].amount,
					 currency: ( sc_script[dataAttr].currency != -1 ? sc_script[dataAttr].currency : 'USD' ),
					 panelLabel: ( sc_script[dataAttr].panelLabel != -1 ? sc_script[dataAttr].panelLabel : 'Pay {{amount}}' ),
					 billingAddress: ( sc_script[dataAttr].billingAddress == 'true' || sc_script[dataAttr].billingAddress == 1 ? true : false ),
					 shippingAddress: ( sc_script[dataAttr].shippingAddress == 'true' || sc_script[dataAttr].shippingAddress == 1 ? true : false ),
					 allowRememberMe: ( sc_script[dataAttr].allowRememberMe == 1 || sc_script[dataAttr].allowRememberMe == 'true' ?  true : false ),
					 email: ( sc_script[dataAttr].email != -1 ?  sc_script[dataAttr].email : '' )
				 });
				 
				 event.preventDefault();

			});
				
			/*	console.log( 'Button Clicked - called from SC');
				
				event.preventDefault();
				
				var dataAttr = $(this).attr( 'data-sc-id' );
				
				console.log( 'Data: ', dataAttr );
				
				// Show options passed
				console.log( 'key: ', sc_script[dataAttr].key );
				console.log( 'image: ', sc_script[dataAttr].image );
				console.log( 'name: ', sc_script[dataAttr].name );
				console.log( 'description: ', sc_script[dataAttr].description );
				console.log( 'amount: ', sc_script[dataAttr].amount );
				console.log( 'currency: ', sc_script[dataAttr].currency );
				console.log( 'panelLabel: ', sc_script[dataAttr].panelLabel );
				console.log( 'billingAddress: ', sc_script[dataAttr].billingAddress );
				console.log( 'shippingAddress: ', sc_script[dataAttr].shippingAddress );
				console.log( 'allowRememberMe: ', sc_script[dataAttr].allowRememberMe );
				console.log( 'email', sc_script[dataAttr].email );
				
				var handler = StripeCheckout.configure({
					key: sc_script[dataAttr].key,
					image: ( sc_script[dataAttr].image != -1 ? sc_script[dataAttr].image : '' ),
					token: function(token, args) {
					  
					  // Set the values on our hidden elements to pass when submitting the form for payment
					  $('#sc_checkout_form_' + dataAttr + ' #sc_stripeToken').val( token.id );
					  $('#sc_checkout_form_' + dataAttr + ' #sc_amount').val( sc_script[dataAttr].amount );
					  $('#sc_checkout_form_' + dataAttr + ' #sc_stripeEmail').val( token.email );
					  
					  
					 // console.log( 'Token Email: ', token.email );
					  
					  $('#sc_checkout_form_' + dataAttr).submit();
					}
				 });
				 
				 handler.open({
					 name: ( sc_script[dataAttr].name != -1 ? sc_script[dataAttr].name : '' ),
					 description: ( sc_script[dataAttr].description != -1 ? sc_script[dataAttr].description : '' ),
					 amount: sc_script[dataAttr].amount,
					 currency: ( sc_script[dataAttr].currency != -1 ? sc_script[dataAttr].currency : 'USD' ),
					 panelLabel: ( sc_script[dataAttr].panelLabel != -1 ? sc_script[dataAttr].panelLabel : 'Pay {{amount}}' ),
					 billingAddress: ( sc_script[dataAttr].billingAddress == 'true' || sc_script[dataAttr].billingAddress == 1 ? true : false ),
					 shippingAddress: ( sc_script[dataAttr].shippingAddress == 'true' || sc_script[dataAttr].shippingAddress == 1 ? true : false ),
					 allowRememberMe: ( sc_script[dataAttr].allowRememberMe == 1 || sc_script[dataAttr].allowRememberMe == 'true' ?  true : false ),
					 email: ( sc_script[dataAttr].email != -1 ?  sc_script[dataAttr].email : '' )
				 });
				 
				 event.preventDefault();
			});*/
			
			function convert_amount( amount, currency ) {
	
				var zero_based = new Array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VUV', 'XAF', 'XOF', 'XPF' );


				if( $.inArray(currency, zero_based) != -1 ) {
					return amount;
				}

				return amount * 100;
			}
				
	});
}(jQuery));
