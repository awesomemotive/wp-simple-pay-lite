/**
 * Payment form data and per-form nonce helpers.
 *
 * Shared by the Lite (core) and Pro UPE bundles, and by the Pro File Upload
 * field, so every request that carries the per-form nonce recovers from an
 * expired nonce the same way.
 *
 * @since 4.17.5
 */

/**
 * REST error codes returned when the per-form nonce (or the REST cookie
 * nonce) fails verification.
 *
 * `simpay_invalid_form_nonce` is returned by the payment routes' nonce check
 * (`PaymentFormNonce::ERROR_CODE`). Other permission failures, such as a
 * generic `rest_forbidden`, are not retried.
 *
 * @since 4.17.5
 *
 * @type {string[]}
 */
const NONCE_ERROR_CODES = [
	'simpay_invalid_form_nonce',
	'rest_cookie_invalid_nonce',
];

/**
 * Returns the payment form's ID.
 *
 * @since 4.17.5
 *
 * @param {HTMLElement} paymentForm Payment form element.
 * @return {number} Form ID, or 0 if unknown.
 */
function getFormId( paymentForm ) {
	if ( paymentForm.formId ) {
		return paymentForm.formId;
	}

	const formId = parseInt( paymentForm.dataset?.simpayFormId, 10 );

	return isNaN( formId ) ? 0 : formId;
}

/**
 * Returns the shared payment form variables (`spGeneral`).
 *
 * `spGeneral` is printed in the footer. Each payment form also carries a copy
 * in its `data-simpay-general` attribute, so a form still loads when the
 * footer script is missing: stripped by an optimization plugin, or not printed
 * yet because the form scripts were moved earlier in the page. The copy then
 * becomes `window.spGeneral`, so every other reader finds it too.
 *
 * @since 4.17.6
 *
 * @param {HTMLElement|null} paymentForm Payment form to read the copy from.
 *                                       Defaults to the first form on the page.
 * @return {Object|null} Shared variables, or null if none are available.
 */
export function getSpGeneral( paymentForm = null ) {
	if ( isPlainObject( window.spGeneral ) ) {
		return window.spGeneral;
	}

	const source = paymentForm?.dataset?.simpayGeneral
		? paymentForm
		: document.querySelector( '[data-simpay-general]' );

	if ( ! source ) {
		return null;
	}

	let spGeneral;

	try {
		spGeneral = JSON.parse( source.dataset.simpayGeneral );
	} catch ( error ) {
		return null;
	}

	if ( ! isPlainObject( spGeneral ) ) {
		return null;
	}

	window.spGeneral = spGeneral;

	return spGeneral;
}

/**
 * Returns the REST API root URL, e.g. `https://example.com/wp-json/`.
 *
 * @since 4.17.6
 *
 * @param {HTMLElement|null} paymentForm Payment form, see getSpGeneral().
 * @return {string} REST API root URL, or an empty string if unknown.
 */
export function getRestUrl( paymentForm = null ) {
	return getSpGeneral( paymentForm )?.strings?.wpJsonUrl || '';
}

/**
 * Fetches a payment form's data from the form-data REST endpoint.
 *
 * Uses `wp.apiFetch` (not a bare fetch) so the request runs in the same
 * authentication context that later verifies the returned per-form nonce.
 *
 * @since 4.17.5
 * @since 4.17.6 Rejects when the REST API URL is unknown, instead of
 *               requesting a relative `undefined…` URL.
 *
 * @param {number} formId Payment form ID.
 * @return {Promise<Object>} Form data.
 */
export function fetchFormData( formId ) {
	const restUrl = getRestUrl();

	if ( ! restUrl ) {
		return Promise.reject(
			new Error( 'The REST API URL for payment forms is not available.' )
		);
	}

	return window.wp
		.apiFetch( {
			url: `${ restUrl }wpsp/__internal__/form-data/${ formId }`,
		} )
		.then( ( data ) => exposeLegacyFormData( formId, data ) );
}

/**
 * Keeps a payment form's data available as `window.simplePayForms[ formId ]`.
 *
 * Before form data was fetched from the REST API, it was printed in the page
 * as the `simplePayForms` global, and custom code may still read it. The
 * object is created as soon as the script loads, and each form's entry is
 * added once its data has been fetched.
 *
 * @since 4.17.5
 *
 * @param {number} formId Payment form ID.
 * @param {Object} data Form data.
 * @return {Object} The form data.
 */
export function exposeLegacyFormData( formId, data ) {
	if ( ! formId || ! data || 'object' !== typeof data ) {
		return data;
	}

	if ( ! isPlainObject( window.simplePayForms ) ) {
		window.simplePayForms = {};
	}

	window.simplePayForms[ formId ] = data;

	return data;
}

/**
 * Determines if a value is a plain object, such as the legacy
 * `simplePayForms` entries or `spGeneral`.
 *
 * @since 4.17.5
 *
 * @param {*} value Value to check.
 * @return {boolean} True if the value is a plain object.
 */
function isPlainObject( value ) {
	return !! value && 'object' === typeof value && ! Array.isArray( value );
}

if ( ! isPlainObject( window.simplePayForms ) ) {
	window.simplePayForms = {};
}

// Make `spGeneral` available as soon as the script loads, for code that reads
// it before the forms are initialized.
getSpGeneral();

/**
 * Copies a payment form's configuration onto its form element.
 *
 * Every key is copied, so keys added by `simpay_form_script_variables`
 * filters still reach the form, except keys that are native element
 * properties (e.g. `id`, `action`, `method`): the form-data response's
 * numeric `id` would otherwise overwrite the element's DOM `id`. The
 * per-form nonce is stored as `simpayNonce`, because `nonce` is the native
 * (CSP) `HTMLElement.nonce` property.
 *
 * @since 4.17.5
 *
 * @param {HTMLElement} paymentForm Payment form element.
 * @param {Object} config Payment form configuration.
 * @return {HTMLElement} The payment form element.
 */
export function applyFormConfig( paymentForm, config ) {
	if ( ! config || 'object' !== typeof config ) {
		return paymentForm;
	}

	const nativeProperties = Object.getPrototypeOf( paymentForm );

	Object.keys( config ).forEach( ( key ) => {
		if ( 'nonce' === key ) {
			if ( config.nonce ) {
				paymentForm.simpayNonce = config.nonce;
			}

			return;
		}

		if ( key in nativeProperties ) {
			return;
		}

		paymentForm[ key ] = config[ key ];
	} );

	return paymentForm;
}

/**
 * Initializes several payment forms at once.
 *
 * Forms are initialized concurrently, so a page with several forms makes its
 * form-data requests in parallel. A form that fails to initialize does not
 * stop the others.
 *
 * @since 4.17.5
 *
 * @param {Iterable<HTMLElement>} paymentForms Payment form elements.
 * @param {Function} initPaymentForm Callback initializing one form; may return a Promise.
 * @return {Promise<PromiseSettledResult[]>} Settled result for each form.
 */
export async function initPaymentFormsConcurrently(
	paymentForms,
	initPaymentForm
) {
	const results = await Promise.allSettled(
		Array.from( paymentForms, ( paymentForm ) =>
			// Wrap so a synchronous throw is also contained to its form.
			Promise.resolve().then( () => initPaymentForm( paymentForm ) )
		)
	);

	results.forEach( ( result ) => {
		if ( 'rejected' === result.status ) {
			// eslint-disable-next-line no-console
			console.error( 'Error initializing payment form:', result.reason );
		}
	} );

	return results;
}

/**
 * Determines if an error means the per-form nonce was rejected.
 *
 * @since 4.17.5
 *
 * @param {*} error Error thrown by `wp.apiFetch`.
 * @return {boolean} True if the error is a nonce failure.
 */
export function isNonceError( error ) {
	return !! error && NONCE_ERROR_CODES.includes( error.code );
}

/**
 * Fetches a fresh per-form nonce and stores it on the payment form.
 *
 * Concurrent callers share one in-flight request.
 *
 * @since 4.17.5
 *
 * @param {HTMLElement} paymentForm Payment form element.
 * @return {Promise<string|null>} The fresh nonce, or null if none could be fetched.
 */
export function refreshFormNonce( paymentForm ) {
	if ( paymentForm.simpayNonceRefresh ) {
		return paymentForm.simpayNonceRefresh;
	}

	const formId = getFormId( paymentForm );

	if ( ! formId ) {
		return Promise.resolve( null );
	}

	paymentForm.simpayNonceRefresh = fetchFormData( formId )
		.then( ( data ) => {
			if ( ! data || ! data.nonce ) {
				return null;
			}

			paymentForm.simpayNonce = data.nonce;

			return data.nonce;
		} )
		.catch( () => null )
		.finally( () => {
			delete paymentForm.simpayNonceRefresh;
		} );

	return paymentForm.simpayNonceRefresh;
}

/**
 * Runs a request that sends the per-form nonce, retrying once with a fresh
 * nonce if the first attempt is rejected for a nonce failure.
 *
 * The per-form nonce is short-lived, so a visitor who keeps a form open long
 * enough would otherwise be unable to pay until they reload the page.
 *
 * @since 4.17.5
 *
 * @param {HTMLElement} paymentForm Payment form element.
 * @param {Function} request Callback receiving the nonce and returning a Promise.
 * @return {Promise<*>} The request's result.
 */
export async function withFormNonce( paymentForm, request ) {
	try {
		return await request( paymentForm.simpayNonce );
	} catch ( error ) {
		if ( ! isNonceError( error ) ) {
			throw error;
		}

		const nonce = await refreshFormNonce( paymentForm );

		if ( ! nonce ) {
			throw error;
		}

		// Retry exactly once; a second failure is returned to the caller.
		return request( nonce );
	}
}

/**
 * Stops a payment form from submitting natively until it is initialized.
 *
 * The payment button is rendered enabled, and the form posts back to the
 * page. Until the form-data request finishes (or when it fails), a click
 * would reload the page instead of paying. Call `markFormReady()` once the
 * form's own submit handler is bound.
 *
 * @since 4.17.5
 *
 * @param {HTMLFormElement} paymentForm Payment form element.
 * @return {HTMLFormElement} The payment form element.
 */
export function preventSubmitUntilReady( paymentForm ) {
	paymentForm.simpayReady = false;

	if ( paymentForm.simpaySubmitGuard ) {
		return paymentForm;
	}

	paymentForm.simpaySubmitGuard = ( event ) => {
		if ( ! paymentForm.simpayReady ) {
			event.preventDefault();
		}
	};

	paymentForm.addEventListener( 'submit', paymentForm.simpaySubmitGuard );

	return paymentForm;
}

/**
 * Marks a payment form as initialized, so its own submit handler takes over.
 *
 * @since 4.17.5
 *
 * @param {HTMLFormElement} paymentForm Payment form element.
 * @return {HTMLFormElement} The payment form element.
 */
export function markFormReady( paymentForm ) {
	paymentForm.simpayReady = true;

	return paymentForm;
}

/**
 * Shows the visitor that a payment form could not be loaded.
 *
 * The message comes from `spGeneral`, which is printed in the page, because
 * the form's own strings are part of the form data that failed to load.
 *
 * @since 4.17.5
 * @since 4.17.6 Reads the message from the form's copy of `spGeneral` when
 *               the footer script is missing.
 *
 * @param {HTMLFormElement} paymentForm Payment form element.
 * @return {boolean} True if the message was shown.
 */
export function showFormLoadError( paymentForm ) {
	// Field-level containers (e.g. the coupon error) also use `.simpay-errors`
	// and can come first, so prefer the form's own generic error container.
	const errorEl =
		paymentForm.querySelector( '.simpay-generic-error' ) ||
		paymentForm.querySelector( '.simpay-errors' );
	const message = getSpGeneral( paymentForm )?.i18n?.formLoadError;

	if ( ! errorEl || ! message ) {
		return false;
	}

	// The message is escaped with esc_html__() on the server, so it is set as
	// HTML: entities such as `&#039;` in translations then display correctly.
	errorEl.innerHTML = message;
	errorEl.style.display = 'block';

	disablePaymentButtons( paymentForm, errorEl );

	if ( window.wp?.a11y?.speak ) {
		window.wp.a11y.speak( message, 'assertive' );
	}

	return true;
}

/**
 * Marks a payment form's buttons as unavailable after its data failed to load.
 *
 * The button is rendered with its "Please wait…" processing text until the
 * form is ready, so without this it would look like it is still loading.
 *
 * The `disabled` state is left alone: `preventSubmitUntilReady()` already
 * stops a click from posting the page, and the Pro button is rendered
 * disabled. `aria-disabled` tells assistive technology it cannot be used.
 *
 * @since 4.17.5
 *
 * @param {HTMLFormElement} paymentForm Payment form element.
 * @param {HTMLElement} errorEl Element holding the load error message.
 */
function disablePaymentButtons( paymentForm, errorEl ) {
	const label = getSpGeneral( paymentForm )?.i18n?.formLoadErrorButton;
	const buttons = paymentForm.querySelectorAll(
		'.simpay-checkout-btn, .simpay-payment-btn'
	);

	buttons.forEach( ( button ) => {
		button.setAttribute( 'aria-disabled', 'true' );

		if ( label ) {
			// Escaped with esc_html__() on the server, like the message.
			( button.querySelector( 'span' ) || button ).innerHTML = label;
		}

		if ( errorEl.id ) {
			button.setAttribute( 'aria-describedby', errorEl.id );
		}
	} );
}
