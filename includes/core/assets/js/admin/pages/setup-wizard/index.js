/* global simpaySetupWizard */

/**
 * WordPress dependencies
 */
import '@wordpress/notices';
import { render, useState } from '@wordpress/element';
import { Popover, SlotFillProvider } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { getQueryArg } from '@wordpress/url';
import { EntityProvider } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import { STEPS } from './constants.js';
import {
	Card,
	CardHeader,
	CloseWizard,
	Logo,
	Progress,
	SetupWizard,
	Toasts,
} from './components';
import { useStepNavigation } from './hooks';
import { Welcome } from './steps';

const { license, adminUrl } = simpaySetupWizard;
const { is_lite: isLite } = license;

function SetupWizardApp() {
	const wizardSteps = STEPS[ true === isLite ? 'lite' : 'pro' ];
	const currentStepId = getQueryArg( window.location.href, 'step' );
	const [ licenseData, setLicenseData ] = useState( license );

	const { currentStep, goNext, goPrev, hasNext, hasPrev } = useStepNavigation(
		{
			steps: wizardSteps,
			currentStepId,
		}
	);

	// Wizard has not been started, show a (self-contained) welcome screen.
	if ( -1 === currentStep ) {
		return <Welcome steps={ wizardSteps } goNext={ goNext } />;
	}

	const step = wizardSteps[ currentStep ];
	const Content = step.content;

	return (
		<SlotFillProvider>
			<EntityProvider kind="root" type="site">
				<SetupWizard>
					<a href={ adminUrl }>
						<Logo width="250px" />
					</a>

					<Progress
						current={ currentStep + 1 }
						total={ wizardSteps.length }
					/>

					<Card isRounded>
						<CardHeader
							supTitle={ sprintf(
								/* translators: %1$d current step count. %2$d total step count*/
								__( 'Step %1$d of %2$d', 'simple-pay' ),
								currentStep + 1,
								wizardSteps.length
							) }
							title={ step.title }
						/>

						<Content
							steps={ wizardSteps }
							currentStep={ currentStep }
							goPrev={ goPrev }
							goNext={ goNext }
							hasNext={ hasNext }
							hasPrev={ hasPrev }
							licenseData={ licenseData }
							setLicenseData={ setLicenseData }
						/>
					</Card>

					{ hasNext && <CloseWizard /> }
				</SetupWizard>

				<Toasts className="simpay-setup-wizard-toasts" />

				<Popover.Slot />
			</EntityProvider>
		</SlotFillProvider>
	);
}

render( <SetupWizardApp />, document.getElementById( 'simpay-setup-wizard' ) );
