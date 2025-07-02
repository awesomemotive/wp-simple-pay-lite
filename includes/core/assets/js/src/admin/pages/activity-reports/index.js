/**
 * WordPress dependencies
 */
import '@wordpress/core-data';
import { render } from '@wordpress/element';
import { Popover } from '@wordpress/components';
import { useState } from 'react';

/**
 * Internal dependencies
 */
import {
	CardToday,
	CardLatestPayments,
	CardReports,
	Config,
	EmbeddedBanner,
	EmbeddedBalance,
} from './components';

const baseClassName = 'simpay-activity-reports';

export default function ActivityReports() {
	const [isBalanceHidden, setIsBalanceHidden] = useState(false);

	return (
		<div>
			<div style={ { width: '100%', marginBottom: '1rem' } }>
				<EmbeddedBanner />
			</div>
			<div
				style={ {
					display: 'flex',
					flexDirection: 'row',
					gap: '1rem',
					width: '100%',
				} }
			>
				<div style={ { width: '25%' } }>
					<CardToday />
				</div>
				<div 
					style={ { 
						width: '75%',
						display: 'flex',
						gap: '1rem',
					} }
				>
					<div style={ { 
						width: isBalanceHidden ? '100%' : '58%',
						transition: 'width 0.3s ease-in-out'
					} }>
						<CardLatestPayments />
					</div>
					{!isBalanceHidden && (
						<div style={ { width: '42%' } }>
							<EmbeddedBalance onTestModeChange={setIsBalanceHidden} />
						</div>
					)}
				</div>
			</div>
			<div className={ baseClassName }>
				<CardReports />
				<Popover.Slot />
			</div>
		</div>
	);
}

render(
	<Config />,
	document.getElementById( 'simpay-admin-page-activity-reports-config' )
);

render(
	<ActivityReports />,
	document.getElementById( 'simpay-admin-page-activity-reports' )
);
