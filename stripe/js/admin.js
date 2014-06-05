(function ($) {
	"use strict";
	$(function () {
		
		$.fn.bootstrapSwitch.defaults.offColor = 'warning';
		$.fn.bootstrapSwitch.defaults.onText = 'Live';
		$.fn.bootstrapSwitch.defaults.offText = 'Test';

        $('input[name="sc_settings_keys[enable_live_key]"]').bootstrapSwitch();
		
		$('.license-wrap button').click(function(e) { 
			
			e.preventDefault();
			
			console.log( 'Button clicked' );
			
			var data = {
				action: 'sc_activate_license',
				license: $(this).parent().find('input[type="text"]').val(),
				item: $(this).data('sc-item')
			}
			
			$.post( ajaxurl, data, function(response) {
				console.log( 'Response from server: ', response );
			});
			
		});
	});
}(jQuery));
