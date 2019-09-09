/* global simplePayForms, spGeneral, jQuery, Stripe */

/**
 * Internal dependencies.
 */
import { default as simpayAppProCompat } from './pro/frontend/compat.js';

import { setup as setupStripeElements } from './pro/frontend/stripe-elements.js';
import { setup as setupOverlayModals } from './pro/frontend/overlays.js';
import { setup as setupDateField } from './pro/frontend/components/date.js';

import { update as updateTotalAmountLabels } from './pro/frontend/components/total-amount-labels.js';
import { update as updateMultiSubSelection } from './pro/frontend/components/multi-sub.js';
import { update as updateQuantityField } from './pro/frontend/components/quantity.js';
import { update as updateAmountField } from './pro/frontend/components/amount.js';

import { toggleShippingAddressFields } from './pro/frontend/components/address.js';

import {
	setup as setupPaymentRequestButtons,
	update as updatePaymentRequestButtons,
} from './pro/frontend/components/payment-request-button.js';

import {
	update as updateCustomAmount,
	enableCustomPlanAmount,
} from './pro/frontend/components/custom-amount.js';

import {
	apply as applyCoupon,
	remove as removeCoupon,
} from './pro/frontend/components/coupon.js';

let simpayAppPro = {};

( function( $ ) {
	'use strict';

	const body = $( document.body );

	/**
	 * Manage additional "Pro" functionality.
	 *
	 * This object mainly serves as a backwards compatibility shim.
	 */
	simpayAppPro = {
		// Manage multiple payment request buttons.
		paymentRequestButtons: {},

		/**
		 * Setup Payment Forms.
		 */
		init: function() {
			// Let `bindEvents` access other object property functions via `this`.
			this.bindEvents = this.bindEvents.bind( this );

			// Bind interactions.
			body.on( 'simpayBindCoreFormEventsAndTriggers', this.bindEvents );

			body.on( 'simpayBindCoreFormEventsAndTriggers', setupStripeElements );
			body.on( 'simpayBindCoreFormEventsAndTriggers', setupOverlayModals );
			body.on( 'simpayBindCoreFormEventsAndTriggers', setupDateField );

			body.on( 'simpayBindCoreFormEventsAndTriggers', updateCustomAmount );
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateQuantityField );
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateAmountField );
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateMultiSubSelection );

			//
			// This is a very important binding, as it eventually comes full circle calling
			// the `simpayFinalizeCoreAmount` trigger, which updates the final amount.
			//
			// This updateTotalAmountLabels also includes the submit button label.
			//
			// 1. updateTotalAmountLabels
			// 2. simpayApp.setCoreFinalAmount
			//      trigger:simpayFinalizeCoreAmount
			// 3. this.updateAmounts
			//
			// To alert of a form value change, `totalChanged` trigger should be fired.
			// This will call `updateTotalAmountLabels` and start the steps above again.
			//
			// The current circular logic remains for backwards compatibility.
			//
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateTotalAmountLabels );
			body.on( 'simpayFinalizeCoreAmount', this.updateAmounts );

			// Setup PRB after totals have been adjusted.
			body.on( 'simpayBindCoreFormEventsAndTriggers', setupPaymentRequestButtons );
		},

		bindEvents: function( e, spFormElem, formData ) {
			// Toggle focus class for easier styling with CSS.
			this.setOnFieldFocus( spFormElem );

			// Update any components that need to use new total values after change.
			// `applyCouponAgain` is a hacky way to prevent an infinite loop when applying
			// the original coupon amount.
			spFormElem.on( 'totalChanged', ( e, spFormElem, formData, applyCouponAgain ) => {
				if ( applyCouponAgain ) {
					applyCoupon( spFormElem, formData );
				}

				updateTotalAmountLabels( e, spFormElem, formData );
				updatePaymentRequestButtons( spFormElem, formData );
			} );

			/**
			 * Validate custom field amount before a form is submitted.
			 *
			 * @param {Event} e Event.
			 * @param {jQuery} spFormElem Form element jQuery object.
			 * @param {Object} formData Configured form data.
			 */
			spFormElem.on( 'simpayBeforeStripePayment', ( e, spFormElem, formData ) => {
				// Backwards compatibility.
				// `simpayBeforeStripePayment` should be used directly.
				spFormElem.trigger( 'simpayFormValidationInitialized' );

				const isCustomAmountValid = updateCustomAmount( e, spFormElem, formData );

				formData.isValid = isCustomAmountValid;
			} );

			/**
			 * Validate and update amounts when the "Custom Amount" field loses focus.
			 *
			 * @param {Event} e Focusout event.
			 */
			spFormElem.find( '.simpay-custom-amount-input' ).on( 'focusout', ( e ) => updateCustomAmount( e, spFormElem, formData ) );

			/**
			 * Toggle the internal flags that a custom amount is being used for Subscriptions.
			 *
			 * @param {Event} e Focusin event.
			 */
			spFormElem.find( '.simpay-custom-amount-input' ).on( 'focusin', ( e ) => enableCustomPlanAmount( e, spFormElem, formData ) );

			/**
			 * Apply a coupon when the "Apply" button is clicked.
			 *
			 * @param {Event} e Click event.
			 */
			spFormElem.find( '.simpay-apply-coupon' ).on( 'click', ( e ) => {
				e.preventDefault();

				return applyCoupon( spFormElem, formData );
			} );

			/**
			 * Apply a coupon when the "Enter" key is pressed while focusing on the input field.
			 *
			 * @param {Event} e Click event.
			 */
			spFormElem.find( '.simpay-coupon-field' ).on( 'keypress', ( e ) => {
				if ( 13 !== e.which ) {
					return;
				}

				e.preventDefault();

				return applyCoupon( spFormElem, formData );
			} );

			/**
			 * Remove a coupon when the "Remove" button is clicked.
			 *
			 * @param {Event} e Click event.
			 */
			spFormElem.find( '.simpay-remove-coupon' ).on( 'click', ( e ) => {
				e.preventDefault();

				return removeCoupon( spFormElem, formData );
			} );

			/**
			 * Update amounts when a multi-plan subscription form updates.
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( '.simpay-multi-sub, .simpay-plan-wrapper select' ).on( 'change', ( e ) => updateMultiSubSelection( e, spFormElem, formData ) );

			/**
			 * Update amounts when a "Quantity" input changes.
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( '.simpay-quantity-input, .simpay-quantity-dropdown' ).on( 'change', ( e ) => updateQuantityField( e, spFormElem, formData ) );

			/**
			 * Update amounts when an "Amount" input changes.
			 *
			 * @param {Event} e Change
			 */
			spFormElem.find( '.simpay-amount-dropdown, .simpay-amount-radio' ).on( 'change', ( e ) => updateAmountField( e, spFormElem, formData ) );
			/**
			 * Toggle shipping fields when "Same billing & shipping info" is toggled.
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( '.simpay-same-address-toggle' ).on( 'change', ( e ) => toggleShippingAddressFields( spFormElem, formData ) );

			/**
			 * Toggle a recurring charge (generates a Subscription).
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( 'input[name="recurring_amount_toggle"]' ).on( 'change', ( e ) => {
				formData.isRecurring = e.target.checked;
			} );

			// Allow further processing.
			body.trigger( 'simpayBindProFormEventsAndTriggers', [ spFormElem, formData ] );
		},

		/**
		 * Toggle `is-focused` class on fields to allow for extra CSS styling.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		setOnFieldFocus: function( spFormElem, formData ) {
			const fields = spFormElem.find( '.simpay-form-control' );

			fields.each( function( i, el ) {
				const field = $( el );

				field.on( 'focusin', setFocus );
				field.on( 'focusout', removeFocus );

				/**
				 * Add `is-focused` class.
				 *
				 * @param {Event} e Event focusin event.
				 */
				function setFocus( e ) {
					$( e.target).addClass( 'is-focused' );
				}

				/**
				 * Remove `is-focused` class.
				 *
				 * @param {Event} e Event focusout event.
				 */
				function removeFocus( e ) {
					const $el = $( e.target );

					// Wait for DatePicker plugin
					setTimeout( function() {
						$el.removeClass( 'is-focused' );

						if ( field.val() ) {
							$el.addClass( 'is-filled' );
						} else {
							$el.removeClass( 'is-filled' );
						}
					}, 300 );
				}
			} );
		},

		/**
		 * Calculate payment amounts.
		 *
		 * @param {Event} e Mixed events. Not used.
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		updateAmounts: function( e, spFormElem, formData ) {
			let tempFinalAmount = formData.amount;

			if ( ( undefined !== formData.customAmount ) && ( formData.customAmount > 0 ) ) {
				tempFinalAmount = formData.customAmount;
			}

			if ( ( 'undefined' !== typeof formData.isSubscription ) && formData.isSubscription ) {

				// Check for single subscription
				if ( 'single' === formData.subscriptionType ) {

					// Check if we are using a custom plan or a regular plan and change amount accordingly
					if ( 'undefined' !== typeof formData.customPlanAmount ) {
						tempFinalAmount = formData.customPlanAmount;
					} else {
						tempFinalAmount = formData.amount;
					}

					// Set planAmount to be used in coupon code calculations
					formData.planAmount = tempFinalAmount;

				} else {

					// Check if we are using a custom plan or a regular plan and change amount accordingly
					if ( ( 'undefined' !== typeof formData.useCustomPlan ) && formData.useCustomPlan ) {
						tempFinalAmount = formData.customPlanAmount;
					} else {
						tempFinalAmount = formData.planAmount;
					}
				}

				if ( formData.isTrial ) {
					tempFinalAmount = 0;
				}

				// Adjust for quantity.
				if ( 'undefined' !== typeof formData.quantity ) {
					tempFinalAmount = tempFinalAmount * formData.quantity;
				}

				// Normal setupFee
				if ( 'undefined' !== typeof formData.setupFee ) {

					// Add the total of all setup fees to the finalAmount
					tempFinalAmount = tempFinalAmount + formData.setupFee;
				}

				// Individual plan setupFee
				if ( 'undefined' !== typeof formData.planSetupFee ) {

					// Add the total of all setup fees to the finalAmount
					tempFinalAmount = tempFinalAmount + spShared.unformatCurrency( formData.planSetupFee );
				}
			} else {
				// Adjust for quantity.
				if ( 'undefined' !== typeof formData.quantity ) {
					tempFinalAmount = tempFinalAmount * formData.quantity;
				}
			}

			// Check for coupon discount
			if ( 'undefined' !== typeof formData.discount ) {
				tempFinalAmount = tempFinalAmount - formData.discount;
			}

			// Only add fee or fee percent if we are not using a subscription
			if ( ( 'undefined' !== typeof formData.isSubscription ) && !formData.isSubscription ) {

				if ( formData.feePercent > 0 ) {
					tempFinalAmount = tempFinalAmount + ( tempFinalAmount * ( formData.feePercent / 100 ) );
				}

				// Add additional fee amount (from user filters currently)
				if ( formData.feeAmount > 0 ) {
					tempFinalAmount = tempFinalAmount + formData.feeAmount;
				}
			}

			if ( formData.taxPercent > 0 ) {

				// For trials, we'll only have an initial tax amount & final amount if there's a setup fee.
				formData.taxAmount = simpayAppPro.calculateTaxAmount( tempFinalAmount, formData.taxPercent );

				// Add final rounded tax amount.
				tempFinalAmount += formData.taxAmount;

				// Set tax amount to hidden input for later form submission.
				spFormElem.find( '.simpay-tax-amount' ).val( formData.taxAmount );
			}

			formData.finalAmount = tempFinalAmount;

			// Send the final amount to Stripe params.
			// Stripe expects amounts in cents (100 for $1.00 USD / no decimals), so convert here.
			formData.stripeParams.amount = spShared.convertToCents( formData.finalAmount );

			// Set the same cents value to hidden input for later form submission.
			spFormElem.find( '.simpay-amount' ).val( formData.stripeParams.amount );
		},

		/**
		 * Calculate the amount of tax given an amount and percentage.
		 *
		 * @param {number} amount Base amount.
		 * @param {number} percent Tax percent.
		 */
		calculateTaxAmount: function( amount, percent ) {
			return Math.abs( accounting.toFixed( amount * ( percent / 100 ), window.spGeneral.integers.decimalPlaces ) );
		},

		...simpayAppProCompat,
	};

	simpayAppPro.init();

}( jQuery ) );

window.simpayAppPro = simpayAppPro;

export default simpayAppPro;
