/* global simpayFormBuilderTemplateExplorer */

/**
 * External dependencies
 */
import classNames from 'classnames';
import { sortBy } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	createInterpolateElement,
	Fragment,
	useMemo,
	useEffect,
} from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { useDebounce } from '@wordpress/compose';
import { speak } from '@wordpress/a11y';

/**
 * Internal dependencies
 */
import TemplateListItem from './list-item.js';
import SuggestCta from './suggest-cta.js';
import UpgradeCta from './upgrade-cta.js';
import { searchItems } from './search-utils.js';

const { suggestUrl, licenseLevel } = simpayFormBuilderTemplateExplorer;

function TemplateExplorerContent( {
	filterValue,
	selectedCategory,
	templates,
} ) {
	const debouncedSpeak = useDebounce( speak, 500 );

	const filteredTemplates = useMemo( () => {
		let results = templates;

		if ( ! filterValue ) {
			if ( '' !== selectedCategory ) {
				results = templates.filter( ( { categories } ) =>
					categories.includes( selectedCategory )
				);
			}
		} else {
			results = searchItems( templates, filterValue );
		}

		return sortBy(
			results,
			( { license } ) => ! license.includes( licenseLevel )
		);
	}, [ filterValue, selectedCategory, templates ] );

	// Announce search results on change.
	useEffect( () => {
		if ( ! filterValue ) {
			return;
		}

		const count = filteredTemplates.length;
		const resultsFoundMessage = sprintf(
			/* translators: %d: number of results. */
			_n( '%d result found.', '%d results found.', count, 'simple-pay' ),
			count
		);

		debouncedSpeak( resultsFoundMessage );
	}, [ filterValue, debouncedSpeak ] );

	const hasItems = !! filteredTemplates?.length;

	const contentClassName = classNames(
		'simpay-form-template-explorer-main__content',
		{
			'is-empty': ! hasItems,
		}
	);

	return (
		<div className={ contentClassName }>
			{ ! hasItems && (
				<p>
					{ createInterpolateElement(
						__(
							"No results found. Have a suggestion for a new template? <suggest>We'd love to hear it</suggest>!",
							'simple-pay'
						),
						{
							suggest: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a
									href={ suggestUrl }
									target="_blank"
									rel="noopener noreferrer"
								/>
							),
						}
					) }
				</p>
			) }

			{ hasItems && (
				<>
					{ filteredTemplates.map( ( template, i ) => {
						const item = (
							<TemplateListItem
								key={ template.slug }
								template={ template }
							/>
						);

						if (
							'' === filterValue &&
							[ 'lite', 'personal', 'plus' ].includes(
								licenseLevel
							) &&
							i === 5
						) {
							return (
								<Fragment key="upgrade-frag">
									{ item }
									<UpgradeCta />
								</Fragment>
							);
						}

						return item;
					} ) }

					<SuggestCta />
				</>
			) }
		</div>
	);
}

export default TemplateExplorerContent;
