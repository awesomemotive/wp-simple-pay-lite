/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { upgradeModal } from '@wpsimplepay/utils';

/**
 * DOM ready.
 */
domReady( () => {
	// Email configuration.
	const configurationSelectorEl = document.querySelector(
		'.simpay-settings-emails-configure'
	);

	if ( configurationSelectorEl ) {
		const deliverySubsectionEl = document.querySelector(
			'.simpay-settings-subsection-delivery'
		);

		deliverySubsectionEl.after( configurationSelectorEl );

		setupConfigurationEducation( configurationSelectorEl );
	}
} );

/**
 * Handles product education when configuring an email not available to the current license.
 *
 * @since 4.4.6
 *
 * @param {HTMLElement} configurationSelectorEl Email configuration form.
 */
function setupConfigurationEducation( configurationSelectorEl ) {
	const selector = configurationSelectorEl.querySelector( 'select' );

	if ( ! selector ) {
		return;
	}

	// Listen for changes.
	selector.addEventListener( 'change', maybeShowUpgradeModal );

	// Show upgrade modal if necessary.
	function maybeShowUpgradeModal( { target } ) {
		const { options, selectedIndex } = target;
		const selected = options[ selectedIndex ];
		const {
			available,
			upgradeTitle,
			upgradeDescription,
			upgradeUrl,
			upgradePurchasedUrl,
		} = selected.dataset;

		if ( 'no' === available ) {
			upgradeModal( {
				title: upgradeTitle,
				description: upgradeDescription,
				url: upgradeUrl,
				purchasedUrl: upgradePurchasedUrl,
			} );

			selector.value = '';
			selector.selectedIndex = 0;
		}
	}
}
