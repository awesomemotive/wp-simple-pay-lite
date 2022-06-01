/**
 * WordPress dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Custom tooltip component.
 *
 * Props are pulled from local state which is updated via the Chart.js tooltip plugin.
 *
 * @param {Object} props
 * @param {Objects} props.position Absolute positioning coordinates.
 * @param {number} props.opacity Opacity.
 * @param {Object} props.data Chart data.
 * @return {JSX.Element} Tooltip.
 */
function Tooltip( { position, opacity, data } ) {
	if ( ! data.length ) {
		return null;
	}

	const { top, left } = position;

	return (
		<div
			className="simpay-admin-dashboard-widget-report__tooltip"
			style={ {
				top,
				left,
				opacity,
			} }
		>
			{ data.map( ( { label, value } ) => (
				<div key={ label + Math.random() }>
					<strong>{ label }</strong>: { decodeEntities( value ) }
				</div>
			) ) }
		</div>
	);
}

export default Tooltip;
