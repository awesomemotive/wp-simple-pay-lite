/* global simpayFormBuilderTemplateExplorer */

/**
 * WordPress dependencies
 */
import { render, useEffect, useState } from '@wordpress/element';
import { Popover, SlotFillProvider } from '@wordpress/components';

/**
 * Internal dependencies
 */
import TemplateExplorerHeader from './header.js';
import TemplateExplorerSidebar from './sidebar.js';
import TemplateExplorerContent from './content.js';

const {
	hasNew,
	templates,
	categories: templateCategories,
} = simpayFormBuilderTemplateExplorer;
const baseClassName = 'simpay-form-template-explorer';

function TemplateExplorer() {
	const [ filterValue, setFilterValueString ] = useState( '' );
	const [ selectedCategory, setSelectedCategory ] = useState(
		hasNew ? 'new' : ''
	);

	const setFilterValue = ( value ) => {
		setFilterValueString( value );
		setSelectedCategory( '' );
	};

	useEffect( () => {
		const body = document.querySelector( 'body' );
		body.classList.add( 'simpay-template-explorer-open' );
	}, [] );

	return (
		<SlotFillProvider>
			<div className={ baseClassName }>
				<TemplateExplorerHeader />

				<div className={ `${ baseClassName }-main` }>
					<TemplateExplorerSidebar
						selectedCategory={ selectedCategory }
						templateCategories={ templateCategories }
						onClickCategory={ setSelectedCategory }
						filterValue={ filterValue }
						setFilterValue={ setFilterValue }
					/>

					<TemplateExplorerContent
						templates={ templates }
						templateCategories={ templateCategories }
						selectedCategory={ selectedCategory }
						filterValue={ filterValue }
					/>
				</div>
			</div>

			<Popover.Slot />
		</SlotFillProvider>
	);
}

render(
	<TemplateExplorer />,
	document.getElementById( 'simpay-form-template-explorer' )
);
