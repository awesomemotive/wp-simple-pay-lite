/* global simpayAdminDashboardWidgetReport */

/**
 * WordPress dependencies
 */
import { Button, Flex, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

const { webhooks_url: webhooksUrl, license } = simpayAdminDashboardWidgetReport;

function MoreInfo( { setIsOpen } ) {
	return (
		<Modal
			title={ __( 'Gross Volume', 'simple-pay' ) }
			onRequestClose={ () => setIsOpen( false ) }
			style={ {
				maxWidth: '420px',
			} }
		>
			<p style={ { marginBottom: '2rem' } }>
				{ __(
					'Estimated gross amounts for transactions in the selected currency and time period. Amounts do not represent verified financial data.',
					'simple-pay'
				) }
			</p>

			{ false === license.is_lite && (
				<p style={ { marginBottom: '2rem' } }>
					{ createInterpolateElement(
						__(
							'Transaction data requires <url>webhooks to be properly configured</url> to populate correctly.',
							'simple-pay'
						),
						{
							url: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a href={ webhooksUrl } />
							),
						}
					) }
				</p>
			) }

			<Flex>
				<Button variant="link" onClick={ () => setIsOpen( false ) }>
					{ __( 'Close', 'simple-pay' ) }
				</Button>

				<Button
					variant="primary"
					isPrimary
					href="https://dashboard.stripe.com/reports/hub"
					target="_blank"
				>
					{ __( 'View advanced reports in Stripe →', 'simple-pay' ) }
				</Button>
			</Flex>
		</Modal>
	);
}

export default MoreInfo;
