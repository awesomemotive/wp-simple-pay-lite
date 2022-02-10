/* global simpaySetupWizard */

/**
 * WordPress dependencies
 */
import { Button, Flex, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

const { adminUrl } = simpaySetupWizard;

export function CloseWizard( { isFirst } ) {
	const [ isVisible, setIsVisible ] = useState( false );

	return (
		<Flex justify="center">
			{ isVisible && (
				<Modal
					title={ __(
						'Are you sure you want to exit the wizard?',
						'simple-pay'
					) }
					onRequestClose={ () => setIsVisible( false ) }
				>
					<p style={ { marginBottom: '2rem' } }>
						{ __(
							'Manual setup is only recommended for experienced users.',
							'simple-pay'
						) }
					</p>

					<Flex>
						<Button
							href={ adminUrl }
							isDestructive
							variant="destructive"
						>
							{ __( 'Exit Setup Wizard', 'simple-pay' ) }
						</Button>

						<Button
							variant="primary"
							isPrimary
							onClick={ () => setIsVisible( false ) }
						>
							{ __( 'Continue Setup', 'simple-pay' ) }
						</Button>
					</Flex>
				</Modal>
			) }

			<Button
				isLink
				variant="link"
				className="simpay-setup-wizard-subtle-link"
				onClick={ () => setIsVisible( true ) }
			>
				{ isFirst
					? __( 'Go back to the Dashboard', 'simple-pay' )
					: __( 'Close and exit the Setup Wizard', 'simple-pay' ) }
			</Button>
		</Flex>
	);
}
