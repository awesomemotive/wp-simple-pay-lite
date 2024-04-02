/* global simpayAdminPageActivityReports */

/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import '@wordpress/core-data';
import { Card, CardBody, CardDivider, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	getEndDateFromType,
	getStartDateFromType,
	useUserPreference,
} from '@wpsimplepay/charts';
import DateRangePicker from './date-range-picker.js';
import PaymentInfoReport from './report-payment-info.js';
import GrossVolumeReport from './report-gross-volume.js';
import SuccessfulPaymentsReport from './report-successful-payments.js';

const {
	user_id: userId,
	default_range: {
		start: defaultDateRangeStart,
		end: defaultDateRangeEnd,
		type: defaultDateRangeType,
	},
	default_currency: currency,
} = simpayAdminPageActivityReports;

const baseClassName = 'simpay-activity-reports-card-reports';

function CardReports() {
	const [ range, setRange ] = useUserPreference(
		userId,
		'simpay_activity_reports_range',
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

	return (
		<Card elevation={ 2 } className={ baseClassName }>
			<CardHeader className={ `${ baseClassName }-header` }>
				<h2 className="simpay-activity-reports-card-title">
					{ __( 'Reports', 'simple-pay' ) }
				</h2>

				<DateRangePicker range={ range } setRange={ setRange } />
			</CardHeader>

			<CardBody>
				<PaymentInfoReport range={ range } currency={ currency } />

				<CardDivider />

				<GrossVolumeReport range={ range } currency={ currency } />

				<CardDivider />

				<SuccessfulPaymentsReport
					range={ range }
					currency={ currency }
				/>
			</CardBody>
		</Card>
	);
}

export default CardReports;
