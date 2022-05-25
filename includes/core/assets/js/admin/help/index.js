/* global simpayHelp */
/* eslint-disable @wordpress/no-global-event-listener */

/**
 * WordPress dependencies
 */
import { Popover } from '@wordpress/components';
import { render, useEffect, useState } from '@wordpress/element';
import { ESCAPE } from '@wordpress/keycodes';
import { getFragment, safeDecodeURI } from '@wordpress/url';

/**
 * Internal dependencies
 */
import {
	HelpPanelActionButton,
	HelpPanelBackdrop,
	HelpPanel,
} from './components';

const { docsSearchTerm: initialSearchTerm, docs } = simpayHelp;

/**
 * Retrieves information from a given URL's hash.
 *
 * @param {string} url URL to retrieve information from.
 * @return {Object} Object containing hash and search term.
 */
function getHashData( url ) {
	let hash = getFragment( url );
	let searchTerm = initialSearchTerm;

	// Allow prepopulating search terms from the URL hash.
	// #help/search keyword
	if ( hash && hash.includes( '/' ) ) {
		const split = hash.split( '/' );
		hash = split[ 0 ];
		searchTerm = safeDecodeURI( split[ 1 ] );
	}

	return {
		hash,
		searchTerm,
	};
}

function Help() {
	const { hash, searchTerm: hashSearchTerm } = getHashData(
		window.location.href
	);
	const [ searchTerm, setSearchTerm ] = useState( hashSearchTerm );
	const [ isOpen, setIsOpen ] = useState( '#help' === hash );

	/**
	 * Manage the URL fragment when the panel is opened or closed.
	 */
	useEffect( () => {
		function maybeOpenPanel( { newURL } ) {
			const { hash: newHash, searchTerm: openSearchTerm } = getHashData(
				newURL
			);

			if ( '#help' === newHash ) {
				setIsOpen( true );
			}

			if ( openSearchTerm !== hashSearchTerm ) {
				setSearchTerm( openSearchTerm );
			}
		}

		window.addEventListener( 'hashchange', maybeOpenPanel );

		return () => {
			window.removeEventListener( 'hashchange', maybeOpenPanel );
		};
	}, [] );

	/**
	 * Close the panel.
	 */
	function onClose() {
		setIsOpen( false );

		window.history.pushState(
			'',
			document.title,
			window.location.pathname + window.location.search
		);
	}

	/**
	 * Open the panel.
	 */
	function onOpen() {
		setIsOpen( true );

		window.history.pushState(
			'',
			document.title,
			window.location.pathname + window.location.search + '#help'
		);
	}

	/**
	 * Close the panel on ESC.
	 *
	 * @param {HTMLEvent} event Keydown event.
	 */
	function handleEscapeKeyDown( event ) {
		if ( event.keyCode === ESCAPE && ! event.defaultPrevented ) {
			event.preventDefault();
			setIsOpen( false );
		}
	}

	return (
		// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
		<div onKeyDown={ handleEscapeKeyDown } role="region">
			<HelpPanelActionButton
				isOpen={ isOpen }
				onOpen={ () => {
					// Reset search term to initial contextually-aware term.
					setSearchTerm( initialSearchTerm );
					onOpen();
				} }
			/>

			{ isOpen && (
				<>
					<HelpPanel
						onClose={ onClose }
						onSearch={ setSearchTerm }
						searchTerm={ searchTerm }
						docs={ docs }
					/>
					<HelpPanelBackdrop onClose={ onClose } />
				</>
			) }
			<Popover.Slot />
		</div>
	);
}

render( <Help />, document.getElementById( 'simpay-branding-bar-help' ) );
