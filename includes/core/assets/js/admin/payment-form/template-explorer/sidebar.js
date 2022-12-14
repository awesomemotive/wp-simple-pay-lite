/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Button, NavigableMenu } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SearchControl from './search-control.js';

const baseClassName = 'simpay-form-template-explorer-main__sidebar';

function TemplateCategoriesList( {
	selectedCategory,
	templateCategories,
	onClickCategory,
} ) {
	return (
		<NavigableMenu
			orientation="vertical"
			className={ `${ baseClassName }-categories` }
		>
			{ Object.keys( templateCategories ).map( ( name ) => {
				const className = classnames(
					`category-${ name }`,
					`${ baseClassName }-categories_category`
				);

				return (
					<Button
						key={ name }
						label={ templateCategories[ name ] }
						className={ className }
						isPressed={ selectedCategory === name }
						onClick={ () => {
							onClickCategory( name );
						} }
					>
						{ templateCategories[ name ] }
					</Button>
				);
			} ) }
		</NavigableMenu>
	);
}

function TemplatesExplorerSearch( { filterValue, setFilterValue } ) {
	return (
		<div className={ `${ baseClassName }-search` }>
			<SearchControl
				onChange={ setFilterValue }
				value={ filterValue }
				label={ __( 'Search for templates', 'simple-pay' ) }
				placeholder={ __( 'Search', 'simple-pay' ) }
			/>
		</div>
	);
}

function TemplateExplorerSidebar( {
	selectedCategory,
	templateCategories,
	onClickCategory,
	filterValue,
	setFilterValue,
} ) {
	return (
		<div className={ baseClassName }>
			<TemplatesExplorerSearch
				filterValue={ filterValue }
				setFilterValue={ setFilterValue }
			/>

			{ ! filterValue && (
				<TemplateCategoriesList
					selectedCategory={ selectedCategory }
					templateCategories={ templateCategories }
					onClickCategory={ onClickCategory }
				/>
			) }
		</div>
	);
}

export default TemplateExplorerSidebar;
