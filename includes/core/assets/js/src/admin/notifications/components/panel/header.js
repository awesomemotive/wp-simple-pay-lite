/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { close } from '@wordpress/icons';

function NotificationPanelHeader( { count, onClose } ) {
	return (
		<div className="simpay-notifications-panel__header">
			<span>
				{ sprintf(
					/* translators: %d Unread notification count. */
					_n(
						'%d Unread Notification',
						'%d Unread Notifications',
						count,
						'simple-pay'
					),
					count
				) }
			</span>

			<Button
				icon={ close }
				iconSize={ 20 }
				onClick={ onClose }
				label={ __( 'Close notifications', 'simple-pay' ) }
				showTooltip={ false }
			/>
		</div>
	);
}

export default NotificationPanelHeader;
