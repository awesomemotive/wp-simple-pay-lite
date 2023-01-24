/**
 * External dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Internal dependencies
 */
import { BadgeDelta } from '@wpsimplepay/charts';

function Stat( { label, value, delta } ) {
	return (
		<div className="simpay-activity-reports-stat">
			<strong className="simpay-activity-reports-stat__label">
				{ label }
			</strong>

			<div className="simpay-activity-reports-stat__value">
				<span>{ decodeEntities( value ) }</span>
				<BadgeDelta
					delta={ delta }
					className="simpay-activity-reports-stat__delta"
				/>
			</div>
		</div>
	);
}

export default Stat;
