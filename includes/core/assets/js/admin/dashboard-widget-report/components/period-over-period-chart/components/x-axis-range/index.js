/**
 * Internal dependencies
 */
import { useInnerChartMargins } from './../../hooks';

/**
 * Uses the chart's inner margins to create XAxis start and end range labels.
 *
 * @param {Object} props
 * @param {Object} props.chartRef Chart ref.
 * @param {Object} props.chart Chart data.
 * @return {JSX.Element} Chart axis range labels.
 */
function XAxisRange( { chart, chartRef } ) {
	const { marginLeft, marginRight } = useInnerChartMargins( chartRef, chart );

	return (
		<div
			style={ {
				width: '100%',
				position: 'relative',
				fontSize: '12px',
				display: 'flex',
				justifyContent: 'space-between',
				marginTop: '-2px',
			} }
		>
			<div style={ { marginLeft, textAlign: 'left' } }>
				{ chart.datasets[ 0 ].data[ 0 ].label }
			</div>
			<div style={ { marginRight, textAlign: 'right' } }>
				{
					chart.datasets[ 0 ].data[
						chart.datasets[ 0 ].data.length - 1
					].label
				}
			</div>
		</div>
	);
}

export default XAxisRange;
