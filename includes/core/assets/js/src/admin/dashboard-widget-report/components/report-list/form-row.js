/**
 * WordPress dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

function FormRow( { id, title, total_formatted: total } ) {
	const formUrl = addQueryArgs( 'post.php', {
		post: id,
		action: 'edit',
	} );

	return (
		<div
			style={ {
				display: 'flex',
				justifyContent: 'space-between',
			} }
		>
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
			<div>
				<strong>{ decodeEntities( total ) }</strong>
			</div>
		</div>
	);
}
export default FormRow;
