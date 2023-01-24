/* global simpayAdminPageActivityReports */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useRestApiReport } from '../../hooks';
import { BadgeDelta, PeriodOverPeriodChart } from '@wpsimplepay/charts';

const { default_currency: currency } = simpayAdminPageActivityReports;
const baseClassName = 'simpay-activity-reports-card-reports';

function SuccessfulPaymentsReport( { range } ) {
	const report = useRestApiReport(
		'/wpsp/__internal__/report/successful-payments-period-over-period',
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
					{ __( 'Successful Payments', 'simple-pay' ) }
					{ ! report.isLoading && (
						<>
							: <em>{ total }</em>
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
				config={ { yAxisIsCurrency: false } }
			/>
		</>
	);
}

export default SuccessfulPaymentsReport;
