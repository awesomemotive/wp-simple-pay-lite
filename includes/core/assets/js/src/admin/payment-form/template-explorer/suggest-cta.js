/* global simpayFormBuilderTemplateExplorer */

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, commentContent } from '@wordpress/icons';
import { createInterpolateElement } from '@wordpress/element';

const { suggestUrl } = simpayFormBuilderTemplateExplorer;
const baseClassName = 'simpay-form-template-explorer-main__content';

function SuggestCta() {
	return (
		<div className={ `${ baseClassName }-suggest` }>
			<div style={ { flexGrow: 0 } }>
				<Icon icon={ commentContent } size="42px" />
			</div>

			<div>
				<h4>{ __( 'We need your help!', 'simple-pay' ) }</h4>
				<p>
					{ createInterpolateElement(
						__(
							"We're constantly building more pre-made payment form templates to get you up and running even faster. If you have ideas for future templates, please let us know!",
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
				<Button
					isSecondary
					variant="secondary"
					isLink
					href={ suggestUrl }
					target="_blank"
				>
					{ __( 'Suggest a Template', 'simple-pay' ) }
				</Button>
			</div>
		</div>
	);
}

export default SuggestCta;
