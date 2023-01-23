/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useRestApiReport } from '../../hooks';
import DataBar from './data-bar.js';

const baseClassName = 'simpay-activity-reports-card-reports';

function PaymentInfoReport( { currency, range } ) {
	const report = useRestApiReport(
		'/wpsp/__internal__/report/payment-info',
		{
			range,
			currency,
		},
		[ range ]
	);

	const {
		payment_method_types: paymentMethodTypes,
		payment_statuses: paymentStatuses,
	} = report.data;

	return (
		<div className={ `${ baseClassName }-payment-breakdown` }>
			<DataBar
				label={ __( 'Payment Methods', 'simple-pay' ) }
				data={ paymentMethodTypes }
				isLoading={ report.isLoading }
			/>

			<DataBar
				label={ __( 'Payments', 'simple-pay' ) }
				data={ paymentStatuses }
				isLoading={ report.isLoading }
			/>
		</div>
	);
}

export default PaymentInfoReport;
