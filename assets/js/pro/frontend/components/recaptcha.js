/* global spShared, simpayAppPro, simpayGoogleRecaptcha, grecaptcha, jQuery */

const { siteKey } = simpayGoogleRecaptcha;

// Create a reCAPTCHA token when the form is loaded.
jQuery( document ).on( 'simpayBindCoreFormEventsAndTriggers', 'body', function( e, spFormElem, formData ) {
	grecaptcha.ready( () => {
		// Disable form while we generate a token.
		window.simpayAppPro.disableForm( spFormElem, formData, true );

		try {
			grecaptcha.execute( siteKey, {
				action: `simple_pay_form_${ formData.formId }`,
			} )
				.then( ( token ) => {
					// Token could not be generated, do not attempt to validate the form.
					if ( ! token ) {
						return;
					}

					const inputEl = document.createElement( 'input' );
					inputEl.name  = 'simpay_recaptcha_response';
					inputEl.type  = 'hidden';
					inputEl.value = token;

					// Append token.
					spFormElem.append( inputEl );

					// Enable form.
					window.simpayAppPro.enableForm( spFormElem, formData );
				} );
		} catch {
			// Enable form.
			window.simpayAppPro.enableForm( spFormElem, formData );
			window.spShared.debugLog( 'Your payment form will not be checked for robots:', '' );
			window.spShared.debugLog( 'Unable to generate reCAPTCHA token. Please ensure you are using v3 of the reCAPTCHA and you have entered valid keys in Simple Pay > Settings > General.', '' );
		}
	} );
} );
