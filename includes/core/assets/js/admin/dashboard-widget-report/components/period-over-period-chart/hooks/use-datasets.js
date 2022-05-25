/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * Internal depenedencies
 */
import { createLinearGradient } from '../utils';

/**
 * Returns memoized shimmed datasets.
 *
 * @param {Object} datasets Chart datasets.
 */
export default ( datasets ) =>
	useMemo(
		() =>
			datasets.map( ( { label, rgb, data: dataValues } ) => {
				return {
					label,
					data: dataValues,

					// Start gradient at the bottom of the chart.
					fill: 'start',

					// Create a gradient background.
					backgroundColor: ( { chart: { ctx, chartArea } } ) =>
						createLinearGradient( ctx, chartArea, rgb ),

					// Set the line and dot colors.
					hoverBackgroundColor: `rgb(${ rgb })`,
					borderColor: `rgb(${ rgb })`,
					pointBackgroundColor: 'rgba(255, 255, 255, 1)',
					borderWidth: 2,
				};
			} ),
		[ datasets ]
	);
