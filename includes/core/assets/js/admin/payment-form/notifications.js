/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { maybeBlockButtonWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Promopts for upgrade if accessing Email Notifications.
 *
 * @since 4.6.4
 */
function bindUpgradeModal() {
	const notificationsTab = document.querySelector(
		'div[data-lite] li.simpay-notifications-tab'
	);

	if ( notificationsTab ) {
		notificationsTab.addEventListener(
			'click',
			maybeBlockButtonWithUpgradeModal
		);
	}
}

domReady( bindUpgradeModal );
