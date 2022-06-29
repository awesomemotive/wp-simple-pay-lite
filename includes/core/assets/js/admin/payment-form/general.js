/* global simpayAdmin, grecaptcha, simpayGoogleRecaptcha, jQuery */

/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { maybeBlockSelectWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Alerts the user when they are about to leave unsaved changes.
 *
 * @since 4.4.1
 *
 * @param {string} initialValues Serialized form initial values.
 */
function onLeavePage( initialValues ) {
	/**
	 * Alerts the user when they are about to leave unsaved changes.
	 *
	 * @since 4.4.1
	 *
	 * @param {Event} event beforeunload event.
	 * @return {string} Message to display in the browser's confirmation dialog (when supported).
	 */
	function confirmLeave( event ) {
		const newFormValues = serialize(
			document.querySelector( '.post-type-simple-pay form#post' ),
			{ hash: true }
		);
		delete newFormValues.simpay_form_settings_tab;
		delete newFormValues.simpay_save_preview;

		if ( JSON.stringify( newFormValues ) !== initialValues ) {
			event.preventDefault();

			// The return string is needed for browser compat.
			// See https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeunload_event.
			return simpayAdmin.i18n.leavePageConfirm;
		}
	}

	// eslint-disable-next-line @wordpress/no-global-event-listener
	window.addEventListener( 'beforeunload', confirmLeave );
	window.onbeforeunload = confirmLeave;

	// Use jQuery to match WordPress core.
	jQuery( '.post-type-simple-pay form#post' )
		.off( 'submit' )
		.on( 'submit', function () {
			// eslint-disable-next-line @wordpress/no-global-event-listener
			window.removeEventListener( 'beforeunload', confirmLeave );
			window.onbeforeunload = null;
		} );
}

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
 * Handles product education when attempting configuring a form type in Lite.
 *
 * @since 4.4.7
 */
function formType() {
	const selector = document.getElementById( 'form-type-select' );
	const overlay = document.getElementById( 'is-overlay' );

	selector.addEventListener( 'change', function ( e ) {
		const {
			target: { options, selectedIndex },
		} = e;
		const option = options[ selectedIndex ];
		const type = option.value;

		if ( 'on-site' === type ) {
			maybeBlockSelectWithUpgradeModal( e );
		}

		if ( 'off-site' === type ) {
			overlay.querySelector( 'input' ).checked = false;
		}

		overlay.style.display =
			'yes' === option.dataset.available && 'on-site' === type
				? 'block'
				: 'none';
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	const formSettings = document.querySelector(
		'.post-type-simple-pay #post'
	);

	if ( formSettings ) {
		const formValues = serialize( formSettings, { hash: true } );
		delete formValues.simpay_form_settings_tab;
		onLeavePage( JSON.stringify( formValues ) );

		formType();
		reCaptchaFeedback();
		requireFormTitle();
	}
} );
