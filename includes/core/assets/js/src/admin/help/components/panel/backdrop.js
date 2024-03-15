/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

function HelpPanelBackdrop( { isOpen, onClose } ) {
	useEffect( () => {
		document.body.classList.toggle( 'simpay-help-body-locked' );

		return () => {
			document.body.classList.remove( 'simpay-help-body-locked' );
		};
	}, [ isOpen ] );

	return <button className="simpay-help-backdrop" onClick={ onClose } />;
}

export default HelpPanelBackdrop;
