/* global jQuery, simpayAdmin */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Toggle fields based on current mode.
 *
 * @param {string} newMode The new mode.
 * @param {string} initialMode The initial mode.
 */
export function toggleStripeConnectNotice( newMode, initialMode ) {
	const notice = document.getElementById( 'simpay-test-mode-toggle-notice' );

	if ( ! notice ) {
		return;
	}

	// Only show a notice when the mode changes.
	if ( newMode === initialMode ) {
		notice.style.display = 'none';
		return;
	}

	const statusText = document.getElementById( 'simpay-toggle-notice-status' );
	const statusLink = document.getElementById(
		'simpay-toggle-notice-status-link'
	);

	notice.style.display = 'block';

	if ( ! statusText || ! statusLink ) {
		return;
	}

	statusText.innerHTML =
		'<strong>' + statusText.dataset[ newMode ] + '</strong>';
	statusLink.href = statusLink.dataset[ newMode ];
}

/**
 * Outputs connected Stripe account information.
 */
function accountInfo() {
	const containerEl = document.getElementById( 'simpay-stripe-account-info' );

	if ( ! containerEl ) {
		return;
	}

	wp.ajax.send( 'simpay_stripe_connect_account_information', {
		data: {
			nonce: containerEl.dataset.nonce,
		},
		success: ( response ) => {
			containerEl.querySelector( 'p' ).innerHTML = response.message;
			containerEl.style.display = 'block';

			if (
				'simpay-stripe-activated-account-actions' === response.actions
			) {
				containerEl.classList.add( 'notice-info' );
			} else {
				containerEl.classList.add( 'notice-warning' );
			}

			const actionsEl = document.getElementById( response.actions );

			if ( actionsEl ) {
				actionsEl.style.display = 'block';
			}

			disconnectLink();
		},
		error: ( response ) => {
			containerEl.querySelector( 'p' ).innerHTML = response.message;
			containerEl.style.display = 'block';
			containerEl.classList.add( 'notice-error' );

			const actionsEl = document.getElementById( response.actions );

			if ( actionsEl ) {
				actionsEl.style.display = 'block';
				disconnectLink();
			}
		},
	} );
}

/**
 * Handles the Stripe Disconnect link and confirmation.
 */
function disconnectLink() {
	const disconnectLinkEls = document.querySelectorAll(
		'.simpay-disconnect-link'
	);

	if ( ! disconnectLinkEls ) {
		return;
	}

	const { i18n } = simpayAdmin;
	const { disconnectConfirm, disconnectCancel } = i18n;

	disconnectLinkEls.forEach( function ( el ) {
		el.addEventListener( 'click', ( event ) => {
			event.preventDefault();

			jQuery( '.simpay-disconnect-confirm' ).dialog( {
				resizable: false,
				height: 'auto',
				width: 400,
				modal: true,
				draggable: false,
				open() {
					jQuery( '.ui-dialog-buttonset .ui-button' )
						.removeClass( 'ui-button' )
						.last()
						.css( {
							marginLeft: '10px',
						} )
						.focus();
				},
				buttons: [
					{
						text: disconnectCancel,
						click() {
							jQuery( this ).dialog( 'close' );
						},
						class: 'button button-secondary',
					},
					{
						text: disconnectConfirm,
						click() {
							window.location.href = el.href;
						},
						class: 'button button-primary',
					},
				],
			} );
		} );
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	accountInfo();
} );
