/* global jQuery */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { maybeBlockButtonWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Handles product education when attempting to add a custom field in Lite.
 *
 * @since 4.4.7
 */
function customFields() {
	// Use jQuery so we can prevent the event from bubbling up.
	const button = jQuery( '#simpay-form-settings' ).find( '#lite-add-field' );

	if ( ! button ) {
		return;
	}

	button.on( 'click.simpayAddField', function ( e ) {
		const newE = e;

		const customFieldCount = document.querySelectorAll(
			'.simpay-custom-fields > div'
		).length;

		// Update the data-available attribute on the button based on the number of custom fields.
		newE.target.dataset.available = customFieldCount >= 3 ? 'no' : 'yes';

		const blocked = maybeBlockButtonWithUpgradeModal( newE );

		if ( blocked ) {
			e.stopImmediatePropagation();
		}
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	const formSettings = document.querySelector(
		'.post-type-simple-pay #post'
	);

	if ( formSettings ) {
		customFields();
	}
} );
