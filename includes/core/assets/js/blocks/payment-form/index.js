/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit.js';
import icon from './icon.js';

registerBlockType( 'simpay/payment-form', {
	edit: Edit,
	icon,
	transforms: {
		from: [
			{
				type: 'shortcode',
				tag: 'simpay',
				attributes: {
					formId: {
						type: 'string',
						shortcode: ( attributes ) => {
							return attributes.named.id || 0;
						},
					},
				},
			},
		],
	},
	save: () => null,
} );
