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
		$('button.ajax_save').click( function( e ) {
			
			e.preventDefault();
			
			var form_id = $(this).parent( 'form' ).attr('id');
			console.log( 'Form ID', form_id);
			
			var serialized_data = $('#' + form_id ).serialize();
			
			var data = {
					action: 'sc_button_save',
					form_data: serialized_data
				};
			
			console.log( 'Serialized Data', serialized_data );
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


