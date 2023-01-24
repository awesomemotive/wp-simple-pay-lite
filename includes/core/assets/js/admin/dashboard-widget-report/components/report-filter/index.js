/* global simpayAdminDashboardWidgetReport */

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { Button, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, info } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import MoreInfo from './more-info.js';
import {
	BadgeDelta,
	getEndDateFromType,
	getStartDateFromType,
} from '@wpsimplepay/charts';

const { currencies } = simpayAdminDashboardWidgetReport;
const today = new Date();

function ReportFilter( { report, currency, setCurrency, range, setRange } ) {
	const { delta } = report.data;
	const [ isShowingMoreInfo, setIsShowingMoreInfo ] = useState( false );

	return (
		<div className="simpay-admin-dashboard-widget-report__data-filter">
			<div className="simpay-admin-dashboard-widget-report__data-filter-title">
				<strong>{ __( 'Top Forms', 'simple-pay' ) }</strong>

				{ isShowingMoreInfo && (
					<MoreInfo setIsOpen={ setIsShowingMoreInfo } />
				) }

				<Button
					variant="link"
					onClick={ () => setIsShowingMoreInfo( true ) }
				>
					<Icon size={ 20 } icon={ info } />
				</Button>
			</div>

			<div className="simpay-admin-dashboard-widget-report__data-filter-controls">
				{ ! report.isLoading && 0 !== delta && (
					<BadgeDelta delta={ delta } />
				) }

				<SelectControl
					label={ __( 'Currency', 'simple-pay' ) }
					hideLabelFromVision={ true }
					value={ currency }
					onChange={ setCurrency }
					options={ currencies.map( ( currencyCode ) => ( {
						label: currencyCode,
						value: currencyCode.toLowerCase(),
					} ) ) }
				/>
				<SelectControl
					label={ __( 'Range', 'simple-pay' ) }
					hideLabelFromVision={ true }
					value={ range.type }
					onChange={ ( type ) => {
						return setRange( {
							type,
							start: getStartDateFromType( type, today ),
							end: getEndDateFromType( type, range.end ),
						} );
					} }
					options={ [
						{ label: 'Today', value: 'today' },
						{ label: 'Last 7 days', value: '7days' },
						{ label: 'Last 4 weeks', value: '4weeks' },
					] }
				/>
			</div>
		</div>
	);
}

export default ReportFilter;
