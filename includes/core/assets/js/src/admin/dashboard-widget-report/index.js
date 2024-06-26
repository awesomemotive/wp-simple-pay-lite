/* global simpayAdminDashboardWidgetReport */

/**
 * WordPress dependencies
 */
import '@wordpress/core-data';
import {
	createInterpolateElement,
	render,
	useEffect,
	useReducer,
} from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import reducer from './reducer.js';
import { ReportFilter, ReportList } from './components';
import {
	getStartDateFromType,
	getEndDateFromType,
	PeriodOverPeriodChart,
	useUserPreference,
} from '@wpsimplepay/charts';

const {
	user_id: userId,
	default_range: {
		type: defaultDateRangeType,
		start: defaultDateRangeStart,
		end: defaultDateRangeEnd,
	},
	default_currency: defaultCurrency,
} = simpayAdminDashboardWidgetReport;

function DashboardWidgetReport() {
	const [ range, setRange ] = useUserPreference(
		userId,
		'simpay_dashboard_widget_report_range',
		{
			start: getStartDateFromType(
				defaultDateRangeType,
				defaultDateRangeStart
			),
			end: getEndDateFromType(
				defaultDateRangeType,
				defaultDateRangeEnd
			),
			type: defaultDateRangeType,
		}
	);

	const [ currency, setCurrency ] = useUserPreference(
		userId,
		'simpay_dashboard_widget_report_currency',
		defaultCurrency
	);

	const [ report, dispatchReport ] = useReducer( reducer, {
		data: false,
		isLoading: true,
	} );

	/**
	 * Fetch API data when the currency or range changes.
	 */
	useEffect( () => {
		const path = addQueryArgs(
			'/wpsp/__internal__/report/dashboard-widget',
			{
				range,
				currency,
			}
		);

		dispatchReport( {
			type: 'START_RESOLUTION',
		} );

		apiFetch( {
			path,
		} ).then( ( reportData ) => {
			dispatchReport( {
				type: 'RECEIVE',
				data: reportData,
			} );

			dispatchReport( {
				type: 'FINISH_RESOLUTION',
			} );
		} );
	}, [ range, currency ] );

	return (
		<>
			<div className="simpay-admin-dashboard-widget-report__chart">
				<PeriodOverPeriodChart
					report={ report }
					style={ { width: '100%', height: '300px' } }
					config={ {
						yAxisIsCurrency: true,
					} }
					noData={
						<div>
							<strong>
								{ sprintf(
									/* translators: %s Currency code. */
									__(
										'No %s transactions found.',
										'simple-pay'
									),
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
			</div>

			<div className="simpay-admin-dashboard-widget-report__data">
				<ReportFilter
					currency={ currency }
					range={ range }
					setCurrency={ setCurrency }
					setRange={ setRange }
					report={ report }
				/>
				<ReportList report={ report } />
			</div>
		</>
	);
}

render(
	<DashboardWidgetReport />,
	document.getElementById( 'simpay-admin-dashboard-widget-report' )
);
