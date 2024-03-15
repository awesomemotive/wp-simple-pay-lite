/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Displays Connect error message and/or reloads the page if needed.
 *
 * @param {Object} response JSON response data.
 * @param {string} response.message Error message.
 * @param {boolean} response.reload Whether to reload the page.
 */
function onConnectError( response ) {
	const feedbackEl = document.getElementById(
		'simpay-connect-license-feedback'
	);

	const submitButtonEl = document.getElementById(
		'simpay-connect-license-submit'
	);

	// Toggle feedback.
	if ( response.message ) {
		feedbackEl.innerText = response.message;
		feedbackEl.classList.remove( 'simpay-license-message--valid' );
		feedbackEl.classList.add( 'simpay-license-message--invalid' );
		feedbackEl.style.display = 'block';
	} else {
		feedbackEl.style.display = 'none';
	}

	// Enable submit button again if the page is not reloading.
	if ( ! response.reload ) {
		submitButtonEl.disabled = false;
		submitButtonEl.innerText = submitButtonEl.dataset.connect;
	} else {
		setTimeout( function () {
			window.location.reload();
		}, 2000 );
	}
}

/**
 * Displays Connect error message and/or reloads or redirects the page if needed.
 *
 * @param {Object} response JSON response data.
 * @param {string} response.message Error message.
 * @param {boolean} response.reload Whether to reload the page.
 * @param {string} response.url URL to redirect to.
 */
function onConnectSuccess( response ) {
	const feedbackEl = document.getElementById(
		'simpay-connect-license-feedback'
	);

	// Toggle feedback.
	if ( response.message ) {
		feedbackEl.innerText = response.message;
		feedbackEl.classList.remove( 'simpay-license-message--invalid' );
		feedbackEl.classList.add( 'simpay-license-message--valid' );
		feedbackEl.style.display = 'block';
	} else {
		feedbackEl.style.display = 'none';
	}

	// Redirect if the current page is not being reloaded.
	if ( ! response.reload ) {
		window.location = response.url;
	} else {
		setTimeout( function () {
			window.location.reload();
		}, 2000 );
	}
}

/**
 * Submits the Lite Connect data.
 */
function onConnect() {
	const licenseKeyEl = document.getElementById(
		'simpay-connect-license-key'
	);

	const nonceEl = document.getElementById( 'simpay-connect-license-nonce' );

	const submitButtonEl = document.getElementById(
		'simpay-connect-license-submit'
	);

	// Disable submit.
	submitButtonEl.disabled = true;
	submitButtonEl.innerText = submitButtonEl.dataset.connecting;

	// Get the URL.
	wp.ajax.send( 'simpay_get_connect_url', {
		data: {
			nonce: nonceEl.value,
			key: licenseKeyEl.value,
		},
		// Handle success (redirect).
		success: onConnectSuccess,

		// Handle error (show error).
		error: onConnectError,
	} );
}

/**
 * Binds the Lite Connect form events.
 */
domReady( () => {
	const licenseKeyEl = document.getElementById(
		'simpay-connect-license-key'
	);

	if ( ! licenseKeyEl ) {
		return;
	}

	const submitButtonEl = document.getElementById(
		'simpay-connect-license-submit'
	);

	// Start the process if the "Enter" key is pressed while the license input is focused.
	licenseKeyEl.addEventListener( 'keypress', ( e ) => {
		if ( e.key !== 'Enter' ) {
			return;
		}

		e.preventDefault();
		onConnect();
	} );

	// Start the process if the submit button is clicked.
	submitButtonEl.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		submitButtonEl.disabled = true;
		onConnect();
	} );
} );
