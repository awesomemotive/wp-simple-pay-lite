/* global simpayBlockButton */

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { PanelBody, SelectControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import icon from './icon.js';

/**
 * Adds inspector controls to the core button block.
 */
const withPaymentFormSelector = createHigherOrderComponent( ( BlockEdit ) => {
	/**
	 * @param {Object} props Block properties.
	 */
	return ( props ) => {
		// Skip if not a core button block.
		if ( props.name !== 'core/button' ) {
			return <BlockEdit { ...props } />;
		}

		// Skip if we cannot find our payment form list.
		if ( ! simpayBlockButton || ! simpayBlockButton.paymentForms ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const { simpayFormId } = attributes;

		/**
		 * Update attributes when a payment form is selected.
		 *
		 * @param {string} value Payment form ID.
		 */
		const onChange = ( value ) => {
			if ( '' === value ) {
				setAttributes( {
					simpayFormId: null,
					simpayFormInstanceId: null,
				} );
				return;
			}

			setAttributes( {
				simpayFormId: parseInt( value ),
				simpayFormInstanceId: Math.floor( Math.random() * 1000 ),
			} );
		};

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						icon={ icon }
						title={ __( 'WP Simple Pay', 'simple-pay' ) }
						initialOpen={ true }
					>
						<SelectControl
							label={ __( 'Payment Form', 'simple-pay' ) }
							value={ simpayFormId }
							onChange={ onChange }
							options={ [
								{
									label: __( 'Select a form…', 'simple-pay' ),
									value: '',
								},
								...simpayBlockButton.paymentForms,
							] }
							help={ __(
								'Select an overlay or Stripe Checkout payment form to launch.',
								'simple-pay'
							) }
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'withToolbarButton' );

addFilter(
	'editor.BlockEdit',
	'simpay/with-payment-form-button-block',
	withPaymentFormSelector
);
