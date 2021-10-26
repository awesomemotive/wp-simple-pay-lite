/* global wp */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

// Need to wait for the DOM because the script is not enqueued at the end of the page.
// @todo Investigate enqueing admin.js in the footer.
// @todo Use REST API.
domReady( () => {
	const nagButton = document.getElementById( 'simpay-usage-tracking-opt-in' );

	if ( ! nagButton ) {
		return;
	}

	nagButton.addEventListener( 'click', ( e ) => {
		e.preventDefault();

		wp.ajax
			.send( 'simpay-usage-tracking-optin-nag', {
				data: {
					nonce: document.getElementById(
						'simpay-usage-tracking-optin-nag'
					).value,
				},
			} )
			.always( () => {
				const notice = document.getElementById(
					'simpay-usage-tracking-optin'
				).parentNode;

				notice.style.display = 'none';
			} );
	} );
} );
