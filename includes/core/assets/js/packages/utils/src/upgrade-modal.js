/* global jQuery */

/**
 * Launches the jQuery UI upgrade modal.
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
