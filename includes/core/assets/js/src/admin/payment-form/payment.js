/**
 * Payment form -> Payment page JS.
 */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { maybeBlockCheckboxWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Allow multiple line items.
 *
 * @since 4.11.0
 */
export function allowMultipleLineItems() {
	const enableEl = document.getElementById(
		'_allow_purchasing_multiple_line_items'
	);

	if ( ! enableEl ) {
		return;
	}

	// if lite, show upgrade modal.
	if ( enableEl.getAttribute( 'data-upgrade-title' ) ) {
		enableEl.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			maybeBlockCheckboxWithUpgradeModal( e, enableEl );
		} );
	}

	const optionToggle = document.querySelectorAll( '.simpay-quantity-toggle' );
	const priceSelectorDisplayStyle = document.querySelector(
		'.simpay-price-select-display-type'
	);
	const toggleUserQuantity = () => {
		optionToggle.forEach( function ( el ) {
			// if enableEl is checked, remove the hidden class from the element
			if ( enableEl.checked ) {
				el.classList.remove( 'hidden' );
				priceSelectorDisplayStyle.style.display = 'none';
			} else {
				el.classList.add( 'hidden' );
				priceSelectorDisplayStyle.style.display = 'block';
			}
		} );
	};

	// Handle toggle on multiple line item setting change.
	enableEl.addEventListener( 'change', toggleUserQuantity );

	// Handle toggle on load.
	toggleUserQuantity();
}

/**
 * DOM ready.
 */
domReady( () => {
	allowMultipleLineItems();
} );
