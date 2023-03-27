/* global simpayGoogleRecaptcha, grecaptcha */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

const settingsToToggle = [
	'.simpay-settings-hcaptcha_setup',
	'.simpay-settings-hcaptcha_site_key',
	'.simpay-settings-hcaptcha_secret_key',

	'.simpay-settings-recaptcha_setup',
	'.simpay-settings-recaptcha_site_key',
	'.simpay-settings-recaptcha_secret_key',
	'.simpay-settings-recaptcha_score_threshold',

	'.simpay-settings-cloudflare_turnstile_setup',
	'.simpay-settings-cloudflare_turnstile_site_key',
	'.simpay-settings-cloudflare_turnstile_secret_key',

	'.simpay-settings-no_captcha_warning',
];

/**
 * Handles toggling the different CAPTCHA type settings.
 *
 * @since 4.6.6
 *
 * @param {Event} e Change event.
 * @return {void}
 */
function onToggle( e ) {
	const value = e.target.value;
	let type;

	switch ( value ) {
		case 'none':
			type = 'no_captcha';
			break;
		case 'recaptcha-v3':
			type = 'recaptcha';
			break;
		case 'cloudflare-turnstile':
			type = 'cloudflare_turnstile';
			break;
		default:
			type = value;
	}

	// Hide everything if using none, and show notice.
	settingsToToggle.forEach( ( setting ) => {
		const settingEl = document.querySelector( setting );

		if ( ! settingEl ) {
			return;
		}

		settingEl.style.display = setting.includes( type )
			? 'table-row'
			: 'none';
	} );
}

/**
 * Displays reCAPTCHA configuration feedback.
 *
 * @since 4.6.6
 *
 * @return {void}
 */
function reCaptchaFeedback() {
	const feedbackNoticeEl = document.querySelector(
		'.simpay-recaptcha-feedback'
	);

	if ( ! feedbackNoticeEl ) {
		return;
	}

	const { siteKey, i18n } = simpayGoogleRecaptcha;

	if ( '' === siteKey ) {
		return;
	}

	/**
	 * Show the error notice.
	 */
	function onError() {
		feedbackNoticeEl.style.display = 'block';
		feedbackNoticeEl.classList.add( 'notice-error' );
		feedbackNoticeEl.innerHTML = '';

		const p = document.createElement( 'p' );
		p.innerText = i18n.invalid;

		feedbackNoticeEl.appendChild( p );
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
 * DOM ready.
 */
domReady( () => {
	const toggleEls = document.querySelectorAll(
		'input[name="simpay_settings[captcha_type]"]'
	);

	if ( toggleEls.length === 0 ) {
		return;
	}

	// Hide all settings initially.
	settingsToToggle.forEach( ( settingRow ) => {
		const settingRowEl = document.querySelector( settingRow );

		if ( ! settingRowEl ) {
			return;
		}

		settingRowEl.style.display = 'none';
	} );

	// Attach toggles to type buttons.
	toggleEls.forEach( ( toggleEl ) =>
		toggleEl.addEventListener( 'change', onToggle )
	);

	// Trigger a change event on an already selected type.
	const selectedType = document.querySelector(
		'input[name="simpay_settings[captcha_type]"]:checked'
	);

	if ( selectedType ) {
		selectedType.dispatchEvent( new Event( 'change' ) );
	}

	reCaptchaFeedback();
} );
