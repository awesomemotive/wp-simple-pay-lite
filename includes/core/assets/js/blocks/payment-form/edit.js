/* global simpayBlockPaymentForm */

/**
 * WordPress dependencies
 */
import ServerSideRenderer from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import {
	Disabled,
	PanelBody,
	Placeholder,
	SelectControl,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import icon from './icon.js';
import { usePaymentFormInitialization, usePaymentForms } from './hooks';

const {
	isLite,
	previews: { lite: litePreview, pro: proPreview },
} = simpayBlockPaymentForm;

function Edit( { attributes, setAttributes } ) {
	const { formId, showTitle, showDescription, preview } = attributes;
	const [ isFormInitialized ] = usePaymentFormInitialization( attributes );
	const { paymentForms, isLoading, hasPaymentForms } = usePaymentForms();

	// Show a fake preview of the payment form if requested.
	if ( preview ) {
		return (
			<img
				src={ '1' === isLite ? litePreview : proPreview }
				alt={ __( 'Payment Form Preview', 'simpay' ) }
				style={ { maxWidth: '100%' } }
			/>
		);
	}

	// Build payment form <select />.
	const paymentFormOptions = [
		{
			label: __( 'Select a form', 'simple-pay' ),
			value: 0,
		},
		...paymentForms.map( ( { id, payment_form_title: label } ) => ( {
			label,
			value: id,
		} ) ),
	];

	const selectFormControl = (
		<SelectControl
			label={ __( 'Select a payment form', 'simple-pay' ) }
			value={ formId }
			onChange={ ( newFormId ) =>
				setAttributes( { formId: parseInt( newFormId ) } )
			}
			options={ paymentFormOptions }
		/>
	);

	// If a payment form has not been set, or payment forms have not been loaded, show a placeholder.
	if ( ! formId ) {
		return (
			<Placeholder
				icon={ icon }
				label={ __( 'WP Simple Pay - Payment Form', 'simple-pay' ) }
			>
				{ isLoading && <Spinner /> }

				{ ! isLoading &&
					! hasPaymentForms &&
					__( 'No payment forms found.', 'simple-pay' ) }

				{ ! isLoading && hasPaymentForms && selectFormControl }
			</Placeholder>
		);
	}

	return (
		<Disabled>
			<InspectorControls>
				<PanelBody title={ __( 'Form Settings', 'simple-pay' ) }>
					{ selectFormControl }

					<ToggleControl
						label={ __( 'Show Title', 'simple-pay' ) }
						checked={ showTitle }
						onChange={ () =>
							setAttributes( {
								showTitle: ! showTitle,
							} )
						}
					/>

					<ToggleControl
						label={ __( 'Show Description', 'simple-pay' ) }
						checked={ showDescription }
						onChange={ () =>
							setAttributes( {
								showDescription: ! showDescription,
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div className={ ! isFormInitialized ? 'is-loading' : '' }>
				<ServerSideRenderer
					block="simpay/payment-form"
					attributes={ attributes }
				/>
			</div>
		</Disabled>
	);
}

export default Edit;
