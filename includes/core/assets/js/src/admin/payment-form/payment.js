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
 * Toggles the "Optional Recurring Toggle" Label setting if the "Optional Recurring Toggle" is checked.
 *
 * @since 4.11.0
 *
 * @param {HTMLElement} priceEl
 * @param {boolean} isVisible
 * @returns
 */
export function toggleOptionalRecurringLabel( priceEl, isVisible ) {
	const multipleLineItem = document.getElementById(
		'_allow_purchasing_multiple_line_items'
	);

	const canRecurToggle = priceEl.querySelector(
		'.simpay-price-enable-optional-subscription'
	);
	const recurringToggleLabelEl = priceEl.querySelector(
		'.simpay-price-recurring-toggle-label'
	);

	if ( ! recurringToggleLabelEl || ! canRecurToggle ) {
		return;
	}
	isVisible = true;
	recurringToggleLabelEl.style.display =
		isVisible && canRecurToggle && canRecurToggle.checked && multipleLineItem.checked ? 'block' : 'none';

	canRecurToggle.addEventListener( 'change', ( e ) => {
		recurringToggleLabelEl.style.display = e.target.checked && multipleLineItem.checked
			? 'block'
			: 'none';
	} );
}

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

	const toggleUserQuantity = () => {
		const optionQuantityToggle = document.querySelectorAll( '.simpay-quantity-toggle' );

		if ( optionQuantityToggle ) {
			optionQuantityToggle.forEach( function ( el ) {
				if ( enableEl.checked ) {
					el.classList.remove( 'hidden' );
				} else {
					el.classList.add( 'hidden' );
				}
			} );
		}

		const priceSelectorDisplayStyle = document.querySelector(
			'.simpay-price-select-display-type'
		);

		if ( priceSelectorDisplayStyle ) {
			if ( enableEl.checked ) {
				priceSelectorDisplayStyle.style.display = 'none';
			} else {
				priceSelectorDisplayStyle.style.display = 'block';
			}
		}
	};

	const toggleRequiredToggle = () => {
		const optionRequiredToggle = document.querySelectorAll( '.simpay-price-required-check' );

		if ( ! optionRequiredToggle ) {
			return;
		}

		optionRequiredToggle.forEach( function ( el ) {
			if ( enableEl.checked ) {
				el.style.display = 'block';
			} else {
				el.style.display = 'none';
			}
		} );
	};

	function toggleAllOptionalRecurringLabels( isVisible ) {
		const pricesEls = document.querySelectorAll( '.simpay-price' );

		pricesEls.forEach( ( priceEl ) => {
			toggleOptionalRecurringLabel( priceEl, isVisible );
		} );
	}

	// Handle toggle on multiple line item setting change.
	enableEl.addEventListener( 'change', ( e ) => {
		toggleUserQuantity();
		toggleRequiredToggle();
		toggleAllOptionalRecurringLabels( e.target.checked );
		enableDisableOneTimePaymentMethods( enableEl );
	} );

	// Handle toggle on load.
	toggleUserQuantity();
	toggleRequiredToggle();
	toggleAllOptionalRecurringLabels();
	enableDisableOneTimePaymentMethods( enableEl );
}

/**
 * Enable/Disable one time payment methods based on multiple line item setting.
 *
 * @since 4.11.0
 * @param {HTMLElement} enableEl Enable/Disable multiple line item setting.
 * @return {void}
 */
export function enableDisableOneTimePaymentMethods( enableEl ) {
	const paymentMethodsFilter = document.querySelectorAll(
		'.simpay-panel-field-payment-method-filter'
	);
	const paymentMethods = document.querySelectorAll(
		'.simpay-panel-field-payment-method[data-payment-method]'
	);

	[ ...paymentMethods ].forEach( ( paymentMethod ) => {
		const { recurring, scope } = JSON.parse(
			paymentMethod.dataset.paymentMethod
		);

		if ( enableEl.checked ) {
			paymentMethod.style.display = ! recurring ? 'none' : 'block';
		} else {
			const maybeShow = 'popular' === scope ? 'block' : 'none';

			paymentMethod.style.display =
				'all' === paymentMethodsFilter[ 0 ].value ? 'block' : maybeShow;
		}
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	allowMultipleLineItems();
} );
