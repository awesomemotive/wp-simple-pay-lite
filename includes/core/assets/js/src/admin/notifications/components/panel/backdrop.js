/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

function NotificationsPanelBackdrop( { isOpen, onClose } ) {
	useEffect( () => {
		document.body.classList.toggle( 'simpay-notifications-body-locked' );

		return () => {
			document.body.classList.remove(
				'simpay-notifications-body-locked'
			);
		};
	}, [ isOpen ] );

	return (
		<button className="simpay-notifications-backdrop" onClick={ onClose } />
	);
}

export default NotificationsPanelBackdrop;
