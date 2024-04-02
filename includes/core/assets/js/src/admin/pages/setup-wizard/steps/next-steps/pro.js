/* global simpaySetupWizard */

/**
 * WordPress dependencies
 */
import { Button, Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, wordpress } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { CardFooter, CardBody, ContinueButton } from './../../components';

const {
	adminUrl,
	newFormUrl,
	taxSettingsUrl,
	captchaSettingsUrl,
	currencySettingsUrl,
	receiptSettingsUrl,
	donationsDocsUrl,
	invoicesDocsUrl,
} = simpaySetupWizard;

export function NextStepsPro() {
	return (
		<>
			<CardBody>
				<div className="simpay-setup-wizard-content-list">
					<div className="simpay-setup-wizard-content-list__content">
						<p>
							{ __(
								'Congratulations, you’re ready to start accepting payments!',
								'simple-pay'
							) }
						</p>

						<p>
							{ __(
								'WP Simple Pay is just that: simple. Create your first payment form below to start collecting payments, or fine-tune your setup through some of these suggestions:',
								'simple-pay'
							) }
						</p>
					</div>

					<div className="simpay-setup-wizard-content-list__list">
						<ul className="simpay-setup-wizard-bullet-list">
							<li>
								<a
									href={ taxSettingsUrl }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __( 'Collect tax or GST', 'simple-pay' ) }
								</a>
							</li>
							<li>
								<a
									href={ captchaSettingsUrl }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __(
										'Add anti-spam protection',
										'simple-pay'
									) }
								</a>
							</li>
							<li>
								<a
									href={ currencySettingsUrl }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __(
										'Change the default currency',
										'simple-pay'
									) }
								</a>
							</li>
							<li>
								<a
									href={ receiptSettingsUrl }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __( 'Customize receipts', 'simple-pay' ) }
								</a>
							</li>
						</ul>
					</div>
				</div>

				<hr />

				<div className="simpay-setup-wizard-doc-suggestions">
					<div>
						<h3>{ __( 'Accept Donations', 'simple-pay' ) }</h3>
						<p>
							{ __(
								'Easily fundraise or accept donations online via 135+ supported currencies. Offer one-time or recurring donations of fixed or user-defined amounts.',
								'simple-pay'
							) }
						</p>
						<Button
							href={ donationsDocsUrl }
							variant="secondary"
							isSecondary
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'View Walkthrough', 'simple-pay' ) }
						</Button>
					</div>

					<div>
						<h3>{ __( 'Reconcile Invoices', 'simple-pay' ) }</h3>
						<p>
							{ __(
								'Collect additional custom data on your payment forms such as an Invoice ID to reconcile invoices against your own invoicing system.',
								'simple-pay'
							) }
						</p>
						<Button
							href={ invoicesDocsUrl }
							variant="secondary"
							isSecondary
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'View Walkthrough', 'simple-pay' ) }
						</Button>
					</div>
				</div>
			</CardBody>

			<CardFooter justify="space-between" align="center">
				<div style={ { flexBasis: '100%' } }>
					<Button
						isLink
						variant="link"
						href={ adminUrl }
						className="simpay-setup-wizard-subtle-link"
						icon={ <Icon icon={ wordpress } /> }
					>
						{ __( 'Return to Dashboard', 'simple-pay' ) }
					</Button>
				</div>

				<Flex justify="flex-end" gap={ 4 }>
					<ContinueButton href={ newFormUrl }>
						{ __( 'Create a Payment Form →', 'simple-pay' ) }
					</ContinueButton>
				</Flex>
			</CardFooter>
		</>
	);
}
