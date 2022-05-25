/* global simpayHelp */

/**
 * External dependencies
 */
import { groupBy, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import SearchCategory from './search-category.js';

const { docs, docsCategories } = simpayHelp;

/**
 * Groups the documentation articles by category.
 *
 * @return {Array} Category information.
 */
const getCategories = function () {
	const groupedCategories = groupBy( docs, 'categories' );

	return map( groupedCategories, ( value, key ) => ( {
		slug: key,
		title: docsCategories[ key ] || '',
		docs: value,
	} ) );
};

function SearchCategories() {
	const [ openCategory, setOpenCategory ] = useState( 'getting-started' );

	return (
		<div className="simpay-help-panel__categories">
			{ getCategories().map( ( category ) => (
				<SearchCategory
					key={ category.slug }
					openCategory={ openCategory }
					setOpenCategory={ setOpenCategory }
					{ ...category }
				/>
			) ) }
		</div>
	);
}

export default SearchCategories;
