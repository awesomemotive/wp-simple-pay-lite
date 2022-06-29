/* global jQuery */

/**
 * Blocks a checkbox from being toggled and displays an upgrade modal built
 * from the data attributes of the checkbox.
 *
 * @since 4.4.7
 *
 * @param {Event} e Change event.
 * @param {HTMLElement} e.target Checkbox being toggled.
 */
export function maybeBlockCheckboxWithUpgradeModal( e ) {
	const { target } = e;
	const {
		available,
		upgradeTitle,
		upgradeDescription,
		upgradeUrl,
		upgradePurchasedUrl,
	} = target.dataset;

	if ( ! available || 'yes' === available ) {
		return;
	}

	e.preventDefault();

	upgradeModal( {
		title: upgradeTitle,
		description: upgradeDescription,
		url: upgradeUrl,
		purchasedUrl: upgradePurchasedUrl,
	} );

	target.checked = false;

	return false;
}

/**
 * Blocks a button from doing anything and displays an upgrade modal built
 * from the data attributes of the button.
 *
 * @since 4.4.7
 *
 * @param {Event} e Click event.
 * @param {HTMLElement} e.target Button being pressed.
 */
export function maybeBlockButtonWithUpgradeModal( e ) {
	const { target } = e;
	const {
		available,
		upgradeTitle,
		upgradeDescription,
		upgradeUrl,
		upgradePurchasedUrl,
	} = target.dataset;

	if ( ! available || 'yes' === available ) {
		return;
	}

	e.preventDefault();

	upgradeModal( {
		title: upgradeTitle,
		description: upgradeDescription,
		url: upgradeUrl,
		purchasedUrl: upgradePurchasedUrl,
	} );
}

/**
 * Blocks a select from doing anything and displays an upgrade modal built
 * from the data attributes of the button.
 *
 * @since 4.4.7
 *
 * @param {Event} e Click event.
 * @param {HTMLElement} e.target Button being pressed.
 */
export function maybeBlockSelectWithUpgradeModal( e ) {
	const { target } = e;
	const {
		available,
		upgradeTitle,
		upgradeDescription,
		upgradeUrl,
		upgradePurchasedUrl,
		prevValue,
	} = target.options[ target.selectedIndex ].dataset;

	if ( ! available || 'yes' === available ) {
		return;
	}

	e.preventDefault();

	upgradeModal( {
		title: upgradeTitle,
		description: upgradeDescription,
		url: upgradeUrl,
		purchasedUrl: upgradePurchasedUrl,
	} );

	target.value = prevValue;
}

/**
 * Launches the jQuery UI upgrade modal.
 *
 * @since 4.4.6
 *
 * @param {Object} args Upgrade modal arguments.
 * @param {string} args.title Upgrade modal title.
 * @param {string} args.description Upgrade modal description.
 * @param {string} args.url Upgrade modal URL.
 * @param {string} args.purchasedUrl Upgrade modal purchased URL.
 * @param {Object} opts jQuery UI Dialog options.
 */
export function upgradeModal(
	{ title, description, url, purchasedUrl },
	opts = {}
) {
	jQuery( '.simpay-upgrade-modal' ).dialog( {
		position: {
			my: 'center',
			at: 'center',
			of: window,
		},
		modal: true,
		width: 600,
		resizable: false,
		draggable: false,
		open() {
			const m = jQuery( this );

			m.parent().find( '.ui-dialog-titlebar' ).css( {
				borderBottom: 0,
			} );

			m.find( '.simpay-upgrade-modal__title' ).html( title );
			m.find( '.simpay-upgrade-modal__description' ).html( description );
			m.find( '.simpay-upgrade-modal__upgrade-url' ).attr( 'href', url );
			m.find( '.simpay-upgrade-modal__upgrade-purchased-url' ).attr(
				'href',
				purchasedUrl
			);
		},
		...opts,
	} );
}
