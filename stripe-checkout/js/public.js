(function ($) {
	"use strict";
	$(function () {
			/*
			 * Call on click handler when button is clicked then use amount (need to sanitize first) and pass to the 
			 * Stripe Checkout handler
			 */
			$( 'button.sc_checkout' ).on( 'click', function( event ) {
				
				// Show options passed
				console.log( 'key: ', sc_script.key );
				console.log( 'image: ', sc_script.image );
				console.log( 'name: ', sc_script.name );
				console.log( 'description: ', sc_script.description );
				console.log( 'amount: ', sc_script.amount );
				console.log( 'currency: ', sc_script.currency );
				console.log( 'panelLabel: ', sc_script.panelLabel );
				console.log( 'billingAddress: ', sc_script.billingAddress );
				console.log( 'shippingAddress: ', sc_script.shippingAddress );
				console.log( 'allowRememberMe: ', sc_script.allowRememberMe );
				
				var handler = StripeCheckout.configure({
					key: sc_script.key,
					image: ( sc_script.image != -1 ? sc_script.image : '' ),
					token: function(token, args) {
					  
					  // Set the values on our hidden elements to pass when submitting the form for payment
					  $('#sc_stripeToken').val( token.id );
					  $('#sc_amount').val( sc_script.amount );
					  $('#sc_stripeEmail').val( token.email );
					  
					  
					 // console.log( 'Token Email: ', token.email );
					  
					  $('#sc_checkout_form').submit();
					}
				 });
				 
				 handler.open({
					 name: ( sc_script.name != -1 ? sc_script.name : '' ),
					 description: ( sc_script.description != -1 ? sc_script.description : '' ),
					 amount: sc_script.amount,
					 currency: ( sc_script.currency != -1 ? sc_script.currency : 'USD' ),
					 panelLabel: ( sc_script.panelLabel != -1 ? sc_script.panelLabel : 'Pay {{amount}}' ),
					 billingAddress: ( sc_script.billingAddress == 'true' || sc_script.billingAddress == 1 ? true : false ),
					 shippingAddress: ( sc_script.shippingAddress == 'true' || sc_script.shippingAddress == 1 ? true : false ),
					 allowRememberMe: ( sc_script.allowRememberMe == 1 || sc_script.allowRememberMe == 'true' ?  true : false )
				 });
				 
				 event.preventDefault();
			});
			
			function convert_amount( amount, currency ) {
	
				var zero_based = new Array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VUV', 'XAF', 'XOF', 'XPF' );


				if( $.inArray(currency, zero_based) != -1 ) {
					return amount;
				}

				return amount * 100;
			}
			
	});
}(jQuery));
