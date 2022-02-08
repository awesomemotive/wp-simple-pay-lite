/* global jQuery */

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useSelect, select as storeSelect } from '@wordpress/data';

/**
 *
 * @param {Object} attributes Block attributes.
 * @param {integer} attributes.formId Form ID.
 * @return {Array} Initialization getter and setter.
 */
export function usePaymentFormInitialization( attributes ) {
	const { formId } = attributes;

	// Create initial initialization state, and setter.
	const [ isFormInitialized, setFormInitialized ] = useState( false );

	// Retrieve all blocks in the editor.
	const clientIds = useSelect(
		( select ) =>
			select( 'core/block-editor' ).getClientIdsWithDescendants(),
		[]
	);

	useEffect( () => {
		// If this block has already been initialized, do nothing.
		if ( isFormInitialized ) {
			return;
		}

		// See if we have any Payment Form blocks.
		clientIds.forEach( ( clientId ) => {
			const block = storeSelect( 'core/block-editor' ).getBlock(
				clientId
			);

			if ( ! block ) {
				return;
			}

			const {
				attributes: { formId: blockFormId },
				name,
			} = block;

			if ( 'simpay/payment-form' !== name ) {
				return;
			}

			if ( ! blockFormId || blockFormId !== formId ) {
				return;
			}

			// Wait for the server response/DOM to be updated before initializing the form.
			return setTimeout( () => {
				const paymentFormInstance = jQuery(
					`#block-${ clientId } #simpay-block-payment-form-${ blockFormId }`
				);

				window.wpsp.initPaymentForm(
					paymentFormInstance,
					paymentFormInstance.data( 'form-vars' )
				);

				setFormInitialized( true );
			}, 1500 );
		} );
	}, [ clientIds, attributes, isFormInitialized ] );

	// When attributes change, let the form be initialized again.
	useEffect( () => {
		setFormInitialized( false );
	}, [ attributes ] );

	return [ isFormInitialized, setFormInitialized ];
}
