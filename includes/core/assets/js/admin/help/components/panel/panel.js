/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	useFocusReturn,
	useFocusOnMount,
	useConstrainedTabbing,
} from '@wordpress/compose';
import { Animate } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useMergeRefs } from './../../hooks';
import HelpPanelHeader from './header.js';
import HelpPanelFooter from './footer.js';
import SearchControl from './search-control.js';
import SearchResults from './search-results.js';

function HelpPanel( { onClose, onSearch, searchTerm } ) {
	const focusOnMountRef = useFocusOnMount( 'firstElement' );
	const constrainedTabbingRef = useConstrainedTabbing();
	const focusReturnRef = useFocusReturn();

	const refs = useMergeRefs( [
		constrainedTabbingRef,
		focusReturnRef,
		focusOnMountRef,
	] );

	return (
		<Animate type="slide-in" options={ { origin: 'left' } }>
			{ ( { className } ) => {
				const panelClassNames = classnames(
					'simpay-help-panel',
					className
				);

				return (
					<div ref={ refs } className={ panelClassNames }>
						<HelpPanelHeader onClose={ onClose } />

						<div className="simpay-help-panel__search">
							<SearchControl
								label={ __(
									'Search the documentation',
									'simple-pay'
								) }
								placeholder={ __( 'Search', 'simple-pay' ) }
								onChange={ onSearch }
								value={ searchTerm }
							/>

							<SearchResults searchTerm={ searchTerm } />
						</div>

						<HelpPanelFooter searchTerm={ searchTerm } />
					</div>
				);
			} }
		</Animate>
	);
}

export default HelpPanel;
