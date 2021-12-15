/* global simpayAdmin, grecaptcha, simpayGoogleRecaptcha */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Provides feedback to reCAPTCHA configuration.
 */
function reCaptchaFeedback() {
	const feedbackNoticeEl = document.querySelector(
		'.simpay-recaptcha-payment-form-feedback'
	);

	if ( ! feedbackNoticeEl ) {
		return;
	}

	const feedbackNoticeElDesc = document.querySelector(
		'.simpay-recaptcha-payment-form-description'
	);
	const { siteKey, i18n } = simpayGoogleRecaptcha;

	feedbackNoticeElDesc.style.display = 'none';

	function onError() {
		feedbackNoticeEl.style.color = '#b32d2e';
		feedbackNoticeEl.style.display = 'inline';
		feedbackNoticeEl.innerHTML = i18n.disabled;

		feedbackNoticeElDesc.style.display = 'inline-block';
	}

	function onSuccess() {
		feedbackNoticeEl.style.color = 'green';
		feedbackNoticeEl.style.display = 'inline';
		feedbackNoticeEl.innerHTML = i18n.enabled;

		feedbackNoticeElDesc.style.display = 'inline-block';
	}

	if ( '' === siteKey ) {
		return onError();
	}

	grecaptcha.ready( () => {
		const badge = document.querySelector( '.grecaptcha-badge' );

		if ( badge ) {
			badge.style.display = 'none';
		}

		try {
			grecaptcha
				.execute( siteKey, {
					action: `simple_pay_admin_test`,
				} )
				.then( ( token ) => {
					wp.ajax.send( 'simpay_validate_recaptcha_source', {
						data: {
							token,
							recaptcha_action: 'simple_pay_admin_test',
						},
						success: onSuccess,
						error: onError,
					} );
				} )
				.catch( onError );
		} catch ( error ) {
			onError();
		}
	} );
}

/**
 * Provides feebdack to Title requirement.
 */
function requireFormTitle() {
	const formTitleInput = document.querySelector( '#_company_name' );

	if ( ! formTitleInput ) {
		return;
	}

	/**
	 * Appends an error message if the input is empty and refills with the site title.
	 */
	formTitleInput.addEventListener( 'blur', function () {
		if ( formTitleInput.value.length === 0 ) {
			formTitleInput.nextElementSibling.classList.remove( 'hidden' );
			formTitleInput.value = simpayAdmin.siteTitle;
		} else {
			formTitleInput.nextElementSibling.classList.add( 'hidden' );
		}
	} );

	formTitleInput.addEventListener( 'focus', function () {
		if ( formTitleInput.value.length !== 0 ) {
			formTitleInput.nextElementSibling.classList.add( 'hidden' );
		}
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	reCaptchaFeedback();
	requireFormTitle();
} );
