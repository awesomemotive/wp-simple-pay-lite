/* global _ */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

// Need to wait for the DOM because the script is not enqueued at the end of the page.
// @todo Investigate enqueing admin.js in the footer.
domReady( () => {
	const { hooks } = window.wpsp;
	const radioEls = document.querySelectorAll(
		'[name="simpay_settings[test_mode]"]'
	);

	if ( 0 === radioEls.length ) {
		return;
	}

	const selectedRadioEl = document.querySelector(
		'[name="simpay_settings[test_mode]"]:checked'
	);

	const currentMode = 'enabled' === selectedRadioEl.value ? 'test' : 'live';

	hooks.doAction( 'settings.toggleTestMode', currentMode, currentMode );

	// Update when the input changes.
	_.each( radioEls, ( radio ) =>
		radio.addEventListener( 'change', ( e ) => {
			const newMode = 'enabled' === e.target.value ? 'test' : 'live';

			hooks.doAction( 'settings.toggleTestMode', newMode, currentMode );
		} )
	);
} );
