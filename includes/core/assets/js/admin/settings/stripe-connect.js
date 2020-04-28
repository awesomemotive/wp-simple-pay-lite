/* global wp, _, wpspHooks */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Toggle fields based on current mode.
 */
export default function toggleStripeConnectNotice( newMode, oldMode ) {
	// Only how a notice when the mode changes.
	if ( newMode === oldMode ) {
		return;
	}

	const notice = document.getElementById( 'simpay-test-mode-toggle-notice' );
	const statusText = document.getElementById( 'simpay-toggle-notice-status' );
	const statusLink = document.getElementById( 'simpay-toggle-notice-status-link' );

	notice.classList.add( 'notice' );
	notice.classList.add( 'notice-warning' );
	notice.style.display = 'block';

	if ( ! statusText || ! statusLink ) {
		return;
	}

	statusText.innerHTML = '<strong>' + statusText.dataset[ newMode ] + '</strong>';
	statusLink.href = statusLink.dataset[ newMode ];
}

/**
 * Shows the currently connected Stripe account's email address.
 */
domReady( () => {
	const containerEl = document.getElementById( 'simpay-stripe-account-info' );

	if ( ! containerEl ) {
		return;
	}

	wp.ajax.send( 'simpay_stripe_connect_account_information', {
		data: {
			nonce: containerEl.dataset.nonce,
			account_id: containerEl.dataset.accountId,
		},
		success: ( response ) => {
			containerEl.querySelector( 'p' ).innerHTML = response.message;
			containerEl.style.display = 'block';
			containerEl.classList.add( 'notice' );

			if ( 'simpay-stripe-activated-account-actions' === response.actions ) {
				containerEl.classList.add( 'notice-info' );
			} else {
				containerEl.classList.add( 'notice-warning' );
			}

			const actionsEl = document.getElementById( response.actions );

			if ( actionsEl ) {
				actionsEl.style.display = 'block';
			}
		},
		error: ( response ) => {
			containerEl.querySelector( 'p' ).innerHTML = response.message;
			containerEl.style.display = 'block';
			containerEl.classList.add( 'notice' );
			containerEl.classList.add( 'notice-error' );

			const actionsEl = document.getElementById( response.actions );

			if ( actionsEl ) {
				actionsEl.style.display = 'block';
			}
		},
	} );
} );
