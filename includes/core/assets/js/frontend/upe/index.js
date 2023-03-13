/* global grecaptcha */

const forms = window.simplePayForms;

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
			return window.wp
				.apiFetch( {
					path: 'wpsp/__internal__/payment/create',
					method: 'POST',
					data: {
						token,
						form_id: formId,
						price_id: Object.values(
							paymentForm.settings.prices
						)[ 0 ].id,
						quantity: 1,
					},
				} )
				.then( ( { redirect } ) => {
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
 *
 * @param {Object} paymentForm Payment form.
 * @param {Object} config Payment form configuration.
 */
function initPaymentForm( paymentForm, config = false ) {
	paymentForm.formId = parseInt( paymentForm.dataset.simpayFormId );

	// Bootstrap the config from the page if not provided.
	if ( ! config ) {
		config = forms[ paymentForm.formId ];
	}

	paymentForm = Object.assign( paymentForm, config );

	// Bind submission event.
	paymentForm.addEventListener( 'submit', submitPaymentForm );
}

/**
 * Initializes Payment Forms on the current page.
 *
 * @since 4.7.0
 */
function initPaymentForms() {
	// Find all payment forms on the page.
	const paymentFormEls = document.querySelectorAll( '.simpay-checkout-form' );

	if ( 0 === paymentFormEls.length ) {
		return;
	}

	// Setup each payment form.
	paymentFormEls.forEach( ( paymentForm ) => {
		initPaymentForm( paymentForm );
	} );
}

window.document.addEventListener( 'DOMContentLoaded', initPaymentForms );

window.wpsp = {
	initPaymentForm,
};
