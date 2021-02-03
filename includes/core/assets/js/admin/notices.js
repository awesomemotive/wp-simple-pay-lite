/* global jQuery, wp */

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
	jQuery( '.simpay-notice' ).each( function() {
		const notice = $( this );
		const noticeId = notice.data( 'id' );
		const nonce = notice.data( 'nonce' );

		notice.on( 'click', '.notice-dismiss', ( e ) => {
			wp.ajax.post( 'simpay_dismiss_admin_notice', {
				notice_id: noticeId,
				nonce,
			} );
		} );
	} );
} );
