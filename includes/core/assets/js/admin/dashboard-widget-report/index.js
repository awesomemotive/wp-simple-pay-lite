/* global simpayAdminDashboardWidgetReport */

/**
 * WordPress dependencies
 */
import '@wordpress/core-data';
import { render, useEffect, useReducer, useState } from '@wordpress/element';
import { Popover } from '@wordpress/components';
import { addQueryArgs } from '@wordpress/url';
import { useDispatch, useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import reducer from './reducer.js';
import { PeriodOverPeriodChart, ReportFilter, ReportList } from './components';

const {
	user_id: userId,
	default_date_range: defaultDateRange,
	default_currency: defaultCurrency,
} = simpayAdminDashboardWidgetReport;

function DashboardWidgetReport() {
	const [ range, setRange ] = useState( defaultDateRange );
	const [ currency, setCurrency ] = useState( defaultCurrency );
	const [ report, dispatchReport ] = useReducer( reducer, {
		data: false,
		isLoading: false,
	} );
	const { editEntityRecord, saveEditedEntityRecord } = useDispatch( 'core' );

	// Ensure the User record is resolved on load to prevent an undefined
	// index when attempting to update the user meta later.
	const user = useSelect(
		( select ) => {
			const { getEntityRecord } = select( 'core' );
			return getEntityRecord( 'root', 'user', userId );
		},
		[ userId ]
	);

	/**
	 * Fetch API data when the currency or range changes.
	 */
	useEffect( () => {
		if ( ! user ) {
			return;
		}

		const path = addQueryArgs( '/wpsp/v2/report/dashboard-widget', {
			range,
			currency,
		} );

		dispatchReport( {
			type: 'START_RESOLUTION',
		} );

		apiFetch( {
			path,
		} ).then( ( { data } ) => {
			dispatchReport( {
				type: 'RECEIVE',
				data,
			} );

			dispatchReport( {
				type: 'FINISH_RESOLUTION',
			} );

			// Save preferences if they have changed.
			if (
				range !== user.meta.simpay_dashboard_widget_report_date_range ||
				currency !== user.meta.simpay_dashboard_widget_report_currency
			) {
				editEntityRecord( 'root', 'user', userId, {
					meta: {
						simpay_dashboard_widget_report_date_range: range,
						simpay_dashboard_widget_report_currency: currency,
					},
				} );

				saveEditedEntityRecord( 'root', 'user', userId );
			}
		} );
	}, [ range, currency, user ] );

	return (
		<>
			<div className="simpay-admin-dashboard-widget-report__chart">
				<PeriodOverPeriodChart
					report={ report.data }
					range={ range }
					user={ user }
				/>
			</div>

			<div className="simpay-admin-dashboard-widget-report__data">
				<ReportFilter
					currency={ currency }
					range={ range }
					setCurrency={ setCurrency }
					setRange={ setRange }
					report={ report.data }
				/>
				<ReportList report={ report.data } />
			</div>

			<Popover.Slot />
		</>
	);
}

render(
	<DashboardWidgetReport />,
	document.getElementById( 'simpay-admin-dashboard-widget-report' )
);
