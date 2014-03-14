(function ($) {
	"use strict";
	$(function () {
		
		var html = '<div class="sc-payment-status">';
		
		if( getQueryArg('payment') == 'success' ) {
			
			var amount = getQueryArg('amount');
			
			amount = new Number( amount / 100 ).toFixed(2);
			
			html += '<p class="sc-success">$' + amount + ' was successfully paid.</p>';
		} else {
			html += '<p class="sc-failed">There was an error with your payment. Please try again.</p>';
		}
		
		html += '</div>';
		
		$('body').append(html);
		
		
		$('.sc-payment-status').delay( 3000).fadeOut( 1500 );
		
		
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
	});
}(jQuery));