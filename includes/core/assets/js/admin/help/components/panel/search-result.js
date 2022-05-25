/* global simpayHelp */

/**
 * Internal depenedencies
 */
import { getGaUrl } from './../../utils.js';
const { isLite } = simpayHelp;

function SearchResult( { title, description, url, searchTerm } ) {
	return (
		<div className="simpay-help-panel__result">
			<a
				href={ getGaUrl( url, 'help', searchTerm, '1' === isLite ) }
				target="_blank"
				rel="noreferrer"
			>
				{ title }
			</a>
			{ description && <p>{ description }</p> }
		</div>
	);
}

export default SearchResult;
