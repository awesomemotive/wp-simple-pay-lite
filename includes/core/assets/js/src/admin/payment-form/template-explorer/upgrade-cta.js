/* global simpayFormBuilderTemplateExplorer */

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const { isLite, upgradeUrl } = simpayFormBuilderTemplateExplorer;
const baseClassName = 'simpay-form-template-explorer-main__content';

function UpgradeCta() {
	return (
		<div className={ `${ baseClassName }-upgrade` }>
			<div>
				<h4>
					{ __(
						'Get Access to All of Our Pre-Made Payment Form Templates',
						'simple-pay'
					) }
				</h4>
				<p>
					{ __(
						'Never start from scratch again! Upgrade to gain access to every payment form template we make and unlock powerful new features.',
						'simple-pay'
					) }
				</p>
			</div>

			<Button
				isSecondary
				variant="primary"
				isLink
				size="large"
				href={ upgradeUrl }
				target="_blank"
			>
				{ isLite
					? __( 'Upgrade to Pro', 'simple-pay' )
					: __( 'Upgrade Now', 'simple-pay' ) }
			</Button>
		</div>
	);
}

export default UpgradeCta;
