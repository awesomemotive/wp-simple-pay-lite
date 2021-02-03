/* global simpayGoogleRecaptcha, grecaptcha, jQuery */

const { siteKey, i18n } = simpayGoogleRecaptcha;

// Create a reCAPTCHA token when the form is loaded.
jQuery( document ).on( 'simpayCoreFormVarsInitialized', function( e, spFormElem, formData ) {
	const {
		debugLog,
	} = window.spShared;

	const {
		enableForm,
		disableForm,
		showError,
	} = window.simpayApp;

	function onError( spFormElem, formData ) {
		showError( spFormElem, formData, i18n.invalid );
		spFormElem.find( ':not(.simpay-errors)' ).remove();
	}

	try {
		grecaptcha.ready( () => {
			// Disable form while we generate a token.
			disableForm( spFormElem, formData, true );

			try {
				// Validate on initial load.
				grecaptcha.execute( siteKey, {
					action: `simple_pay_form_${ formData.formId }_source`,
				} )
					.then( ( token ) => {
						wp.ajax.send( 'simpay_validate_recaptcha_source', {
							data: {
								token,
								recaptcha_action: `simple_pay_form_${ formData.formId }_source`,
							},
							/**
							 * Enable form on success.
							 *
							 * @since 3.7.1
							 */
							success() {
								enableForm( spFormElem, formData );
							},
							/**
							 * Show error message on error.
							 *
							 * @since 3.7.1
							 */
							error: () => onError( spFormElem, formData ),
						} );
					} )
					.catch( () => onError( spFormElem, formData ) );
			} catch {
				onError( spFormElem, formData );
			}
		} );
	} catch {
		onError( spFormElem, formData );
	}
} );
