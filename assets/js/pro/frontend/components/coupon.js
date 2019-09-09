/* global spShared, spGeneral, jQuery */

/**
 * Internal dependencies.
 */
import { update as updateTotalAmountLabels } from 'pro/frontend/components/total-amount-labels.js';

/**
 * Apply a coupon.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function apply( spFormElem, formData ) {
	const $ = jQuery;
	const couponField = spFormElem.find( '.simpay-coupon-field' );
	const responseContainer = spFormElem.find( '.simpay-coupon-message' );
	const loadingImage = spFormElem.find( '.simpay-coupon-loading' );
	const removeCoupon = spFormElem.find( '.simpay-remove-coupon' );
	const hiddenCouponElem = spFormElem.find( '.simpay-coupon' );

	let couponCode = '';
	let couponMessage = '';
	let amount = formData.amount;
	let setupFee = 0;

	if ( ! couponField.val() && ! formData.couponCode ) {
		return;
	} else if ( formData.couponCode ) {
		couponCode = formData.couponCode;
	} else {
		couponCode = couponField.val();
	}

	if ( 'undefined' !== typeof formData.quantity ) {
		amount = amount * formData.quantity;
	}

	if ( formData.isSubscription ) {
		let fees = 0;

		if ( 'undefined' !== typeof formData.setupFee ) {
			fees += formData.setupFee;
		}

		if ( 'undefined' !== typeof formData.planSetupFee ) {
			fees += window.spShared.unformatCurrency( formData.planSetupFee );
		}

		if ( formData.useCustomPlan ) {
			amount = formData.customPlanAmount + fees;
		} else {
			amount = formData.planAmount + fees;
		}
	} else {
		if ( ( 'undefined' !== formData.customAmount ) && ( formData.customAmount > 0 ) ) {
			amount = formData.customAmount;
		}
	}

	// AJAX params
	const data = {
		action: 'simpay_get_coupon',
		coupon: couponCode,
		amount: amount,
		couponNonce: spFormElem.find( '#simpay_coupon_nonce' ).val()
	};

	// Clear the response container and hide the remove coupon link
	responseContainer.text( '' );
	removeCoupon.hide();

	// Clear textbox
	couponField.val( '' );

	// Show the loading image
	loadingImage.show();

	$.ajax( {
		url: window.spGeneral.strings.ajaxurl,
		method: 'POST',
		data: data,
		dataType: 'json',
		success: function( response ) {
			if ( response.success ) {
				// Set the coupon code attached to this form to the couponCode being used here
				formData.couponCode = couponCode;

				// Set an attribute to store the discount so we can subtract it later
				formData.discount = response.discount;

				// Coupon message for frontend
				couponMessage = response.coupon.code + ': ';

				// Output different text based on the type of coupon it is - amount off or a percentage
				if ( 'percent' === response.coupon.type ) {
					couponMessage += response.coupon.amountOff + spGeneral.i18n.couponPercentOffText;
				} else if ( 'amount' === response.coupon.type ) {
					couponMessage += window.spShared.formatCurrency( response.coupon.amountOff, true ) + ' ' + spGeneral.i18n.couponAmountOffText;
				}

				$( '.coupon-details' ).remove();

				// Update the coupon message text
				responseContainer.append( couponMessage );

				// Create a hidden input to send our coupon details for Stripe metadata purposes
				$( '<input />', {
					name: 'simpay_coupon_details',
					type: 'hidden',
					value: couponMessage,
					class: 'simpay-coupon-details'
				} ).appendTo( responseContainer );

				// Show remove coupon link
				removeCoupon.show();

				// Add the coupon to our hidden element for processing
				hiddenCouponElem.val( couponCode );

				// Hide the loading image
				loadingImage.hide();

				// Trigger custom event when coupon apply done.
				spFormElem.trigger( 'simpayCouponApplied' );
			} else {
				// Show invalid coupon message
				responseContainer.append( $( '<p />' ).addClass( 'simpay-field-error' ).text( response.data.error ) );

				// Hide loading image
				loadingImage.hide();
			}
		},
		error: function( response ) {
			var errorMessage = '';

			window.spShared.debugLog( 'Coupon error', response.responseText );

			if ( response.responseText ) {
				errorMessage = response.responseText;
			}

			// Show invalid coupon message
			responseContainer.append( $( '<p />' ).addClass( 'simpay-field-error' ).text( errorMessage ) );

			// Hide loading image
			loadingImage.hide();
		},
		complete: function( response ) {
			// Alert the rest of the components they need to update.
			// `false` tells the total event to not reapply the coupon.
			spFormElem.trigger( 'totalChanged', [ spFormElem, formData, false ] );
		}
	} );
};

/**
 * Remove a coupon.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function remove( spFormElem, formData ) {
	spFormElem.find( '.simpay-coupon-loading' ).hide();
	spFormElem.find( '.simpay-remove-coupon' ).hide();
	spFormElem.find( '.simpay-coupon-message' ).text( '' );
	spFormElem.find( '.simpay-coupon' ).val( '' );

	formData.couponCode = '';
	formData.discount = 0;

	// Trigger custom event when coupon apply done.
	spFormElem.trigger( 'simpayCouponRemoved' );

	// Alert the rest of the components they need to update.
	spFormElem.trigger( 'totalChanged', [ spFormElem, formData ] );
};
