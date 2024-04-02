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
	const checkboxEl = document.getElementById( '_recaptcha' );

	if ( ! checkboxEl ) {
		return;
	}

	// Launch settings when attempting to check.
	checkboxEl.addEventListener( 'change', ( e ) => {
		e.preventDefault();
		e.target.checked = ! e.target.checked;

		window.open( e.target.dataset.settingsUrl, '_blank' );
	} );
}

/**
 * Provides feedback for email verification.
 */
function emailVerificationFeedback() {
	const checkboxEl = document.getElementById( '_email_verification' );

	if ( ! checkboxEl ) {
		return;
	}

	// Launch settings when attempting to check.
	checkboxEl.addEventListener( 'change', ( e ) => {
		e.preventDefault();
		e.target.checked = ! e.target.checked;

		window.open( e.target.dataset.settingsUrl, '_blank' );
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
		emailVerificationFeedback();
		requireFormTitle();
	}
} );
