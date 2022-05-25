/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Tuple that returns a storage mechanism for the Tooltip component and a way
 * to update the data.
 */
export default () => {
	const [ tooltip, setTooltip ] = useState( {
		opacity: 0,
		position: {
			top: 0,
			left: 0,
		},
		data: [],
	} );

	return [ tooltip, setTooltip ];
};
