/**
 * Internal dependencies
 */
import { convertFormDataToCartData } from '@wpsimplepay/cart';
import { Cart } from '@wpsimplepay/core/frontend/payment-forms/stripe-checkout/cart';
import { onPaymentFormError, getPaymentFormType, getPaymentForms } from '@wpsimplepay/core/frontend/payment-forms';

/**
 * Bind events for Stripe Checkout.
 *
 * @param {Event} e simpayBindCoreFormEventsAndTriggers Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	const submitBtn = spFormElem.find( '.simpay-payment-btn' )[0];

	const { enableForm, disableForm } = window.simpayApp;
	const { debugLog } = window.spShared;

	// Don't continue if this form is not using Stripe Checkout.
	// We need to check here due to legacy implementation of form setups.
	if ( 'stripe-checkout' !== getPaymentFormType( spFormElem, formData ) ) {
		return;
	}

	// Disable Payment Form during setup.
	disableForm( spFormElem, formData, true );

	try {
		// Convert legacay data in to Cart data.
		const {
			items,
			currency,
			taxPercent,
			isNonDecimalCurrency,
		} = convertFormDataToCartData( formData );

		// Create a cart.
		const cart = new Cart( {
			currency,
			taxPercent,
			isNonDecimalCurrency,
		} );

		// Add items to the Cart.
		if ( items.length > 0 ) {
			items.forEach( ( item ) => {
				cart.addLineItem( item );
			} );
		}

		// Attach cart to the Payment Form instance.
		spFormElem.cart = cart;

		// Reenable form.
		enableForm( spFormElem, formData );

		// Attach submission handler.
		submitBtn.addEventListener( 'click', ( e ) => {
			e.preventDefault();

			if ( window.simpayGoogleRecaptcha ) {
				const { siteKey, i18n } = simpayGoogleRecaptcha;

				// @todo Complete syncronously inside of separate reCAPTCHA script.
				//
				// This is a temporary measure to ensure reCAPTCHA tokens are generated
				// at the time of submission to avoid them being invalidated after 120 seconds.
				grecaptcha.ready( () => {
					try {
						grecaptcha.execute( siteKey, {
							action: `simple_pay_form_${ formData.formId }_customer`,
						} )
							.then( ( token ) => {
								spFormElem.append( '<input type="hidden" name="grecaptcha_customer" value="' + token + '" />' );

								grecaptcha.execute( siteKey, {
									action: `simple_pay_form_${ formData.formId }_payment`,
								} )
									.then( ( token ) => {
										spFormElem.append( '<input type="hidden" name="grecaptcha_payment" value="' + token + '" />' );

										// Find and submit the Payment Form.
										getPaymentForms()[ getPaymentFormType( spFormElem, formData ) ]
											.submit( spFormElem, formData );
									} );
							} )
							.catch( ( error ) => {
								onPaymentFormError( i18n.error, spFormElem, formData );
							} );
					} catch ( error ) {
						onPaymentFormError( i18n.error, spFormElem, formData );
					}
				} );
			} else {
				getPaymentForms()[ getPaymentFormType( spFormElem, formData ) ]
					.submit( spFormElem, formData );
			}
		} );
	} catch ( error ) {
		onPaymentFormError( error, spFormElem, formData );
	}
}
