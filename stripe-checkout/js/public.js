(function ($) {
	"use strict";
	$(function () {
		/*
		var html = '<div class="sc-payment-status">';
		
		if( getQueryArg('payment') == 'success' ) {
			
			var amount = getQueryArg('amount');
			
			amount = new Number( amount / 100 ).toFixed(2);
			
			html += '<p class="sc-success">Your payment of $' + amount + ' was successfully submitted.</p>';
		} else {
			html += '<p class="sc-failed">There was an error submitting your payment.</p>';
		}
		
		html += '</div>';
		
		$('body').append(html);

		//TODO $('.sc-payment-status').delay( 10000 ).fadeOut( 1500 );
		
		function getQueryArg(variable)
		{
			   var query = window.location.search.substring(1);
			   var vars = query.split("&");
			   for (var i=0;i<vars.length;i++) {
					   var pair = vars[i].split("=");
					   if(pair[0] == variable){return pair[1];}
			   }
			   return(false);
		}
		*/

		// TODO UI feedback on submit of parent form of stripe button script.
		/*
		console.log($('.stripe-button').closest('form'));

		// Not working. Form not submitting normally?
		//$('.stripe-button').closest('form').on('submit', function() {
		$('.stripe-button').closest('form').submit(function() {
			$(this).after('testing...');
		});
		*/
		/*
		$('button.stripe-button-el').on('click', function() {
			$(this).append('testing...');
		});
		*/
		/*
		$('.stripe_checkout_app').find('form.checkoutView').on('submit', function() {
			console.log('submitting...');
		});
		*/
		/*
		$('iframe.stripe_checkout_app').find('iframe').find('form.checkoutView').on('submit', function() {
			console.log('submitting...');
		});
		*/

	});
}(jQuery));
