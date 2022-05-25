/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Computes inner chart margins for a given chart.
 * This can be used to add additional items on top of the canvas but within
 * the drawn chart boundaries.
 *
 * @param {Object} chartRef Chart ref.
 * @param {Object} chart data.
 * @return {Object} Chart margins.
 */
export default ( chartRef, chart ) => {
	const [
		{ marginTop, marginRight, marginBottom, marginLeft },
		setMargins,
	] = useState( {
		marginTop: 0,
		marginRight: 0,
		marginBottom: 0,
		marginLeft: 0,
	} );

	useEffect( () => {
		if ( ! chartRef.current ) {
			return;
		}

		setMargins( {
			marginTop: chartRef.current.chartArea.top,
			marginRight:
				chartRef.current.width - chartRef.current.chartArea.right,
			marginBottom: chartRef.current.chartArea.bottom,
			marginLeft: chartRef.current.chartArea.left,
		} );
	}, [ chartRef, chart ] );

	return {
		marginTop,
		marginRight,
		marginBottom,
		marginLeft,
	};
};
