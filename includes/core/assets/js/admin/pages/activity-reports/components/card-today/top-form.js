/**
 * WordPress dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';

const baseClassName = 'simpay-activity-reports-card-today';

function TopForm( form ) {
	const { title, href, gross_volume: grossVolume } = form;

	return (
		<div className={ `${ baseClassName }-forms__form` } key={ title }>
			<a href={ decodeEntities( href ) }>{ title }</a>
			<strong>{ decodeEntities( grossVolume ) }</strong>
		</div>
	);
}

export default TopForm;
