/* global grecaptcha */

/**
 * Internal dependencies
 */
import {
	applyFormConfig,
	fetchFormData,
	initPaymentFormsConcurrently,
	markFormReady,
	preventSubmitUntilReady,
	showFormLoadError,
	withFormNonce,
} from './form-data.js';

/**
 * Returns a Promise that resolves to the token for the given payment form.
 *
 * @since 4.7.0
 *
 * @param {HTMLFormElement} paymentForm Payment form.
 * @return {Promise<string>} Promise that resolves to the token.
 */
function getPaymentFormToken( paymentForm ) {
	// reCAPTCHA v3.
	if ( window.simpayGoogleRecaptcha ) {
		const { siteKey } = window.simpayGoogleRecaptcha;

		return new Promise( ( resolve ) => {
			grecaptcha
				.execute( siteKey, {
					action: 'simpay_payment',
				} )
				.then( ( reCaptchaToken ) => resolve( reCaptchaToken ) )
				.catch( () => resolve( null ) );
		} );
	}

	// hCaptcha.
	const hCaptchaTokenEl = paymentForm.querySelector(
		'[name="h-captcha-response"]'
	);

	if ( hCaptchaTokenEl ) {
		return Promise.resolve( hCaptchaTokenEl.value );
	}

	// Cloudflare Turnstile.
	const cloudflareTurnstileTokenEl = paymentForm.querySelector(
		'[name="cf-turnstile-response"]'
	);

	if ( cloudflareTurnstileTokenEl ) {
		return Promise.resolve( cloudflareTurnstileTokenEl.value );
	}

	return Promise.resolve( null );
}

/**
 * Submits a payment form.
 *
 * @since 4.7.0
 *
 * @param {Event} event Submit event.
 */
function submitPaymentForm( event ) {
	// Do not submit the form.
	event.preventDefault();
	const { target: paymentForm } = event;
	const { formId, i18n } = paymentForm;

	// Find the submit button and disable it.
	const submitButtonEl = paymentForm.querySelector( '.simpay-payment-btn' );
	const submitButtonTextEl = submitButtonEl.querySelector( 'span' );
	const submitButtonToRestore = submitButtonEl.innerHTML;

	submitButtonEl.disabled = true;
	submitButtonTextEl.innerText = i18n.paymentButtonLoadingText;

	// Find the errors and remove any previous errors.
	const errorsEl = paymentForm.querySelector( '.simpay-errors' );
	errorsEl.innerHTML = '';

	// ...submit.
	getPaymentFormToken( paymentForm )
		.then( ( token ) => {
			// Use the global wp.apiFetch so the middlewares are used.
			// Our current webpack config is not setup to extract the
			// dependencies automatically.
			//
			// Send the per-form nonce from the form-data response so the
			// route's permission_callback can verify it, refreshing it once
			// if it has expired.
			return withFormNonce( paymentForm, ( nonce ) =>
				window.wp.apiFetch( {
					url:
						window.spGeneral.strings.wpJsonUrl +
						'wpsp/__internal__/payment/create',
					method: 'POST',
					data: {
						token,
						form_id: formId,
						price_id: Object.values(
							paymentForm.settings.prices
						)[ 0 ].id,
						quantity: 1,
						_wpnonce: nonce,
					},
				} )
			).then( ( { redirect } ) => {
				window.location.href = redirect;
			} );
		} )
		.catch( ( { message } ) => {
			submitButtonEl.disabled = false;
			errorsEl.innerHTML = message;
			submitButtonEl.innerHTML = submitButtonToRestore;
			wp.a11y.speak( message, 'assertive' );
		} );
}

/**
 * Initializes a payment form.
 *
 * @since 4.7.0
 * @since 4.17.5 Fetches the configuration from the form-data REST route, and
 *               blocks native submission until the form is initialized. A
 *               form that fails to load or initialize shows an error.
 *
 * @param {Object} paymentForm Payment form.
 * @param {Object} config Payment form configuration.
 * @return {Promise<void>} Resolves once the form is initialized, or could not be.
 */
export async function initPaymentForm( paymentForm, config = false ) {
	paymentForm.formId = parseInt( paymentForm.dataset.simpayFormId );

	// The payment button is usable before the form data arrives. Keep it
	// from posting the page back until the form is ready.
	preventSubmitUntilReady( paymentForm );

	// Bootstrap the config from REST API if not provided.
	if ( ! config ) {
		try {
			config = await fetchFormData( paymentForm.formId );
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error fetching form data:', error );
			showFormLoadError( paymentForm );
			return;
		}
	}

	try {
		// Merge config into paymentForm, without overwriting native element
		// properties such as `id`.
		applyFormConfig( paymentForm, config );
	} catch ( error ) {
		// A throw leaves the form blocked, so tell the visitor rather than
		// leaving a payment button that silently does nothing.
		// eslint-disable-next-line no-console
		console.error( 'Error initializing payment form:', error );
		showFormLoadError( paymentForm );
		return;
	}

	// Bind submission event.
	paymentForm.addEventListener( 'submit', submitPaymentForm );

	markFormReady( paymentForm );
}

/**
 * Initializes Payment Forms on the current page.
 *
 * @since 4.7.0
 */
async function initPaymentForms() {
	// Find all payment forms on the page.
	const paymentFormEls = document.querySelectorAll( '.simpay-checkout-form' );

	if ( 0 === paymentFormEls.length ) {
		return;
	}

	// Setup every payment form at once, so their form-data requests run in
	// parallel.
	await initPaymentFormsConcurrently( paymentFormEls, ( paymentForm ) =>
		initPaymentForm( paymentForm )
	);
}

window.document.addEventListener( 'DOMContentLoaded', initPaymentForms );

window.wpsp = {
	initPaymentForm,
};
