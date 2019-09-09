/* global simpayAppPro, _ */

/**
 * Internal dependencies.
 */
import { setup as setupPaymentRequestButton } from 'pro/frontend/components/payment-request-button.js';

/**
 * Toggle an overlay form's visibility.
 *
 * Attach to any type of link. Useful when the form number is known.
 *
 *   document.querySelector( '.my-link' ).addEventListener( 'click', function( e ) {
 *     e.preventDefault();
 *     simpayAppPro.toggleOverlayForm( 13 );
 *   } );
 *
 * Attach to a button that has an associated form ID. Useful when the form number is dynamic.
 * This functionality is added by default to [simpay id="13"] shortcode usage.
 *
 *   <button data-form-id="13">Launch</button>
 *   document.querySelector( '.my-button' ).addEventListener( 'click', simpayAppPro.toggleOverlayForm );
 *
 * @param {mixed} Click or change event, or an ID of a form.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {HTMLElement} cardEl Card element to mount to.
 */
export function toggle( e, spFormElem, formData ) {
	var formId = false;

	if ( 'object' === typeof e ) {
		e.preventDefault();
		formId = e.target.dataset.formId;
	} else {
		formId = e;
	}

	// Find the modal.
	var modal = document.querySelectorAll( '.simpay-modal[data-form-id="' + formId + '"]' );

	if ( 0 === modal.length ) {
		return;
	}

	// Always get the last instance of the modal markup since the markup
	// is moved to the end of the DOM.
	//
	// @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/738
	modal = modal[ modal.length - 1 ];

	// Move Modal markup to end of the document.
	document.body.appendChild( modal );

	var modalStyles = getComputedStyle( modal );
	var isVisible = '0' !== modalStyles.getPropertyValue( 'opacity' );

	if ( isVisible ) {
		modal.style.opacity = 0;
		modal.style.height = 0;
	} else {
		modal.style.opacity = 1;
		modal.style.height = '100%';

		setupPaymentRequestButton( e, spFormElem, formData );
		focusFirstField( modal, spFormElem, formData );
	}
};

/**
 * Focus first field in an overlay form's modal.
 *
 * @param {HTMLElement} modal Modal being shown.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {HTMLElement} cardEl Card element to mount to.
 */
function focusFirstField( modal ) {
	/**
	 * Selectable elements.
	 */
	const SELECTOR = [
		'button:not([disabled])',
		'input:not([type="hidden"]):not([aria-hidden]):not([disabled])',
		'select:not([disabled])',
		'textarea:not([disabled])',
	].join( ',' );

	const firstModalField = modal.querySelector( SELECTOR );

	if ( firstModalField ) {
		firstModalField.focus();
		firstModalField.parentElement.classList.add( 'is-focused' );
	}
};

/**
 * Manage modal toggling for "Overlay" form display types.
 *
 * Initial HTML markup for the modal is output as a sibling to the toggle
 * control but is moved to the end of the document to combat issues with z-index.
 *
 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/610
 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/645
 *
 * @param {Event} e Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {HTMLElement} cardEl Card element to mount to.
 */
export function setup( e, spFormElem, formData ) {
	var inputControls = document.querySelectorAll( 'input.simpay-modal-control' );
	var controls = document.querySelectorAll( '*:not(input).simpay-modal-control' );

	// Bind each control to toggle a modal.
	_.each( inputControls, function( control ) {
		control.addEventListener( 'change', ( e ) => toggle( e, spFormElem, formData ) );
	} );

	_.each( controls, function( control ) {
		control.addEventListener( 'click', ( e ) => toggle( e, spFormElem, formData ) );
	} );
};
