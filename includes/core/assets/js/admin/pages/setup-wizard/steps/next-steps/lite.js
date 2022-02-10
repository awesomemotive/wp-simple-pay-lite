/* global simpaySetupWizard */

/**
 * WordPress dependencies
 */
import { Button, Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { check, Icon, wordpress } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { UpgradeCta, UpgradeCtaFeatures } from './styles.js';
import { CardFooter, CardBody, ContinueButton } from './../../components';

const { adminUrl, newFormUrl, upgradeUrl } = simpaySetupWizard;

const proFeatures = [
	__( 'Unlimited Custom Form Fields', 'simple-pay' ),
	__( 'On-Site Payment Forms', 'simple-pay' ),
	__( 'User-Entered Amounts', 'simple-pay' ),
	__( 'Apple Pay & Google Pay', 'simple-pay' ),
	__( 'Accept Recurring Payments', 'simple-pay' ),
	__( 'Drag & Drop Payment Form Builder', 'simple-pay' ),
	__( 'Custom Payment Receipt Emails', 'simple-pay' ),
	__( 'Coupon Codes', 'simple-pay' ),
	__( 'ACH Debit Payments', 'simple-pay' ),
	__( 'Plus much more…', 'simple-pay' ),
];

export function NextStepsLite() {
	return (
		<>
			<CardBody>
				<Flex
					justify="space-between"
					align="center"
					className="simpay-setup-wizard-content-list"
				>
					<div className="simpay-setup-wizard-content-list__content">
						<p>
							{ __(
								'Congratulations, you’re ready to start easily and securely accepting payments with WP Simple Pay!',
								'simple-pay'
							) }
						</p>
					</div>

					<div>
						<ContinueButton href={ newFormUrl }>
							{ __( 'Create a Payment Form →', 'simple-pay' ) }
						</ContinueButton>
					</div>
				</Flex>

				<hr />

				<UpgradeCta>
					<h4>
						{ __(
							'Special Upgrade Offer - Save 50%',
							'simple-pay'
						) }
					</h4>
					<h3>
						{ __(
							'Upgrade to WP Simple Pay Pro Today and Save',
							'simple-pay'
						) }
					</h3>

					<UpgradeCtaFeatures className="simpay-setup-wizard-check-list">
						{ proFeatures.map( ( feature ) => (
							<li key={ feature }>
								<Icon icon={ check } />
								{ feature }
							</li>
						) ) }
					</UpgradeCtaFeatures>

					<Button
						isLarge
						isSecondary
						variant="secondary"
						href={ upgradeUrl }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'Upgrade to WP Simple Pay Pro', 'simple-pay' ) }
					</Button>
				</UpgradeCta>
			</CardBody>

			<CardFooter justify="center">
				<Button
					isLink
					variant="link"
					href={ adminUrl }
					className="simpay-setup-wizard-subtle-link"
					icon={ <Icon icon={ wordpress } /> }
				>
					{ __( 'Return to Dashboard', 'simple-pay' ) }
				</Button>
			</CardFooter>
		</>
	);
}
