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
	const button = document.getElementById( 'simpay-add-field-lite' );

	if ( ! button ) {
		return;
	}

	button.addEventListener( 'click', maybeBlockButtonWithUpgradeModal );
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
