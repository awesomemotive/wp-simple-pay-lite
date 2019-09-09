/* global simpayAppPro, spShared, _, $ */

/**
 * Internal dependencies.
 */
import { update as updateCustomAmount } from 'pro/frontend/components/custom-amount.js';
import { handleSource } from 'pro/frontend/stripe-elements.js';

/**
 * Setup and enable Payment Request Button if needed.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Payment form data.
 */
export function setup( e, spFormElem, formData ) {
	if ( ! formData.hasPaymentRequestButton ) {
		return;
	}

	const key = `${ formData.formInstance }-${ formData.formId }`;

	const {
		stripeParams: {
			country,
			currency
		},
		hasPaymentRequestButton: {
			i18n: {
				totalLabel,
			},
			requestPayerName,
			requestPayerEmail,
			requestShipping,
		},
		finalAmount,
	} = formData;

	// Generate initial state of button. Eventually used to generate a request.
	window.simpayAppPro.paymentRequestButtons[ key ] = spFormElem.stripeInstance.paymentRequest( {
		country,
		currency: currency.toLowerCase(),
		total: {
			label: totalLabel,
			amount: spShared.convertToCents( finalAmount ),
		},
		displayItems: getDisplayItems( spFormElem, formData ),
		requestPayerName,
		requestPayerEmail,
		requestShipping,
	} );

	const stripeElements = spFormElem.stripeInstance.elements();

	// Create the button element to render.
	const prButton = stripeElements.create( 'paymentRequestButton', {
		paymentRequest: window.simpayAppPro.paymentRequestButtons[ key ],
		style: {
			paymentRequestButton: {
				type: formData.hasPaymentRequestButton.type,
			}
		},
	} );

	// Check the availability of the Payment Request API.
	// @todo Remove anonymous function usage.
	window.simpayAppPro.paymentRequestButtons[ key ].canMakePayment().then( function( result ) {
		// Render button if possible.
		if ( result ) {
			prButton.mount( 'form[data-simpay-form-instance="' + formData.formInstance + '"] #' + formData.hasPaymentRequestButton.id + ' .simpay-payment-request-button-container__button' );

			// Ensure form is valid before continuing.
			prButton.on( 'click', function( e ) {
				window.simpayApp.setCoreFinalAmount( spFormElem, formData );

				if ( ! paymentRequestIsValid( spFormElem, formData ) ) {
					e.preventDefault();

					// Show browser validation.
					window.simpayAppPro.triggerBrowserValidation( spFormElem, formData );
				}

				// Update custom amount and recalculate, but do not attempt to update PRB.
				const isCustomAmountValid = updateCustomAmount( null, spFormElem, formData, false );

				if ( ! isCustomAmountValid ) {
					e.preventDefault();
				}
			} );
		} else {
			document.getElementById( formData.hasPaymentRequestButton.id ).remove();
		}
	} );

	/**
	 * Update shipping options for request.
	 * There are no defined shipping methods, so this is merely to satisfy the API requirements.
	 *
	 * @todo Remove anonymous function usage.
	 * @todo Populate hidden fields so the values are sent through?
	 *
	 * @param {Object} e Payment Request Button event.
	 */
	window.simpayAppPro.paymentRequestButtons[ key ].on( 'shippingaddresschange', function( e ) {
		e.updateWith( {
			status: 'success',
			shippingOptions: formData.hasPaymentRequestButton.shippingOptions,
		} );
	});

	/**
	 * Handle token once created.
	 *
	 * @param {Object} e Payment Request Button event.
	 */
	window.simpayAppPro.paymentRequestButtons[ key ].on( 'source', function( e ) {
		const $ = jQuery;

		e.complete( 'success' );
		window.simpayAppPro.disableForm( spFormElem, formData, true );

		if ( e.payerEmail && '' !== e.payerEmail ) {
			$( '<input>' ).attr( {
				type: 'hidden',
				name: 'simpay_email',
				value: e.payerEmail
			} ).appendTo( spFormElem );
		}

		if ( e.payerName && '' !== e.payerName ) {
			$( '<input>' ).attr( {
				type: 'hidden',
				name: 'simpay_name',
				value: e.payerName
			} ).appendTo( spFormElem );
		}
		
		return handleSource( e, spFormElem, formData );
	} );
};

/**
 * Update Payment Request Button when data changes.
 *
 * @todo Separate out total and item generators.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Payment form data.
 */
export function update( spFormElem, formData ) {
	if ( ! formData.hasPaymentRequestButton ) {
		return;
	}

	const key = `${ formData.formInstance }-${ formData.formId }`;

	// Enable if not previously setup.
	if ( ! window.simpayAppPro.paymentRequestButtons.hasOwnProperty( key ) ) {
		setup( null, spFormElem, formData );
	}

	window.simpayAppPro.paymentRequestButtons[ key ].update( {
		total: {
			label: formData.hasPaymentRequestButton.i18n.totalLabel,
			amount: window.spShared.convertToCents( formData.finalAmount ),
		},
		displayItems: getDisplayItems( spFormElem, formData ),
	} );
};

/**
 * Custom check to see if relevant custom fields are valid before allowing Payment Button Request.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Payment form data.
 */
export function paymentRequestIsValid( spFormElem, formData ) {
	/**
	 * Determine if a form control is a "classic" field, meaning it is needed
	 * to submit a standard payment form instead of using the Payment Request API.
	 *
	 * @param {HTMLElement} control Form control.
	 * @return {bool} If the field is classic.
	 */
	function isClassicField( control ) {
		const classicFields = [
			'simpay-customer-name-container',
			'simpay-email-container',
			'simpay-card-container',
			'simpay-address-container',
			'simpay-telephone-container',
		];

		const classList = control.classList;
		let is = false;

		classList.forEach( function( className ) {
			if ( -1 !== classicFields.indexOf( className ) ) {
				is = true;
			}
		} );

		return is;
	}

	let requiredFieldsValid = true;

	_.each( document.querySelectorAll( '.simpay-form-control' ), function( control ) {
		const classicField = isClassicField( control );

		if ( classicField ) {
			return;
		}

		const inputs = control.querySelectorAll( 'input' );

		_.each( inputs, function( input ) {
			if ( ! input.required ) {
				return;
			}

			if ( ! input.validity.valid ) {
				requiredFieldsValid = false;
			}
		} );
	} );

	return requiredFieldsValid;
};

/**
 * Generate Payment Request API displayItems from form data.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Form data.
 * @return {array}
 */
export function getDisplayItems( spFormElem, formData ) {
	const displayItems = [];
	const $ = jQuery;

	// Recalculate all values before display.
	window.simpayAppPro.updateAmounts( null, spFormElem, formData );

	// Add subscription plan to list.
	if ( formData.planAmount ) {
		let planName = $( '.simpay-multi-sub:checked' ).parent( 'label' ).text().trim();

		// Look for a select value.
		if ( '' == planName ) {
			planName = $( '.simpay-multi-sub:selected' ).text().trim();
		}

		if ( '' === planName ) {
			planName = formData.hasPaymentRequestButton.i18n.planLabel;
		}

		displayItems.push( {
			label: planName,
			amount: spShared.convertToCents( formData.planAmount ),
		} );
	}

	// Add Fees (initial, plan) to list.
	if ( formData.setupFee || formData.planSetupFee ) {
		let amount = parseInt( formData.setupFee );

		if ( parseInt( formData.planSetupFee ) ) {
			amount = amount + parseInt( formData.planSetupFee );
		}

		if ( 0 !== amount ) {
			displayItems.push( {
				label: formData.hasPaymentRequestButton.i18n.setupFeeLabel,
				amount: spShared.convertToCents( amount ),
			} );
		}
	}

	// Add tax to list.
	if ( formData.taxAmount > 0 ) {
		displayItems.push( {
			label: formData.hasPaymentRequestButton.i18n.taxLabel.replace( '%s', formData.taxPercent ),
			amount: spShared.convertToCents( formData.taxAmount ),
		} );
	}

	// Add tax to list.
	if ( formData.discount > 0 ) {
		displayItems.push( {
			label: formData.hasPaymentRequestButton.i18n.couponLabel.replace( '%s', formData.couponCode ),
			amount: spShared.convertToCents( formData.discount ) * -1,
		} );
	}

	return displayItems;
}
