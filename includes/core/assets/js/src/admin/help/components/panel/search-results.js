/* global simpayHelp */

/**
 * WordPress dependencies
 */
import { useEffect, useMemo } from '@wordpress/element';
import { _n, sprintf } from '@wordpress/i18n';
import { useDebounce } from '@wordpress/compose';
import { speak } from '@wordpress/a11y';

/**
 * Internal dependencies
 */
import SearchResult from './search-result.js';
import SearchCategories from './search-categories.js';
import { searchItems } from './../../search-utils.js';
const { docs } = simpayHelp;

function SearchResults( { searchTerm } ) {
	// Filter the docs list based on the search term.
	const filteredDocs = useMemo( () => {
		let results = [];
		// If there is a search term, filter the docs list.
		if ( '' !== searchTerm ) {
			results = searchItems( docs, searchTerm );
		}

		return results;
	}, [ searchTerm ] );

	// Announce search results on change.
	const debouncedSpeak = useDebounce( speak, 500 );

	useEffect( () => {
		if ( ! searchTerm ) {
			return;
		}

		const count = filteredDocs.length;
		const resultsFoundMessage = sprintf(
			/* translators: %d: number of results. */
			_n( '%d result found.', '%d results found.', count, 'simple-pay' ),
			count
		);

		debouncedSpeak( resultsFoundMessage );
	}, [ searchTerm, debouncedSpeak ] );

	const hasItems = !! filteredDocs?.length;

	return (
		<div className="simpay-help-panel__results">
			{ ! hasItems && <SearchCategories /> }

			{ hasItems &&
				filteredDocs.map( ( doc ) => (
					<SearchResult
						key={ doc.id }
						searchTerm={ searchTerm }
						{ ...doc }
					/>
				) ) }
		</div>
	);
}

export default SearchResults;
