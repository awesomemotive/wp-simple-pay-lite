/* global simpaySetupWizard */

/**
 * WordPress dependencies
 */
import { Button, Flex, Popover } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { PopoverContent } from './styles.js';

const { adminUrl } = simpaySetupWizard;

export function CloseWizard() {
	const [ isVisible, setIsVisible ] = useState( false );
	const ref = useRef();

	useEffect( () => {
		if ( ! ref.current ) {
			return;
		}

		ref.current.focus();
	}, [ isVisible ] );

	return (
		<Flex justify="center">
			{ isVisible && (
				<Popover
					focusOnMount="container"
					position="top center"
					onFocusOutside={ () => setIsVisible( false ) }
				>
					<PopoverContent>
						<p>
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
								onClick={ () => setIsVisible( false ) }
								isLink
								variant="link"
								ref={ ref }
							>
								{ __( 'Continue Setup', 'simple-pay' ) }
							</Button>
						</Flex>
					</PopoverContent>
				</Popover>
			) }

			<Button
				isLink
				variant="link"
				className="simpay-setup-wizard-subtle-link"
				onClick={ () => setIsVisible( true ) }
			>
				{ __( 'Close and exit Setup Wizard', 'simple-pay' ) }
			</Button>
		</Flex>
	);
}
