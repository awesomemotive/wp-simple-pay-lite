/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Internal dependencies
 */
import FormRow from './form-row.js';

function ReportList( { report } ) {
	if ( report.isLoading ) {
		return (
			<div className="simpay-admin-dashboard-widget-report__data-list" />
		);
	}

	const {
		top_forms: { top, remaining },
	} = report.data;

	return (
		<div className="simpay-admin-dashboard-widget-report__data-list">
			{ top.map( ( form ) => (
				<FormRow key={ form.id } { ...form } />
			) ) }

			{ remaining.count > 0 && (
				<div
					style={ {
						display: 'flex',
						justifyContent: 'space-between',
					} }
				>
					<div>
						{ sprintf(
							/* translators: %d The number of forms included in results that are not shown. */
							__( '…and %d more', 'simple-pay' ),
							remaining.count
						) }
					</div>
					<div>{ decodeEntities( remaining.total_formatted ) }</div>
				</div>
			) }
		</div>
	);
}

export default ReportList;
