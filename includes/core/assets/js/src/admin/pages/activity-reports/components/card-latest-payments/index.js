/* global simpayAdminPageActivityReports */

/**
 * WordPress dependencies
 */
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import EmptyState from './empty-state.js';
import Payment from './payment.js';
import { useRestApiReport } from '../../hooks';

const {
	default_currency: currency,
	links: { all_payments: allPaymentsLink },
} = simpayAdminPageActivityReports;
const baseClassName = 'simpay-activity-reports-card-latest';

function CardLatestPayments() {
	const report = useRestApiReport(
		'/wpsp/__internal__/report/latest-payments',
		{
			currency,
		},
		[]
	);

	const { payments } = report.data;

	return (
		<Card elevation={ 2 } className={ baseClassName }>
			<CardHeader>
				<h2 className="simpay-activity-reports-card-title">
					{ __( 'Latest Payments', 'simple-pay' ) }
				</h2>
				<Button
					className="simpay-activity-reports-card-title-link"
					href={ allPaymentsLink }
					variant="link"
					isSmall
					target="_blank"
				>
					{ __( 'View More →', 'simple-pay' ) }
				</Button>
			</CardHeader>

			<CardBody>
				{ report.isLoading && <Spinner /> }

				{ ! report.isLoading && payments.length > 0 && (
					<table className={ `${ baseClassName }-payments` }>
						<tbody>
							{ payments.map( ( payment ) => {
								return (
									<Payment
										key={ payment.id }
										{ ...payment }
									/>
								);
							} ) }
						</tbody>
					</table>
				) }

				{ ! report.isLoading && payments.length === 0 && (
					<EmptyState currency={ currency } />
				) }
			</CardBody>
		</Card>
	);
}

export default CardLatestPayments;
