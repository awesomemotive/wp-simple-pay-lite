/* global simpayAdminDashboardWidgetReport */

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { Button, SelectControl, Tooltip } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, info } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import MoreInfo from './more-info.js';

const { currencies } = simpayAdminDashboardWidgetReport;

function ReportFilter( { report, currency, setCurrency, range, setRange } ) {
	const { total, delta } = report || {};
	const [ isShowingMoreInfo, setIsShowingMoreInfo ] = useState( false );

	const badgeClassName = classnames(
		'simpay-admin-dashboard-widget-report__badge',
		{
			'is-positive': delta > 0,
			'is-negative': delta < 0,
		}
	);

	return (
		<div className="simpay-admin-dashboard-widget-report__data-filter">
			<div className="simpay-admin-dashboard-widget-report__data-filter-title">
				<span>{ __( 'Payments by form', 'simple-pay' ) }</span>

				{ isShowingMoreInfo && (
					<MoreInfo setIsOpen={ setIsShowingMoreInfo } />
				) }

				{ total > 0 && delta > 0 && (
					<Tooltip
						text={ __(
							'Change since previous period',
							'simple-pay'
						) }
						position="top center"
					>
						<div className={ badgeClassName }>
							<>
								{ delta < 0 ? '&ndash;' : '' }
								{ delta || '0' }%
							</>
						</div>
					</Tooltip>
				) }

				<Button
					isLink
					variant="link"
					onClick={ () => setIsShowingMoreInfo( true ) }
				>
					<Icon size={ 20 } icon={ info } />
				</Button>
			</div>

			<div className="simpay-admin-dashboard-widget-report__data-filter-controls">
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
					value={ range }
					onChange={ setRange }
					options={ [
						{ label: 'Last 7 days', value: 'last7' },
						{ label: 'Last 30 days', value: 'last30' },
					] }
				/>
			</div>
		</div>
	);
}

export default ReportFilter;
