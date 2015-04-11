/**
 * Stripe Checkout Admin JS
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

/* global jQuery, sc_script */

(function ($) {
	'use strict';

	$(function () {
		$('.sc-nav-tab').click( function() {
			var tab_id = $(this).data('tab-id');
			
			$('.tab-content').hide().removeClass('tab-content');
			$('#' + tab_id + '-settings-tab').addClass('tab-content').show();
		});
		
		// Active tab stuff
		$('.nav-tab').click( function() {
			$(this).parent().children( '.nav-tab' ).each( function(index) {
				$(this).removeClass('nav-tab-active');
			});
			
			$(this).addClass('nav-tab-active');
		});
		
	});
}(jQuery));


