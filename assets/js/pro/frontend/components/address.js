/**
 * Toggle shipping address fields.
 *
 * When hiding, disable fields so the values are not sent.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function toggleShippingAddressFields( spFormElem, formData ) {
	const shippingAddressContainer = spFormElem.find( '.simpay-shipping-address-container' );
	const isChecked = spFormElem.find( '.simpay-same-address-toggle' ).is( ':checked' );

	shippingAddressContainer
		.toggle()
		.find( 'input, select' )
		.prop( 'disabled', isChecked );
};
