/** @typedef {import('./../').PaymentMethod} PaymentMethod */

/**
 * Payment Method definitions keyed by ID.
 *
 * @type {Object.<string,PaymentMethod>}
 */
const paymentMethods = {};

/**
 * Registers a Payment Method.
 *
 * @since 4.2.0
 *
 * @param {string} id A string identifying the Payment Method.
 * @param {Object} settings Payment Methodsettings and callbacks.
 * @return {PaymentMethod} The registered Payment Method.
 */
export function registerPaymentMethod( id, settings ) {
	if ( typeof settings !== 'object' ) {
		return null;
	}

	if ( typeof id !== 'string' ) {
		return null;
	}

	if ( paymentMethods[ id ] ) {
		return null;
	}

	// @todo Validate onSetup, onSubmit, and onError exist.
	paymentMethods[ id ] = settings;

	return settings;
}

/**
 * Returns a registered Payment Method's settings.
 *
 * @since 4.2.0
 *
 * @param {string} id Payment Method ID.
 * @return {?PaymentMethod} Payment Method settings.
 */
export function getPaymentMethod( id ) {
	return paymentMethods[ id ];
}

/**
 * Returns available Payment Methods.
 *
 * @since 3.8.0
 *
 * @return {Object} Payment Methods keyed by ID.
 */
export const getPaymentMethods = () => paymentMethods;
