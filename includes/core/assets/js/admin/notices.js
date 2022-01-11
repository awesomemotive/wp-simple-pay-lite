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
 * Opens the "Upgrade to Pro" menu item in a new tab.
 */
function upgradeToProLink() {
	const upgradeLinkEl = document.querySelector(
		'#adminmenu a[href^="https://wpsimplepay.com/lite-vs-pro"]'
	);

	if ( ! upgradeLinkEl ) {
		return;
	}

	upgradeLinkEl.setAttribute( 'target', '_blank' );
}

/**
 * Appends a notification bubble to the parent menu item if one exists in the admin bar.
 */
function menuNameBubble() {
	const adminBarMenuBubbleEl = document.querySelector(
		'.simpay-admin-bar-test-mode .simpay-settings-bubble'
	);

	// No bubble is present, do nothing.
	if ( ! adminBarMenuBubbleEl ) {
		return;
	}

	const menuItemEl = document.querySelector(
		'#menu-posts-simple-pay .wp-menu-name'
	);

	// No menu item is present, do nothing.
	if ( ! menuItemEl ) {
		return;
	}

	const newBubble = adminBarMenuBubbleEl.cloneNode( true );
	newBubble.innerHTML = '';

	// Append the bubble to the admin menu item.
	menuItemEl.appendChild( newBubble );
}

/**
 * Binds navigation buttons/links in the "Five Star Rating" admin notice.
 */
function fiveStarRatingNotice() {
	const steps = document.querySelectorAll(
		'.simpay-admin-notice-five-star-rating'
	);

	steps.forEach( ( stepEl ) => {
		const navigationEls = stepEl.querySelectorAll( '[data-navigate]' );

		if ( ! navigationEls ) {
			return;
		}

		navigationEls.forEach( ( navigationEl ) => {
			navigationEl.addEventListener( 'click', ( { target } ) => {
				const step = target.dataset.navigate;
				const stepToShow = document.querySelector(
					`.simpay-admin-notice-five-star-rating[data-step="${ step }"]`
				);
				const stepsToHide = document.querySelectorAll(
					`.simpay-admin-notice-five-star-rating:not([data-step="${ step }"])`
				);

				if ( stepToShow ) {
					stepToShow.style.display = 'block';
				}

				if ( stepsToHide.length > 0 ) {
					stepsToHide.forEach( ( stepToHide ) => {
						stepToHide.style.display = 'none';
					} );
				}
			} );
		} );
	} );
}

/**
 * Handle AJAX dismissal of notices.
 *
 * Uses jQuery because the `.notice-dismiss` button is added to the DOM
 * via jQuery when the notice loads.
 */
domReady( () => {
	fiveStarRatingNotice();
	upgradeToProLink();
	menuNameBubble();

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
