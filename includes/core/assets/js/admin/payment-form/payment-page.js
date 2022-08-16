/* global $ */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { maybeBlockCheckboxWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Promopts for upgrade if enabling Payment Pages with an incorrect license.
 *
 * @since 4.5.0
 */
function bindEnable() {
	const enableEl = document.querySelector( '[name="_enable_payment_page"]' );

	if ( ! enableEl ) {
		return;
	}

	enableEl.addEventListener( 'change', maybeBlockCheckboxWithUpgradeModal );
}

/**
 * Promopts for upgrade if disabling with an incorrect license.
 *
 * @since 4.5.0
 */
function bindHideBranding() {
	const hideEl = document.querySelector(
		'[name="_payment_page_powered_by"]'
	);

	if ( ! hideEl ) {
		return;
	}

	hideEl.addEventListener( 'change', maybeBlockCheckboxWithUpgradeModal );
}

/**
 * Bind color picker.
 *
 * @since 4.5.0
 */
function bindColorPicker() {
	const customColorEl = document.getElementById(
		'payment-page-background-color-custom'
	);

	if ( ! customColorEl ) {
		return;
	}

	$( '#payment-page-background-color-custom' ).wpColorPicker();

	const customButtonEl = document.querySelector(
		'.simpay-payment-page-background-color .wp-color-result'
	);

	const colorButtonEls = document.querySelectorAll(
		'.simpay-payment-page-background-color'
	);

	customButtonEl.addEventListener( 'click', ( e ) => {
		colorButtonEls.forEach( ( el ) => {
			el.querySelector( 'input' ).checked = false;
		} );

		e.target.classList.add( 'is-selected' );
		$( e.target ).parent().find( 'input' ).prop( 'checked', true );
	} );

	colorButtonEls.forEach( ( el ) => {
		el.querySelector( 'input' ).addEventListener( 'change', () => {
			customButtonEl.classList.remove( 'is-selected' );
		} );
	} );
}

/**
 * Updates the .simpay-payment-page-url clipboard text with the new value of the slug.
 *
 * @since 4.5.0
 */
function bindSlug() {
	const slugEl = document.querySelector( '[name="_payment_page_slug"]' );

	if ( ! slugEl ) {
		return;
	}

	slugEl.addEventListener( 'keyup', ( e ) => {
		const urlEl = document.querySelector( '.simpay-payment-page-url' );

		if ( ! urlEl ) {
			return;
		}

		urlEl.dataset.clipboardText = `${ window.location.origin }/${ e.target.value }`;
	} );
}

domReady( () => {
	bindEnable();
	bindHideBranding();
	bindColorPicker();
	bindSlug();
} );
