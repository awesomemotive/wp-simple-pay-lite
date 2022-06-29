/**
 * External dependencies
 */
import {
	Chart as ChartJS,
	CategoryScale,
	LinearScale,
	PointElement,
	LineElement,
	Tooltip,
	Filler,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(
	CategoryScale,
	LinearScale,
	PointElement,
	LineElement,
	Tooltip,
	Filler
);

/**
 * WordPress dependencies
 */
import { useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import getConfig from './config.js';
import {
	ChartBorder,
	Tooltip as TooltipPlugin,
	YGridLineHover,
} from './plugins';
import { useDatasets, useTooltip } from './hooks';
import { Tooltip as TooltipComponent, XAxisRange } from './components';

function LineChart( { user, report } ) {
	const chartRef = useRef( null );
	const [ tooltip, setTooltip ] = useTooltip();
	const datasets = useDatasets( report ? report.chart.datasets : [] );

	const dimensions = {
		width: '100%',
		height: '300px',
	};

	if ( ! report || ! user ) {
		return (
			<div
				style={ {
					...dimensions,
					display: 'flex',
					justifyContent: 'center',
					alignItems: 'center',
				} }
			>
				<Spinner />
			</div>
		);
	}

	const config = getConfig( report );
	const options = {
		...config,
		plugins: {
			...config.plugins,
			tooltip: TooltipPlugin( tooltip, setTooltip ),
		},
	};

	const { total, currency } = report;

	return (
		<div style={ dimensions }>
			{ 0 === total && (
				<div
					className="simpay-admin-dashboard-widget-report__no-data"
					style={ {
						...dimensions,
						height: `calc(${ dimensions.height } + 15px)`,
					} }
				>
					<div>
						<strong>
							{ sprintf(
								/* translators: %s Currency code. */
								__(
									'No %s transactions for selected period.',
									'simple-pay'
								),
								currency.code.toUpperCase()
							) }
						</strong>
						<div>
							{ __(
								'Please select a different currency, period, or check back later.',
								'simple-pay'
							) }
						</div>
					</div>
				</div>
			) }
			<Line
				ref={ chartRef }
				options={ options }
				plugins={ [ ChartBorder, YGridLineHover ] }
				data={ { datasets } }
			/>
			<XAxisRange chartRef={ chartRef } chart={ report.chart } />
			<TooltipComponent { ...tooltip } />
		</div>
	);
}

export default LineChart;
