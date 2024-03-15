/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit.js';
import icon from './icon.js';
import { name, title, description, category } from './block.json';

registerBlockType( name, {
	edit: Edit,
	title,
	description,
	category,
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
