/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { createInterpolateElement } from '@wordpress/element';

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
				noData={
					<div>
						<strong>
							{ sprintf(
								/* translators: %s Currency code. */
								__( 'No %s transactions found.', 'simple-pay' ),
								currency.toUpperCase()
							) }
						</strong>
						<div>
							{ __(
								'Please select a different currency, date range, or check back later.',
								'simple-pay'
							) }
						</div>
					</div>
				}
				deltaChangedString={ ( tooltip ) =>
					createInterpolateElement(
						sprintf(
							/* translators: %s: delta percentage */
							__(
								'<delta>%1$s%%</delta> vs. previous period',
								'simple-pay'
							),
							Math.abs( tooltip.delta )
						),
						{
							delta: (
								<strong
									className={
										tooltip.delta > 0
											? 'is-positive'
											: 'is-negative'
									}
								/>
							),
						}
					)
				}
			/>
		</>
	);
}

export default GrossVolumeReport;
