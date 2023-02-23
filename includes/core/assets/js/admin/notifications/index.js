/* eslint-disable @wordpress/no-global-event-listener */

/**
 * WordPress dependencies
 */
import { render, useEffect, useReducer, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { ESCAPE } from '@wordpress/keycodes';
import { getFragment } from '@wordpress/url';

/**
 * Internal dependencies
 */
import reducer from './reducer.js';
import {
	NotificationsPanelActionButton,
	NotificationsPanelBackdrop,
	NotificationsPanel,
} from './components';

function Notifications() {
	const [ notifications, dispatchNotifications ] = useReducer( reducer, {
		data: [],
		isLoading: true,
	} );

	const [ isOpen, setIsOpen ] = useState(
		'#notifications' === getFragment( window.location.href )
	);

	/**
	 * Manage the URL fragment when the panel is opened or closed.
	 */
	useEffect( () => {
		function maybeOpenPanel() {
			if ( '#notifications' === getFragment( window.location.href ) ) {
				setIsOpen( true );
			}
		}

		window.addEventListener( 'hashchange', maybeOpenPanel );

		return () => {
			window.removeEventListener( 'hashchange', maybeOpenPanel );
		};
	}, [] );

	/**
	 * Update admin bar notification bubble count.
	 */
	useEffect( () => {
		if ( notifications.isLoading ) {
			return;
		}

		const bubbleEl = document.querySelector(
			'#wp-admin-bar-simpay-admin-bar-test-mode .wp-ui-notification'
		);

		if ( notifications.data.length === 0 ) {
			if ( bubbleEl ) {
				bubbleEl.remove();
			}

			const menuBarEl = document.getElementById(
				'wp-admin-bar-simpay-notifications'
			);

			if ( menuBarEl ) {
				menuBarEl.remove();
			}

			const menuItemEl = document.querySelector(
				'#menu-posts-simple-pay .wp-submenu a[href$="#notifications"]'
			);

			if ( menuItemEl ) {
				menuItemEl.remove();
			}
		} else if ( bubbleEl ) {
			bubbleEl.textContent = notifications.data.length;
		}
	}, [ notifications.data ] );

	/**
	 * Load notifications on.
	 */
	useEffect( () => {
		dispatchNotifications( {
			type: 'START_RESOLUTION',
		} );

		apiFetch( {
			path: '/wpsp/__internal__/notifications',
		} ).then( ( { data } ) => {
			dispatchNotifications( {
				type: 'SET',
				notifications: data,
			} );

			dispatchNotifications( {
				type: 'FINISH_RESOLUTION',
			} );
		} );
	}, [] );

	/**
	 * Close the panel.
	 */
	function onClose() {
		setIsOpen( false );

		window.history.pushState(
			'',
			document.title,
			window.location.pathname + window.location.search
		);
	}

	/**
	 * Open the panel.
	 */
	function onOpen() {
		setIsOpen( true );

		window.history.pushState(
			'',
			document.title,
			window.location.pathname + window.location.search + '#notifications'
		);
	}

	function handleEscapeKeyDown( event ) {
		if ( event.keyCode === ESCAPE && ! event.defaultPrevented ) {
			event.preventDefault();
			setIsOpen( false );
		}
	}

	/**
	 * Dismiss a notification.
	 *
	 * @param {number} notificationId
	 */
	function onDismissNotification( notificationId ) {
		dispatchNotifications( {
			type: 'DISMISS',
			id: notificationId,
		} );

		apiFetch( {
			path: '/wpsp/__internal__/notifications/' + notificationId,
			method: 'DELETE',
		} );
	}

	return (
		// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
		<div onKeyDown={ handleEscapeKeyDown } role="region">
			<NotificationsPanelActionButton
				count={ notifications.data.length }
				isOpen={ isOpen }
				onOpen={ onOpen }
			/>

			{ isOpen && (
				<>
					<NotificationsPanel
						notifications={ notifications }
						onDismissNotification={ onDismissNotification }
						onClose={ onClose }
					/>
					<NotificationsPanelBackdrop onClose={ onClose } />
				</>
			) }
		</div>
	);
}

render(
	<Notifications />,
	document.getElementById( 'simpay-branding-bar-notifications' )
);
