/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * Custom tooltip implementation.
 *
 * @param {Object} tooltip Local tooltip state.
 * @param {Function} setTooltip Sets the local tooltip state.
 */
export default ( tooltip, setTooltip ) => {
	return {
		enabled: false,
		mode: 'index',
		external: ( { tooltip: tooltipModel } ) => {
			if ( tooltipModel.opacity === 0 ) {
				if ( tooltip.opacity !== 0 )
					setTooltip( ( prev ) => ( {
						...prev,
						opacity: 0,
					} ) );
				return;
			}

			const newTooltipData = {
				opacity: 1,
				position: {
					left: tooltipModel.caretX + 20,
					top: 20,
				},
				data: tooltipModel.dataPoints.map(
					( { dataset, dataIndex } ) => {
						const { data } = dataset;
						return data[ dataIndex ];
					}
				),
			};

			if ( ! isEqual( tooltip, newTooltipData ) ) {
				setTooltip( newTooltipData );
			}
		},
	};
};
