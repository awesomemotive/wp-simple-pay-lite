/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

function EmptyState( { currency } ) {
	return (
		<div
			className="simpay-admin-charts-no-data"
			style={ { width: '100%', height: '100%' } }
		>
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
						'Please select a different currency or check back later.',
						'simple-pay'
					) }
				</div>
			</div>
		</div>
	);
}

export default EmptyState;
