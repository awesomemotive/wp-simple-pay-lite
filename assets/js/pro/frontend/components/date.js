/* global jQuery, datepicker */

/**
 * Initailize jQuery UI datepicker.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	const dateInputEl = spFormElem.find( '.simpay-date-input' );

	dateInputEl.datepicker( {
		dateFormat: formData.dateFormat,
		beforeShow: function() {    
			jQuery( '.ui-datepicker' ).css( 'font-size', 14 );
		},
	} );
};
