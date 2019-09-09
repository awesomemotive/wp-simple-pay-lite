/* global jQuery */

/**
 * WordPress dependencies.
 */
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies.
 */
import { create as createCustomer } from 'core/frontend/payments/customer.js';

import {
	create as createPaymentIntent,
	handleServerResponse as handlePaymentIntentServerResponse,
} from 'pro/frontend/payments/paymentintent.js';

import {
	create as createSubscription,
	handleServerResponse as handleSubscriptionServerResponse,
} from 'pro/frontend/payments/subscription.js';

/**
 * Get Card element configuration.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {HTMLElement} cardEl Card element to mount to.
 */
function getCardConfig( spFormElem, cardEl ) {
	// If a billing address field exists (overrides Card field setting).
	const hidePostalCode = !! spFormElem[0].querySelector( '.simpay-address-zip' );

	// Base styles.
	const style = {
		base: {
			color: '#32325d',
			fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
			fontSize: '15px',
			fontSmoothing: 'antialiased',
			fontWeight: 500,

			'::placeholder': {
				color: '#aab7c4'
			}
		},
		invalid: {
			color: '#fa755a',
			iconColor: '#fa755a'
		}
	};

	return {
		hidePostalCode,
		style,
	};
};

/**
 * Find card owner data in the form.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {HTMLElement} cardEl Card element to mount to.
 */
function getCardOwnerData( spFormElem, formData ) {
	const billingAddressContainer = spFormElem.find( '.simpay-billing-address-container' );

	const name = spFormElem.find( '.simpay-customer-name' ).val() || null;
	const email = spFormElem.find( '.simpay-email' ).val() || null;
	const phone = spFormElem.find( '.simpay-telephone' ).val() || null;
	const address = 0 !== billingAddressContainer.length ? {
		line1: billingAddressContainer.find( '.simpay-address-street' ).val() || null,
		city: billingAddressContainer.find( '.simpay-address-city' ).val() || null,
		state: billingAddressContainer.find( '.simpay-address-state' ).val() || null,
		postal_code: billingAddressContainer.find( '.simpay-address-zip' ).val() || null,
		country: billingAddressContainer.find( '.simpay-address-country' ).val() || null,
	} : null;

	return {
		name,
		email,
		phone,
		address,
	}
}

/**
 * Handle Stripe Payment Source creation.
 *
 * Depending on the form type, follow one of two flows:
 *
 * 1. https://stripe.com/docs/billing/subscriptions/payment
 * 2. https://stripe.com/docs/payments/payment-intents/quickstart#manual-confirmation-flow
 *
 * @param {Object} result Stripe createSource result.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export async function handleSource( result, spFormElem, formData ) {
	if ( result.error ) {
		throw result.error;
	}

	// Customer is required for both flows.
	const customer = await createCustomer(
		{
			source_id: result.source.id,
		},
		spFormElem,
		formData
	);

	const successUrl = addQueryArgs( formData.stripeParams.success_url, {
		customer_id: customer.id,
	} );

	let paymentIntentRequiresAction;

	if ( formData.isSubscription || formData.isRecurring ) {
		const subscription = await createSubscription(
			{
				customer_id: customer.id,
			},
			spFormElem,
			formData
		);

		// Handle next actions on Subscription's PaymentIntent.
		paymentIntentRequiresAction = await handleSubscriptionServerResponse( subscription, spFormElem, formData );
	} else {
		const paymentIntent = await createPaymentIntent(
			{
				customer_id: customer.id,
				payment_method_id: result.source.id
			},
			spFormElem,
			formData
		);

		// No SCA needed, redirect.
		if ( ! paymentIntent.requires_action ) {
			return window.location.href = successUrl;
		}

		// Handle next actions on PaymentIntent.
		paymentIntentRequiresAction = await handlePaymentIntentServerResponse(
			{
				customer_id: customer.id,
				payment_intent: paymentIntent,
			},
			spFormElem,
			formData
		);
	}

	// Nothing else is needed, redirect.
	if ( false === paymentIntentRequiresAction ) {
		return window.location.href = successUrl;
	}
}

/**
 * Submit payment form.
 *
 * @param {Event} e Form submit Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function submitForm( e, spFormElem, formData ) {
	e.preventDefault();

	// Remove existing errors.
	window.simpayApp.showError( spFormElem, formData, '' );

	// Disable form while processing.
	window.simpayApp.disableForm( spFormElem, formData, true );

	// HTML5 validation check.
	if ( ! spFormElem[0].checkValidity() ) {
		window.simpayApp.triggerBrowserValidation( spFormElem, formData );
		window.simpayApp.enableForm( spFormElem, formData );

		return;
	}

	// Allow further validation.
	//
	// jQuery( document.body ).on( 'simpayBeforeStripePayment', function( e, spFormElem, formData ) {
	//  formData.isValid = false;
	// } );
	spFormElem.trigger( 'simpayBeforeStripePayment', [ spFormElem, formData ] );

	if ( ! formData.isValid ) {
		window.simpayApp.enableForm( spFormElem, formData );

		return;
	}

	// Collect card details and create a PaymentMethod.
	spFormElem.stripeInstance
		.createSource( spFormElem.cardElementInstance, {
			type: 'card',
			currency: formData.currency,
			owner: getCardOwnerData( spFormElem, formData ),
		} )
		.then( ( result ) => handleSource( result, spFormElem, formData ) )
		.catch( ( error ) => {
			if ( _.isObject( error ) ) {
				const { responseJSON, responseText, message } = error;
				const errorMessage = message ? message : ( responseJSON && responseJSON.message ? responseJSON.message : responseText );

				window.simpayApp.showError( spFormElem, formData, errorMessage );
			}

			window.spShared.debugLog( 'Payment Form Error:', error );
			window.simpayApp.enableForm( spFormElem, formData );
		} );
}

/**
 * Bind events for Stripe Elements.
 *
 * @param {Event} e simpayBindCoreFormEventsAndTriggers Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	const elements = spFormElem.stripeInstance.elements();
	const cardEl = spFormElem[0].querySelector( '.simpay-card-wrap' );

	if ( ! cardEl ) {
		return;
	}

	spFormElem.cardElementInstance = elements.create( 'card', getCardConfig( spFormElem, cardEl ) );

	window.simpayAppPro.disableForm( spFormElem, formData, true );

	// Cache form elements.
	var realFormElem = spFormElem[0];
	var submitBtn = realFormElem.querySelector( '.simpay-checkout-btn' );

	if ( ! submitBtn ) {
		return;
	}

	// Mount and setup Element card instance.
	spFormElem.cardElementInstance.mount( cardEl );

	// Enable form when Card field is ready.
	spFormElem.cardElementInstance.on( 'ready', () => window.simpayApp.enableForm( spFormElem, formData ) );

	// Live feedback when card updates.
	spFormElem.cardElementInstance.on( 'change', ( result ) => {
		if ( result.error ) {
			return window.simpayApp.showError( spFormElem, formData, result.error.message );
		}

		return window.simpayApp.showError( spFormElem, formData, '' );
	} );

	// Handle form submission.
	realFormElem.addEventListener( 'submit', ( e ) => submitForm( e, spFormElem, formData ) );
}
