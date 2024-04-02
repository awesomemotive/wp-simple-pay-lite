/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { maybeBlockCheckboxWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Binds toggling of "Collect Tax ID" setting.
 *
 * @since 4.4.7
 */
function collectTaxId() {
	const collectTaxIdEl = document.getElementById( '_enable_tax_id' );

	if ( ! collectTaxIdEl ) {
		return;
	}

	collectTaxIdEl.addEventListener(
		'change',
		maybeBlockCheckboxWithUpgradeModal
	);
}

/**
 * Binds toggling of "Allow Coupons" setting.
 *
 * @since 4.4.7
 */
function allowCoupons() {
	const enableCoupons = document.getElementById( '_enable_promotion_codes' );

	if ( ! enableCoupons ) {
		return;
	}

	enableCoupons.addEventListener(
		'change',
		maybeBlockCheckboxWithUpgradeModal
	);
}

/**
 * DOM ready.
 */
domReady( () => {
	collectTaxId();
	allowCoupons();
} );
