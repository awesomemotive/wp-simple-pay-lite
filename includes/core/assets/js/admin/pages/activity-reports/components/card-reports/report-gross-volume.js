/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Internal dependencies
 */
import { useRestApiReport } from '../../hooks';
import { BadgeDelta, PeriodOverPeriodChart } from '@wpsimplepay/charts';

const baseClassName = 'simpay-activity-reports-card-reports';

function GrossVolumeReport( { currency, range } ) {
	const report = useRestApiReport(
		'/wpsp/__internal__/report/gross-volume-period-over-period',
		{
			range,
			currency,
		},
		[ range ]
	);

	const { delta, total_formatted: total } = report.data;

	return (
		<>
			<div className={ `${ baseClassName }-report-title` }>
				<h3>
					{ __( 'Gross Volume', 'simple-pay' ) }
					{ ! report.isLoading && (
						<>
							: <em>{ decodeEntities( total ) }</em>
						</>
					) }
				</h3>

				{ ! report.isLoading && <BadgeDelta delta={ delta } /> }
			</div>
			<PeriodOverPeriodChart
				report={ report }
				style={ {
					width: '100%',
					height: '300px',
				} }
				config={ { yAxisIsCurrency: true } }
			/>
		</>
	);
}

export default GrossVolumeReport;
