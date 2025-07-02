import { __ } from '@wordpress/i18n';

export default function BalanceError() {
	return (
		<div
			className="simpay-admin-charts-no-data"
			style={ { width: '100%', height: '100%' } }
		>
			<div>
				<strong>
					{ sprintf(
						__( 'Unable to load Stripe account balance.', 'simple-pay' ),
						
					) }
				</strong>
				<div>
					{ __(
						'Please refresh the page to try again. Make sure you have a valid license key.',
						'simple-pay'
					) }
				</div>
			</div>
		</div>
	);
} 