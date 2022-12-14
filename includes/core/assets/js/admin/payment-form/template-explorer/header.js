/* global simpayFormBuilderTemplateExplorer */

/**
 * External dependencies
 */
import { find } from 'lodash';

/**
 * WordPress dependencies
 */
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs, getQueryArg } from '@wordpress/url';

const {
	suggestUrl,
	addNewUrl,
	licenseLevel,
	templates,
} = simpayFormBuilderTemplateExplorer;
const blankUrl = addQueryArgs( addNewUrl, {
	'simpay-template':
		'lite' === licenseLevel
			? find( templates, { slug: 'payment-button' } ).id
			: find( templates, { slug: 'payment-form' } ).id,
} );

const baseClassName = 'simpay-form-template-explorer-header';
const isAdding =
	'simpay_form_templates' === getQueryArg( window.location.href, 'page' );

function Header() {
	return (
		<div className={ baseClassName }>
			<h2 className={ `${ baseClassName }__title` }>
				{ isAdding
					? __(
							'Get a Head Start With Our Pre-Made Form Templates',
							'simple-pay'
					  )
					: __( 'Select a template', 'simple-pay' ) }
			</h2>

			<p className={ `${ baseClassName }__subtitle` }>
				{ createInterpolateElement(
					__(
						"To speed up the process you can select from one of our pre-made templates or start with a <blank>basic form</blank>. Have a suggestion for a new template? <suggest>We'd love to hear it</suggest>!",
						'simple-pay'
					),
					{
						blank: (
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							<a href={ blankUrl } />
						),
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
		</div>
	);
}

export default Header;
