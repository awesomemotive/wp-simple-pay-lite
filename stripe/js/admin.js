(function ($) {
	"use strict";
	$(function () {
		
		$.fn.bootstrapSwitch.defaults.offColor = 'warning';
		$.fn.bootstrapSwitch.defaults.onText = 'Live';
		$.fn.bootstrapSwitch.defaults.offText = 'Test';

        $('input[name="sc_settings_keys[enable_live_key]"]').bootstrapSwitch();
		
		$('.license-wrap button').on( 'click', function(e) { 
			
			var button = $(this);
			
			e.preventDefault();
			
			console.log( 'Button clicked' );
			
			var data = {
				action: 'sc_activate_license',
				license: button.parent().find('input[type="text"]').val(),
				item: button.attr('data-sc-item'),
				sc_action: button.attr('data-sc-action'),
				id: button.parent().find('input[type="text"]').attr('id')
			}
			
			$.post( ajaxurl, data, function(response) {
				console.log( 'Response from server: ', response );
				
				if( response == 'valid' ) {
					button.html( 'Deactivate' );
					button.attr('data-sc-action', 'deactivate_license');
				} else if( response == 'deactivated' ) {
					button.html( 'Activate' );
					button.attr( 'data-sc-action', 'activate_license' );
				}
			});
			
		});
	});
}(jQuery));
