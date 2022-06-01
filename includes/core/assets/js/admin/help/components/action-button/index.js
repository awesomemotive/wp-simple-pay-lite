/* global simpayHelp */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Button, Popover } from '@wordpress/components';

const { hasSeen } = simpayHelp;

function HelpActionButton( { onOpen } ) {
	const [ isPopoverOpen, setIsPopoverOpen ] = useState( '0' === hasSeen );

	return (
		<div>
			<button
				type="button"
				className="simpay-branding-bar__actions-button"
				onClick={ () => {
					setIsPopoverOpen( false );
					onOpen();
				} }
			>
				<svg
					viewBox="0 0 20 20"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z"
						fill="currentColor"
					/>
				</svg>
			</button>

			{ isPopoverOpen && (
				<Popover position="left" noArrow={ false }>
					<div className="simpay-help-popover">
						<h4>
							{ __( 'Need help with something?', 'simple-pay' ) }
						</h4>
						<p>
							{ __(
								'Answers are at your fingertips with the WP Simple Pay help panel. Quickly view suggested articles, search for a specific feature, or submit a support ticket.',
								'simple-pay'
							) }
						</p>
						<Button
							variant="secondary"
							isSecondary
							onClick={ () => setIsPopoverOpen( false ) }
						>
							{ __( 'Got it!', 'simple-pay' ) }
						</Button>
					</div>
				</Popover>
			) }
		</div>
	);
}

export default HelpActionButton;
