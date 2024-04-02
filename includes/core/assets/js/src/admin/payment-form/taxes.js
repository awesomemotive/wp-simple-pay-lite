/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { maybeBlockSelectWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * DOM ready.
 */
domReady( () => {
	const selector = document.getElementById( '_tax_status_lite' );

	if ( selector ) {
		selector.addEventListener( 'change', maybeBlockSelectWithUpgradeModal );
	}
} );
