/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * DOM ready.
 */
domReady( () => {
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
		feedbackNoticeEl.querySelector( 'p' ).innerText = i18n.invalid;
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
} );
