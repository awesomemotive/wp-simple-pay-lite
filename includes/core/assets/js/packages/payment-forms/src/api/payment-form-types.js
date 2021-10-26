/** @typedef {import('./../').PaymentFormType} PaymentFormType */

/**
 * Payment form type definitions keyed by type.
 *
 * @type {Object.<string,PaymentFormType>}
 */
const paymentFormsTypes = {};

/**
 * Registers a payment form type.
 *
 * @since 4.2.0
 *
 * @param {string} type A string identifying the payment from type.
 * @param {PaymentFormType} settings Payment form type settings and callbacks.
 * @return {PaymentFormType} The registered payment form type.
 */
export function registerPaymentFormType( type, settings ) {
	if ( typeof settings !== 'object' ) {
		return null;
	}

	if ( typeof type !== 'string' ) {
		return null;
	}

	if ( paymentFormsTypes[ type ] ) {
		return null;
	}

	// @todo Validate onSetup, onSubmit, and onError exist.
	paymentFormsTypes[ type ] = settings;

	return settings;
}

/**
 * Returns a registered payment form type's settings.
 *
 * @since 4.2.0
 *
 * @param {string} type Payment form type
 * @return {?PaymentFormType} Payment form type settings.
 */
export function getPaymentFormType( type ) {
	return paymentFormsTypes[ type ];
}

/**
 * Returns available Payment Forms.
 *
 * @since 4.2.0
 *
 * @return {Object.<string,PaymentFormType>} Payment form types keyed by type.
 */
export const getPaymentFormTypes = () => paymentFormsTypes;
