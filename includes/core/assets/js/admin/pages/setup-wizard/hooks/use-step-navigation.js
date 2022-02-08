/* eslint-disable @wordpress/no-global-event-listener */
/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url';
import { useEffect, useState } from '@wordpress/element';

/**
 * Navigate between a list of steps.
 *
 * @param {Object} options Hook properties.
 * @param {Array} options.steps List of setup wizard steps.
 * @param {string} options.currentStepId Current setup wizard step ID.
 * @return {Object} Navigation step helpers.
 */
export function useStepNavigation( { steps, currentStepId } ) {
	const currentStepIndex =
		steps.findIndex( ( step ) => step.id === currentStepId ) || 0;

	const [ currentStep, setCurrentStep ] = useState( currentStepIndex );

	// Update current step as the user navigates in the browser.
	useEffect( () => {
		const handleState = () => {
			const newStep = getQueryArg( window.location.search, 'step' );
			const newStepIndex =
				steps.findIndex( ( step ) => step.id === newStep ) || 0;

			setCurrentStep( newStepIndex );
		};

		window.addEventListener( 'popstate', handleState );
		window.addEventListener( 'pushstate', handleState );

		return () => {
			window.removeEventListener( 'popstate', handleState );
			window.removeEventListener( 'pushstate', handleState );
		};
	}, [] );

	function navigate( toStep ) {
		setCurrentStep( toStep );

		// Push to browser history.
		window.history.pushState(
			{},
			'',
			addQueryArgs( window.location.href, {
				step: steps[ toStep ].id,
			} )
		);
	}

	function goNext() {
		navigate( currentStep + 1 );
	}

	function goPrev() {
		navigate( currentStep - 1 );
	}

	return {
		currentStep,
		setCurrentStep,
		hasNext: currentStep < steps.length - 1,
		goNext,
		goPrev,
		hasPrev: currentStep > 0,
	};
}
