/* global simpayFormBuilderTemplateExplorer */

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { Icon, lock } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import UpgradeModal from './upgrade-modal.js';

const { licenseLevel, addNewUrl, isLite } = simpayFormBuilderTemplateExplorer;
const baseClassName = 'simpay-form-template-explorer-main__content';

const noDemo = [
	'payment-button',
	'donate-button',
	'payment-form',
	'product-installment-plan-form',
	'product-purchse-form',
	'product-subscription-form',
	'product-subscription-form-coupon',
	'recurring-service-setup-fee-form',
	'recurring-service-trial-period-form',
	'recurring-services-form',
];

function TemplateListItem( { template } ) {
	const [ isShowingUpgradeModal, setIsShowingUpgradeModal ] = useState(
		false
	);

	const needsUpgrade = ! template.license.includes( licenseLevel );
	const useTemplateUrl = addQueryArgs( addNewUrl, {
		'simpay-template': template.id,
	} );

	const demoUrl = addQueryArgs(
		`https://wpsimplepay.com/templates/${ template.slug }/`,
		{
			utm_source: 'WordPress',
			utm_campaign: '1' === isLite ? 'lite-plugin' : 'pro-plugin',
			utm_medium: 'template-explorer',
			utm_content: template.name,
		}
	);

	return (
		<>
			{ isShowingUpgradeModal && (
				<UpgradeModal
					template={ template }
					setIsShowingUpgradeModal={ setIsShowingUpgradeModal }
				/>
			) }

			<div
				key={ template.id }
				className={ `${ baseClassName }-list-item` }
			>
				{ needsUpgrade && (
					<div className={ `${ baseClassName }-list-item__badge` }>
						<Icon icon={ lock } size="16px" />
					</div>
				) }

				<div
					id={ template.id }
					className={ `${ baseClassName }-list-item__name ${
						needsUpgrade
							? `${ baseClassName }-list-item__name--is-locked`
							: ''
					}` }
				>
					{ template.name }
				</div>

				<div className={ `${ baseClassName }-list-item__description` }>
					{ template.description }
				</div>

				<div className={ `${ baseClassName }-list-item__actions` }>
					<Button
						isPrimary
						variant="primary"
						href={ needsUpgrade ? undefined : useTemplateUrl }
						onClick={ () =>
							needsUpgrade
								? setIsShowingUpgradeModal( true )
								: null
						}
					>
						{ 'lite' === licenseLevel &&
						'payment-button' === template.slug
							? __( 'Create Payment Button', 'simple-pay' )
							: __( 'Use Template', 'simple-pay' ) }
					</Button>

					{ ! noDemo.includes( template.slug ) && (
						<Button
							isSecondary
							variant="secondary"
							href={ demoUrl }
							style={ { marginLeft: '10px' } }
							target="_blank"
						>
							{ __( 'View Demo', 'simple-pay' ) }
						</Button>
					) }
				</div>
			</div>
		</>
	);
}

export default TemplateListItem;
