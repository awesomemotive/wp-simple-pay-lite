/* global simpaySetupWizard */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	Card,
	CloseWizard,
	ContinueButton,
	Logo,
	SetupWizard,
} from './../../components';

const { adminUrl } = simpaySetupWizard;

export function Welcome( { goNext, hasPrev } ) {
	const toFocus = useRef();

	useLayoutEffect( () => {
		if ( ! toFocus.current ) {
			return;
		}

		toFocus.current.focus();
	}, [] );

	return (
		<SetupWizard>
			<a href={ adminUrl }>
				<Logo width="250px" />
			</a>

			<Card isRounded className="simpay-setup-wizard-welcome-step">
				<h1>
					{ __(
						'Welcome to the WP Simple Pay Setup Wizard!',
						'simple-pay'
					) }
				</h1>

				<p>
					{ __(
						"We'll guide you through getting WP Simple Pay set up on your site and ready to start accepting payments.",
						'simple-pay'
					) }
				</p>

				<ContinueButton onClick={ goNext } ref={ toFocus }>
					{ __( "Let's Get Started →", 'simple-pay' ) }
				</ContinueButton>
			</Card>

			<CloseWizard isFirst={ true } />
		</SetupWizard>
	);
}
