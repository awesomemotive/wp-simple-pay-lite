/* global wpspHooks */
 
/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Toggle fields based on current mode.
 */
export default function toggleWebhookEndpointSecret( newMode, oldMode ) {
	var testSecretEl = document.getElementById( 'simpay-settings-keys-test-keys-endpoint-secret' );
	var liveSecretEl = document.getElementById( 'simpay-settings-keys-live-keys-endpoint-secret' );

	if ( 'test' === newMode ) {
		liveSecretEl.parentNode.parentNode.style.display = 'none';
		testSecretEl.parentNode.parentNode.style.display = 'table-row';
	} else {
		testSecretEl.parentNode.parentNode.style.display = 'none';
		liveSecretEl.parentNode.parentNode.style.display = 'table-row';
	}
}

// Show a notice about Webhook status if possible (no Stripe Connect).
//
// Need to wait for the DOM because the script is not enqueued at the end of the page.
// @todo Investigate enqueing admin.js in the footer.
domReady( () => {
	const notice = document.getElementById( 'simpay-webhook-error' );

	if ( ! notice ) {
		return;
	}

	// Show a notice if invalid.
	$.ajax( {
		method: 'GET',
		url: wpApiSettings.root + 'wpsp/v1/webhooks',
		beforeSend: ( xhr ) => {
			xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
		},
	} ).success( ( response ) => {
		if ( ! response.can_handle_events ) {
			notice.classList.add( 'notice' );
			notice.classList.add( 'notice-error' );
			notice.style.display = 'block';
		}
	} );

	// Create webhook automatically.
	document.getElementById( 'simpay-webhook-create' ).addEventListener( 'click', ( e ) => {
		e.preventDefault();

		e.target.classList.add( 'disabled' );

		$.ajax( {
			method: 'POST',
			url: wpApiSettings.root + 'wpsp/v1/webhooks',
			beforeSend: ( xhr ) => {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
		} ).success( ( response ) => {
			if ( response.secret ) {
				document.getElementById( 'simpay-settings-keys-test-keys-endpoint-secret' ).value = response.secret;
				notice.classList.remove( 'notice-error' );
				notice.classList.add( 'notice-success' );
			} else {
				e.target.classList.remove( 'disabled' );
			}

			const createResponse = document.createElement( 'p' );
			createResponse.innerHTML = response.message;
			notice.innerHTML = '';
			notice.appendChild( createResponse );
		} );

	} );
} );
