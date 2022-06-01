/**
 * External dependencies
 */
import { animated, config, useSpring } from 'react-spring';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

function NotificationActionButton( { count, onOpen } ) {
	const styles = useSpring( {
		config: config.wobbly,
		loop: { reverse: true },
		reset: true,
		from: { transform: 'translate3d(0, 0px, 0)' },
		to: { transform: 'translate3d(0, -5px, 0)' },
	} );

	return (
		<button
			type="button"
			className="simpay-branding-bar__actions-button"
			onClick={ onOpen }
		>
			{ count > 0 && (
				<animated.span
					style={ styles }
					className="simpay-branding-bar__actions-button-count"
					aria-label={ sprintf(
						/* translators: %d Unread notification count. */
						__( '%d unread notifications', 'simple-pay' ),
						count
					) }
				>
					<span>{ count }</span>
				</animated.span>
			) }

			<svg
				viewBox="0 0 20 20"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
			>
				<path
					fillRule="evenodd"
					clipRule="evenodd"
					d="M15.8333 2.5H4.16667C3.25 2.5 2.5 3.25 2.5 4.16667V15.8333c0 .9167.74167 1.6667 1.66667 1.6667H15.8333c.9167 0 1.6667-.75 1.6667-1.6667V4.16667C17.5 3.25 16.75 2.5 15.8333 2.5Zm0 13.3333H4.16667v-2.5h2.96666C7.70833 14.325 8.775 15 10.0083 15c1.2334 0 2.2917-.675 2.875-1.6667h2.95v2.5Zm-4.1583-4.1666h4.1583V4.16667H4.16667v7.50003h4.175c0 .9166.75 1.6666 1.66663 1.6666.9167 0 1.6667-.75 1.6667-1.6666Z"
					fill="currentColor"
				/>
			</svg>
		</button>
	);
}

export default NotificationActionButton;
