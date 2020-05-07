/* global _ */

/**
 * Internal dependencies
 */
import { default as stripeCheckout } from './stripe-checkout';

/**
 * Returns the current form type.
 *
 * @since 3.8.0
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 * @return {string} Form type. Currently supports `stripe-checkout` or `stripe-elements`.
 */
export function getPaymentFormType( spFormElem, formData ) {
	if (
		undefined === formData.formDisplayType ||
		'stripe_checkout' === formData.formDisplayType
	) {
		return 'stripe-checkout';
	} else {
		return 'stripe-elements';
	}
};

/**
 * Returns available Payment Forms.
 *
 * @todo Create a registry/datastore.
 *
 * @since 3.8.0
 *
 * @return {Object} List of available Payment Forms.
 */
export const getPaymentForms = () => ( {
	'stripe-checkout': stripeCheckout,
} );

/**
 * Handles an error on submission.
 *
 * @since 3.8.0
 *
 * @param {Object} error Error data.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function onPaymentFormError( error = {}, spFormElem, formData ) {
	const { showError, enableForm } = window.simpayApp;
	const { debugLog } = window.spShared;
	const { id = '', message = '' } = error;

	showError( spFormElem, formData, message );
	enableForm( spFormElem, formData );
}
