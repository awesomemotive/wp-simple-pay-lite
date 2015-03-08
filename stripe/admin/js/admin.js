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
		
		// DO AJAX save?
		$('#test').click( function( e ) {
			
			console.log('clicked');
			e.preventDefault();
			
			var serialized_data = $('#default-settings').serialize();
			
			console.log( serialized_data );
			
			var data = {
					action: 'sc_button_save',
					form_data: serialized_data
				};
			
			console.log( "Ajax URL", ajaxurl );

			$.post( ajaxurl, data, function(response) {
				console.log( response );
			});
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


