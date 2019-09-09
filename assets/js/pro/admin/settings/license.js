/* global $, ajaxurl */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

const COLOR_RED = '#a00';
const COLOR_GREEN = '#00800e';

/**
 * Handle AJAX license activation and deactivation.
 *
 * @todo Use React.
 *
 * Need to wait for the DOM because the script is not enqueued at the end of the page.
 */
domReady( () => {
	const inputEl = document.getElementById( 'simpay-settings-license-key-license-key' );
	const activateButtonEl = document.getElementById( 'simpay-activate-license' );
	const deactivateButtonEl = document.getElementById( 'simpay-deactivate-license' );
	const resultEl = document.getElementById( 'simpay-license-message' );
	const nonceEl = document.getElementById( 'simpay-license-nonce' );

	// Do nothing if we can't find our input (this is bundled with all other admin scripts).
	if ( ! inputEl ) {
		return;
	}

	/**
	 * Make a request to admin-ajax.php
	 *
	 * Error handling is done via try/catch when dealing with the Promise.
	 *
	 * @param {string} action AJAX action.
	 * @param {Object} options Additional options to pass to the AJAX request.
	 * @return {Promise}
	 */
	function adminAjax( action, options ) {
		return wp.ajax.post( action, {
			nonce: nonceEl.value,
			...options,
		} );
	}

	/**
	 * Activate a license.
	 *
	 * @param {string} license License to verify.
	 * @return {Promise}
	 */
	function activateLicense( license ) {
		return adminAjax( 'simpay_activate_license', {
			license,
		} );
	}

	/**
	 * Deactivate a license.
	 *
	 * @param {string} license License to verify.
	 * @return {Promise}
	 */
	function deactivateLicense( license ) {
		return adminAjax( 'simpay_deactivate_license', {
			license,
		} );
	}

	/**
	 * Handle the use of the "Activate" button.
	 * 
	 * @param {Event} e Event.
	 */
	async function onActivateLicense( e ) {
		if ( e ) {
			e.preventDefault();
		}

		// Do nothing if there is no value.
		if ( '' === inputEl.value ) {
			return;
		}

		activateButtonEl.disabled = true;
		activateButtonEl.innerText = activateButtonEl.dataset.busyLabel;

		try {
			const activate = await activateLicense( inputEl.value );

			const {
				message,
				license_data: licenseData,
			} = activate;

			resultEl.innerHTML = message;

			resultEl.style.color = COLOR_GREEN;
			activateButtonEl.innerText = activateButtonEl.dataset.activateLabel;

			deactivateButtonEl.disabled = false;

			// Hide Subscriptions upgrade if they are using the correct license.
			if ( '1' !== licenseData.price_id ) {
				const upgradeNag = document.getElementById( 'simpay-license-upgrade' );

				if ( upgradeNag ) {
					upgradeNag.style.display = 'none';
				}
			}

			// Hide bubble nag.
			const bubble = document.getElementById( 'simpay-settings-bubble' );

			if ( bubble ) {
				bubble.style.display = 'none';
			}
		} catch ( error ) {
			resultEl.style.color = COLOR_RED;

			if ( error.message ) {
				resultEl.innerHTML = error.message;
			} else {
				resultEl.innerHTML = resultEl.dataset.errorLabel;
			}

			activateButtonEl.disabled = false;
			activateButtonEl.innerHTML = activateButtonEl.dataset.activateLabel;

			inputEl.disabled = false;
		}
	}

	/**
	 * Handle the use of the "Deactivate" button.
	 * 
	 * @param {Event} e Event.
	 */
	async function onDeactivateLicense( e ) {
		if ( e ) {
			e.preventDefault();
		}

		activateButtonEl.disabled = true;
		deactivateButtonEl.disabled = true;

		try {
			const deactivate = await deactivateLicense( inputEl.value );
			const { message } = deactivate;

			resultEl.innerHTML = '';

			activateButtonEl.innerText = activateButtonEl.dataset.activateLabel;
			activateButtonEl.disabled = false;

			inputEl.disabled = false;
			inputEl.value = '';

			deactivateButtonEl.disabled = true;
		} catch ( error ) {
			resultEl.style.color = COLOR_RED;
			resultEl.innerHTML = resultEl.dataset.errorLabel;

			deactivateButtonEl.disabled = false;
		}
	}

	activateButtonEl.addEventListener( 'click', onActivateLicense );
	deactivateButtonEl.addEventListener( 'click', onDeactivateLicense );

	// Validate on load.
	if ( '' !== inputEl.value ) {
		return onActivateLicense();
	} else {
		inputEl.disabled = false;

		activateButtonEl.disabled = false;
		activateButtonEl.innerText = activateButtonEl.dataset.activateLabel;
	}
} );
