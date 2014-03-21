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

		/*
		$('button.stripe-button-el').on('click', function() {
			$(this).append('testing...');
		});
		*/

		// TODO UI feedback on submit of parent form of stripe button script.
		// Not working. Form not submitting normally?
		$('.stripe-button').closest('form').on('submit', function() {
			$(this).after('testing...');
		});

	});
}(jQuery));
