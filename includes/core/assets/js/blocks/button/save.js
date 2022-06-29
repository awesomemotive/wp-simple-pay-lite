/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Filters the save result of the button block if a payment form is selected.
 *
 * @param {WPElement} element    Block save result.
 * @param {WPBlock}   blockType  Block type definition.
 * @param {Object}    attributes Block attributes.
 */
function addShortcodeOnSave( element, blockType, attributes ) {
	// Skip if element is undefined.
	if ( ! element ) {
		return;
	}

	// Skip if not a core button block.
	if ( blockType.name !== 'core/button' ) {
		return element;
	}

	// Skip if a payment form is not selected.
	const { simpayFormId, simpayFormInstanceId } = attributes;

	if ( ! simpayFormId ) {
		return element;
	}

	return (
		<>
			{ element }
			<div
				id={ `simpay-block-button-${ simpayFormInstanceId }` }
				className={ `simpay-block-button-${ simpayFormId }` }
				style={ { display: 'none' } }
			>
				[simpay id={ simpayFormId } isButtonBlock=1 instanceId=
				{ simpayFormInstanceId }]
			</div>
		</>
	);
}

addFilter(
	'blocks.getSaveElement',
	'simpay/save-payment-form-button-block',
	addShortcodeOnSave
);
