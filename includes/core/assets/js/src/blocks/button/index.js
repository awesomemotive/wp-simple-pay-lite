/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import './edit.js';
import './save.js';

/**
 * Adds additional attributes to the button block for managing payment forms.
 *
 * @param {Object} settings Block settings.
 * @param {string} name Block name.
 * @return {Object} Block settings.
 */
function registerBlockAttribues( settings, name ) {
	// Skip if not a core button block.
	if ( name !== 'core/button' ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			simpayFormId: {
				type: 'integer',
			},
			simpayFormInstanceId: {
				type: 'integer',
			},
		},
	};
}

addFilter(
	'blocks.registerBlockType',
	'simpay/payment-form-button-block',
	registerBlockAttribues
);
