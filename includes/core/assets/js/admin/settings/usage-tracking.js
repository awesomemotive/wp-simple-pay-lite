/* global wp */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

// Need to wait for the DOM because the script is not enqueued at the end of the page.
// @todo Investigate enqueing admin.js in the footer.
// @todo Use REST API.
domReady( () => {
	const nagForm = document.getElementById( 'simpay-usage-tracking-nag' );

	if ( ! nagForm ) {
		return;
	}

	const nag = nagForm.parentNode;

	nagForm.addEventListener( 'submit', ( e ) => {
		const optin = wp.ajax.send( 'simpay-usage-tracking-optin-nag', {
			data: {
				email: document.getElementById( 'simpay-usage-tracking-email' ).value,
				nonce: document.getElementById( 'simpay-usage-tracking-optin-nag' ).value,
			},
		} );

		optin.always( () => nag.style.display = 'none' );
	} );
} );
