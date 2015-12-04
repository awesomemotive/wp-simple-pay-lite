/**
 * Admin JS file
 */

/* global jQuery, sc_script */

(function ($) {
	'use strict';

	$(function () {
		
		// Get the # of the page so we can find out what tab to show
		function get_hash() {
			if(window.location.hash) {
				return window.location.hash.substring(1);
			} else {
				return 'stripe-keys';
			}
		}
		
		// Show the tab content
		$('#' + get_hash() + '-settings-tab').addClass('tab-content').show();
		
		// Make the actual tab selected
		$('.nav-tab-wrapper').children( '.nav-tab' ).each( function(index) {
				if($(this).data('tab-id') == get_hash() ) {
					$(this).addClass('nav-tab-active');
				}
			});
		
		// Code to hide/show selected tabs
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
		
		$('#sc-settings-content form #submit').on('click', function() {
			//event.preventDefault();
			$(this).closest('form').attr('action', 'options.php#' + get_hash() );
			//$(this).closest('form').submit();
		});
		
	});
}(jQuery));
