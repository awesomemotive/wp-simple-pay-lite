/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Internal dependencies
 */
import FormRow from './form-row.js';

function ReportList( { report } ) {
	if ( ! report ) {
		return null;
	}

	const {
		forms: { top, remaining },
	} = report;

	return (
		<div className="simpay-admin-dashboard-widget-report__data-list">
			{ top.map( ( form ) => (
				<FormRow key={ form.id } { ...form } />
			) ) }

			{ remaining.count > 0 && (
				<Flex justify="space-between">
					<Flex justify="flex-start">
						{ sprintf(
							/* translators: %d The number of forms included in results that are not shown. */
							__( '…and %d more', 'simple-pay' ),
							remaining.count
						) }
					</Flex>
					<Flex justify="flex-end">
						{ decodeEntities( remaining.total_formatted ) }
					</Flex>
				</Flex>
			) }
		</div>
	);
}

export default ReportList;
