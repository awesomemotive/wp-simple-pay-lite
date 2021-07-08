/* global jQuery, userSettings */

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
 *
 * Uses jQuery because the `.notice-dismiss` button is added to the DOM
 * via jQuery when the notice loads.
 */
domReady( () => {
	jQuery( '.simpay-notice' ).each( function () {
		const notice = jQuery( this );
		const noticeId = notice.data( 'id' );
		const nonce = notice.data( 'nonce' );
		const lifespan = notice.data( 'lifespan' );

		notice.on( 'click', '.notice-dismiss, .simpay-notice-dismiss', () => {
			wp.ajax.send( 'simpay_dismiss_admin_notice', {
				data: {
					notice_id: noticeId,
					nonce,
					lifespan,
				},
				success() {
					notice.slideUp( 'fast' );

					// Remove previously set "seen" local storage.
					const { uid = 0 } = userSettings;
					const seenKey = `simpay-notice-${ noticeId }-seen-${ uid }`;
					window.localStorage.removeItem( seenKey );
				},
			} );
		} );
	} );

	// Move "Top of Page" promos to the top of content (before Help/Screen Options).
	const topOfPageNotice = jQuery( '.simpay-admin-notice-top-of-page' );

	if ( topOfPageNotice.length > 0 ) {
		const topOfPageNoticeEl = topOfPageNotice.detach();

		jQuery( '#wpbody-content' ).prepend( topOfPageNoticeEl );

		const { uid = 0 } = userSettings;
		const noticeId = topOfPageNoticeEl.data( 'id' );
		const seenKey = `simpay-notice-${ noticeId }-seen-${ uid }`;

		if ( window.localStorage.getItem( seenKey ) ) {
			topOfPageNoticeEl.show();
		} else {
			setTimeout( () => {
				window.localStorage.setItem( seenKey, true );
				topOfPageNotice.slideDown();
			}, 1500 );
		}
	}
} );
