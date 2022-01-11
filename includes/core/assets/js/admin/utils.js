/* global ClipboardJS */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Uses the ClipboardJS library to copy the shortcode to the clipboard.
 */
function copyToClipboard() {
	const clipboard = new ClipboardJS( '.simpay-copy-button' );

	clipboard.on( 'success', function ( e ) {
		const buttonEl = e.trigger;
		const { copied: copiedText } = buttonEl.dataset;
		const originalText = buttonEl.innerHTML;

		// Show success visual feedback.
		clearTimeout( successTimeout );
		buttonEl.innerHTML = copiedText;

		if ( buttonEl.classList.contains( 'button-secondary' ) ) {
			buttonEl.style.color = 'green';
			buttonEl.style.borderColor = 'green';
		}

		e.clearSelection();

		// Hide success visual feedback after 3 seconds since last success.
		const successTimeout = setTimeout( function () {
			buttonEl.innerHTML = originalText;

			if ( buttonEl.classList.contains( 'button-secondary' ) ) {
				buttonEl.style.color = '';
				buttonEl.style.borderColor = '';
			}

			// Remove the visually hidden textarea so that it isn't perceived by assistive technologies.
			if (
				clipboard.clipboardAction.fakeElem &&
				clipboard.clipboardAction.removeFake
			) {
				clipboard.clipboardAction.removeFake();
			}
		}, 3000 );

		// Handle success audible feedback.
		wp.a11y.speak( copiedText );
	} );
}

domReady( copyToClipboard );
