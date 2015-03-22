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
			
			$(this).attr( 'disabled', true );
			
			var button = $(this);
			
			var form_id = $(this).parent( 'form' ).attr('id');
			
			var serialized_data = $('#' + form_id ).serialize();
			
			var checkboxes = '';
			
			// Loop through checkboxes and save as 0 if they are unchecked. Need to do this for ease with saving settings in WP later.
			$('input[type=checkbox]').each(function() {
				if (!this.checked) {
					checkboxes += '&' + this.name + '=0';
				}
			});
			
			serialized_data += checkboxes;
			
			serialized_data = serialized_data.replace( '[', '_' );
			serialized_data = serialized_data.replace( ']', '_' );
			
			console.log( 'Serialized Data', serialized_data );
			
			var data = {
					action: 'sc_button_save',
					form_data: encodeURIComponent( serialized_data )
				};

			$.post( ajaxurl, data, function(response) {
				console.log( response );
				// Create a new element to show the save message
				var save_message = $('<div class="sc-ajax-saved">Settings have been saved!</div>');
				button.after( save_message );
				$('.sc-ajax-saved').fadeOut( 5000 );
				$('body').remove( '.sc-ajax-saved' );
				button.attr( 'disabled', false );
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


