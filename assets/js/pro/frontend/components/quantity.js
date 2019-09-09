/**
 * Internal dependencies.
 */
import { apply as applyCoupon } from 'pro/frontend/components/coupon.js';
import { update as updateTotalAmountLabels } from 'pro/frontend/components/total-amount-labels.js';

export function update( e, spFormElem, formData ) {
	formData.quantity = 1;

	if ( spFormElem.find( '.simpay-quantity-dropdown' ).length ) {
		formData.quantity = parseFloat( spFormElem.find( '.simpay-quantity-dropdown' ).find( 'option:selected' ).data( 'quantity' ) );

		spFormElem.trigger( 'simpayDropdownQuantityChange' );
	} else if ( spFormElem.find( '.simpay-quantity-radio' ).length ) {
		formData.quantity = parseFloat( spFormElem.find( '.simpay-quantity-radio' ).find( 'input[type="radio"]:checked' ).data( 'quantity' ) );

		spFormElem.trigger( 'simpayRadioQuantityChange' );
	} else if ( spFormElem.find( '.simpay-quantity-input' ).length ) {
		formData.quantity = parseFloat( spFormElem.find( '.simpay-quantity-input' ).val() );

		spFormElem.trigger( 'simpayNumberQuantityChange' );
	}

	if ( formData.quantity < 1 ) {
		formData.quantity = 1;
	}

	// Update hidden quantity field.
	spFormElem.find( '.simpay-quantity' ).val( formData.quantity );

	// Alert the rest of the components they need to update.
	spFormElem.trigger( 'totalChanged', [ spFormElem, formData ] );
}
