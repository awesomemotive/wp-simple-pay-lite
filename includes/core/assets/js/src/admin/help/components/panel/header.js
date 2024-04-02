/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { close } from '@wordpress/icons';

function HelpPanelHeader( { onClose } ) {
	return (
		<div className="simpay-help-panel__header">
			<span>{ __( "We're Here to Help", 'simple-pay' ) }</span>

			<Button
				icon={ close }
				iconSize={ 20 }
				onClick={ onClose }
				label={ __( 'Close help', 'simple-pay' ) }
				showTooltip={ false }
			/>
		</div>
	);
}

export default HelpPanelHeader;
