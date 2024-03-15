/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import {
	maybeBlockButtonWithUpgradeModal,
	maybeBlockCheckboxWithUpgradeModal,
} from '@wpsimplepay/utils';

/**
 * Toggles Payment Method visibility when filtering Payment Methods.
 *
 * @since 4.4.7
 *
 * @param {Object} e Change event.
 * @param {HTMLElement} e.target Payment Method filter toggle.
 */
function onFilter( { target } ) {
	const filter = target.options[ target.selectedIndex ].value;

	const paymentMethods = document.querySelectorAll(
		'.simpay-panel-field-payment-method[data-payment-method]'
	);

	[ ...paymentMethods ].forEach( ( paymentMethod ) => {
		const { scope } = JSON.parse( paymentMethod.dataset.paymentMethod );
		const maybeShow = 'popular' === scope ? 'block' : 'none';

		paymentMethod.style.display = 'all' === filter ? 'block' : maybeShow;
	} );
}

domReady( () => {
	// Filter.
	const filters = document.querySelectorAll(
		'.simpay-panel-field-payment-method-filter'
	);

	if ( 0 !== filters.length ) {
		[ ...filters ].forEach( ( filter ) => {
			filter.addEventListener( 'change', onFilter );
		} );
	}

	// Education.
	const paymentMethods = document.querySelectorAll(
		'.simpay-payment-method-lite'
	);

	if ( paymentMethods ) {
		paymentMethods.forEach( ( paymentMethod ) =>
			paymentMethod.addEventListener(
				'change',
				maybeBlockCheckboxWithUpgradeModal
			)
		);
	}

	const paymentMethodsFeeRecovery = document.querySelectorAll(
		'.simpay-payment-method-fee-recovery-lite'
	);

	if ( paymentMethodsFeeRecovery ) {
		paymentMethodsFeeRecovery.forEach( ( paymentMethod ) =>
			paymentMethod.addEventListener(
				'click',
				maybeBlockButtonWithUpgradeModal
			)
		);
	}
} );
