/* global simpayHelp */

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, chevronDown, chevronRight } from '@wordpress/icons';

/**
 * Internal depenedencies
 */
import { getGaUrl } from './../../utils.js';
const { isLite } = simpayHelp;

function SearchCategory( {
	title: categoryTitle,
	slug,
	docs,
	openCategory,
	setOpenCategory,
} ) {
	const [ isShowingAll, setIsShowingAll ] = useState( false );
	const initialDocs = docs.slice( 0, 5 );
	const docsToShow = isShowingAll ? docs : initialDocs;
	const isExpanded = openCategory === slug;

	return (
		<div className="simpay-help-panel__category">
			<button
				className="simpay-help-panel__category-title"
				onClick={ () => setOpenCategory( isExpanded ? '' : slug ) }
			>
				<span>{ categoryTitle }</span>

				<Icon
					icon={ isExpanded ? chevronDown : chevronRight }
					size={ 32 }
				/>
			</button>

			{ isExpanded && (
				<div>
					{ docsToShow.map( ( { title, url } ) => {
						return (
							<a
								key={ url }
								href={ getGaUrl(
									url,
									'help',
									title,
									'1' === isLite
								) }
								target="_blank"
								rel="noreferrer"
							>
								{ title }
							</a>
						);
					} ) }

					{ ! isShowingAll && docs.length > 5 && (
						<Button
							variant="secondary"
							isSecondary
							isSmall
							onClick={ () => setIsShowingAll( true ) }
						>
							{ __( 'View all', 'simple-pay' ) }
						</Button>
					) }
				</div>
			) }
		</div>
	);
}

export default SearchCategory;
