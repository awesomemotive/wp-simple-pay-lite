/**
 * External dependencies
 */
import classnames from 'classnames';
import { animated, useTransition, config } from 'react-spring';
import { sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import {
	useFocusReturn,
	useFocusOnMount,
	useConstrainedTabbing,
} from '@wordpress/compose';
import { Animate, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useMergeRefs } from './../../hooks';
import NotificationPanelHeader from './header';
import Notification from './../notification';

function NotificationsPanel( {
	notifications,
	onClose,
	onDismissNotification,
} ) {
	const { data, isLoading } = notifications;
	const focusOnMountRef = useFocusOnMount( 'firstElement' );
	const constrainedTabbingRef = useConstrainedTabbing();
	const focusReturnRef = useFocusReturn();

	const refs = useMergeRefs( [
		constrainedTabbingRef,
		focusReturnRef,
		focusOnMountRef,
	] );

	const [ refMap ] = useState( () => new WeakMap() );

	const sortedNotifications = sortBy( data, 'is_dismissible' );

	const notificationTransitions = useTransition( sortedNotifications, {
		config: config.default,
		enter: ( item ) => async ( next ) => {
			await next( {
				height: refMap.get( item ).offsetHeight,
				transform: 'translate3d(0%, 0px, 0px)',
			} );
		},
		leave: [
			{
				transform: 'translate3d(150%, 0px, 0px)',
			},
			{
				height: 0,
			},
		],
		keys: ( { id } ) => id,
		trail: 100,
	} );

	return (
		<Animate type="slide-in" options={ { origin: 'left' } }>
			{ ( { className } ) => {
				const panelClassNames = classnames(
					'simpay-notifications-panel',
					className
				);

				return (
					<div ref={ refs } className={ panelClassNames }>
						<NotificationPanelHeader
							count={ notifications.data.length }
							onClose={ onClose }
						/>

						<div className="simpay-notifications-panel__notifications">
							{ isLoading && (
								<div className="simpay-notifications-panel__none">
									<Spinner />
								</div>
							) }

							{ data.length === 0 && ! isLoading && (
								<div className="simpay-notifications-panel__none">
									<span>
										{ __(
											'You have no new notifications.',
											'simple-pay'
										) }
									</span>
								</div>
							) }

							{ notificationTransitions(
								( styles, notification ) => (
									<animated.div
										style={ {
											...styles,
											overflow: 'hidden',
										} }
										ref={ ( ref ) =>
											ref &&
											refMap.set( notification, ref )
										}
									>
										<Notification
											onDismissNotification={
												onDismissNotification
											}
											notification={ notification }
										/>
									</animated.div>
								)
							) }
						</div>
					</div>
				);
			} }
		</Animate>
	);
}

export default NotificationsPanel;
