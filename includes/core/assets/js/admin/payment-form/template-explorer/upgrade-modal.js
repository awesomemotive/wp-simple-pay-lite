/* global simpayFormBuilderTemplateExplorer */

/**
 * External dependencies
 */
import { upperFirst } from 'lodash';

/**
 * WordPress dependencies
 */
import { Button, Modal } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { Icon, check, lock } from '@wordpress/icons';
import { addQueryArgs } from '@wordpress/url';
import { createInterpolateElement } from '@wordpress/element';

const {
	alreadyPurchasedUrl,
	licenseLevel,
	upgradeUrl,
} = simpayFormBuilderTemplateExplorer;
const baseClassName = 'simpay-form-template-explorer-upgrade';

function UpgradeModal( { template, setIsShowingUpgradeModal } ) {
	return (
		<Modal
			title={ __( 'Upgrade Required', 'simple-pay' ) }
			onRequestClose={ () => setIsShowingUpgradeModal( false ) }
			className={ baseClassName }
		>
			<div className={ `${ baseClassName }__content` }>
				<Icon icon={ lock } size="48px" />

				<h3 className={ `${ baseClassName }__title` }>
					{ sprintf(
						/* translators: %s Template name */
						__( 'Unlock the "%s" Template', 'simple-pay' ),
						template.name
					) }
				</h3>

				<p className={ `${ baseClassName }__description` }>
					{ createInterpolateElement(
						sprintf(
							/* translators: %$ss Template name. %2$s Minimum license level. */
							__(
								'We\'re sorry, the "%1$s" template is not available on your plan. Please upgrade to the <strong>%2$s</strong> plan or higher to unlock this and other awesome features.',
								'simple-pay'
							),
							template.name,
							upperFirst( template.license[ 0 ] )
						),
						{
							strong: <strong />,
						}
					) }
				</p>

				<Button
					isPrimary
					variant="primary"
					href={ addQueryArgs( upgradeUrl, {
						utm_content: template.name,
					} ) }
					target="_blank"
					rel="noopener noreferrer"
				>
					{ 'lite' === licenseLevel
						? __( 'Upgrade to Pro', 'simple-pay' )
						: __( 'See Upgrade Options', 'simple-pay' ) }
				</Button>

				<a
					href={ alreadyPurchasedUrl }
					target="_blank"
					rel="noopener noreferrer"
					className={ `${ baseClassName }__purchased` }
				>
					{ __( 'Already purchased?', 'simple-pay' ) }
				</a>

				<p className={ `${ baseClassName }__discount` }>
					<Icon icon={ check } />
					{ 'lite' === licenseLevel
						? createInterpolateElement(
								// eslint-disable-next-line @wordpress/i18n-translator-comments
								__(
									'<strong>Bonus:</strong> WP Simple Pay Lite users get <highlight>50% off</highlight> regular price, automatically applied at checkout. <upgrade>Upgrade to Pro →</upgrade>',
									'simple-pay'
								),
								{
									strong: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										<strong />
									),
									highlight: <u />,
									upgrade: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										<a
											href={ addQueryArgs( upgradeUrl, {
												utm_content: template.name,
											} ) }
											target="_blank"
											rel="noopener noreferrer"
										/>
									),
								}
						  )
						: createInterpolateElement(
								// eslint-disable-next-line @wordpress/i18n-translator-comments
								__(
									'<strong>Bonus:</strong> WP Simple Pay Pro users get <highlight>50% off</highlight> upgrade pricing, automatically applied at checkout. <upgrade>See Upgrade Options →</upgrade>',
									'simple-pay'
								),
								{
									strong: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										<strong />
									),
									highlight: <u />,
									upgrade: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										<a
											href={ addQueryArgs( upgradeUrl, {
												utm_content: template.name,
											} ) }
											target="_blank"
											rel="noopener noreferrer"
										/>
									),
								}
						  ) }
				</p>
			</div>
		</Modal>
	);
}

export default UpgradeModal;
