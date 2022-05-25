/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

function FormRow( { id, title, total_formatted: total } ) {
	const formUrl = addQueryArgs( 'post.php', {
		post: id,
		action: 'edit',
	} );

	return (
		<Flex justify="space-between" key={ title }>
			<Flex justify="flex-start">
				<span>
					{ title ? (
						<a href={ formUrl } target="_blank" rel="noreferrer">
							{ title }
						</a>
					) : (
						sprintf(
							/* translators: %d Form ID. */
							__( 'Payment form %d (deleted)', 'simple-pay' ),
							id
						)
					) }
				</span>
			</Flex>
			<Flex gap={ 2 } justify="flex-end">
				<strong>{ decodeEntities( total ) }</strong>
			</Flex>
		</Flex>
	);
}
export default FormRow;
