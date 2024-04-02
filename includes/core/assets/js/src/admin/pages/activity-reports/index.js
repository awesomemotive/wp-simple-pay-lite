/**
 * WordPress dependencies
 */
import '@wordpress/core-data';
import { render } from '@wordpress/element';
import { Popover } from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	CardToday,
	CardLatestPayments,
	CardReports,
	Config,
} from './components';

const baseClassName = 'simpay-activity-reports';

function ActivityReports() {
	return (
		<div className={ baseClassName }>
			<CardToday />
			<CardLatestPayments />
			<CardReports />
			<Popover.Slot />
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
