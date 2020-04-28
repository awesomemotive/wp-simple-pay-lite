/* global _ */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies.
 */
import './settings/usage-tracking.js';

/**
 * Handle AJAX dismissal of notices.
 */
domReady( () => {
	const simpayNotices = document.querySelectorAll( '.simpay-notice' );

	if ( simpayNotices.length === 0 ) {
		return;
	}

	_.each( simpayNotices, ( notice ) => {
		const dismissEl = notice.querySelector( '.notice-dismiss' );

		if ( ! dismissEl ) {
			return;
		}

		const noticeId = notice.dataset.id;
		const nonce = notice.dataset.nonce;

		dismissEl.addEventListener( 'click', ( e ) => {
			wp.ajax.post( 'simpay_dismiss_admin_notice', {
				notice_id: noticeId,
				nonce,
			} );
		} );
	} );
} );
