/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Filters the integrations list based on a fuzzy search of data-name and
 * data-description attributes on the integrations.
 *
 * @since 4.7.8
 */
domReady( () => {
	const searchEl = document.getElementById( 'automations-search' );

	if ( ! searchEl ) {
		return;
	}

	const integrationEls = document.querySelectorAll(
		'.simpay-form-builder-automator__integrations-integration'
	);

	searchEl.addEventListener( 'input', () => {
		const search = searchEl.value.toLowerCase();

		integrationEls.forEach( ( integrationEl ) => {
			const fields = Object.values( integrationEl.dataset );

			for ( let i = 0; i < fields.length; i++ ) {
				const fieldValue = fields[ i ].toLowerCase();

				if ( fieldValue.includes( search ) ) {
					integrationEl.style.display = 'block';
					return;
				}

				integrationEl.style.display = 'none';
			}
		} );

		if ( '' === search ) {
			integrationEls.forEach( ( integrationEl ) => {
				if ( integrationEl.dataset.overflow === 'yes' ) {
					integrationEl.style.display = 'none';
				}
			} );
		}
	} );
} );
