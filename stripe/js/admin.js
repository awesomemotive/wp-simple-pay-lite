(function ($) {
	"use strict";
	$(function () {
		
		$.fn.bootstrapSwitch.defaults.offColor = 'warning';
		$.fn.bootstrapSwitch.defaults.onText = 'Live';
		$.fn.bootstrapSwitch.defaults.offText = 'Test';

        $('input[name="sc_settings_keys[enable_live_key]"]').bootstrapSwitch();
		
	});
}(jQuery));
