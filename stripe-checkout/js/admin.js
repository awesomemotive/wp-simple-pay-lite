(function ($) {
	"use strict";
	$(function () {
		
		$.fn.bootstrapSwitch.defaults.offColor = 'warning';
		$.fn.bootstrapSwitch.defaults.onText = 'Live';
		$.fn.bootstrapSwitch.defaults.offText = 'Test';
		
		$("[name*='enable_live_key']").bootstrapSwitch();
		
		
	});
}(jQuery));


