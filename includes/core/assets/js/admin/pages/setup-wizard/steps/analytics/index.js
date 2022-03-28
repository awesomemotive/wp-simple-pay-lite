/* global simpaySetupWizard */

/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { Button, Spinner, Popover, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useLayoutEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Icon, info } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import {
	AnalyticsFaq,
	AnalyticsOptIn,
	AnalyticsLabel,
	AnalyticsToggle,
} from './styles';
import { CardFooter, CardBody, ContinueButton } from './../../components';
import { useSettings } from './../../hooks';

const {
	adminEmail: bootAdminEmail,
	subscribeNonce,
	ajaxUrl,
} = simpaySetupWizard;

export function Analytics( { goPrev, goNext } ) {
	const {
		settings,
		rawSettings,
		discardChanges,
		editSettings,
		saveSettings,
	} = useSettings();

	const [ isOptedIn, setIsOptedIn ] = useState( true );
	const [ adminEmail, setAdminEmail ] = useState( bootAdminEmail );
	const [ isBusy, setIsBusy ] = useState( false );
	const [ isShowingAnalyticsFaq, setIsShowingAnalyticsFaq ] = useState(
		false
	);

	const toFocus = useRef();

	useLayoutEffect( () => {
		if ( ! toFocus.current ) {
			return;
		}

		toFocus.current.focus();
	}, [ rawSettings ] );

	if ( isEmpty( settings ) ) {
		return (
			<CardBody>
				<Spinner />
			</CardBody>
		);
	}

	function onSkip() {
		discardChanges();
		goNext();
	}

	/**
	 * Attempts to subscribe the email address via admin-ajax.php, save settings, and navigates forward.
	 */
	function onSave() {
		setIsBusy( true );

		// Force an edit to the settings to ensure unchanged settings are saved.
		editSettings( {
			usage_tracking_opt_in: isOptedIn ? 'yes' : 'no',
		} );

		saveSettings(); // Always save settings.

		// eslint-disable-next-line no-undef
		const body = new FormData();
		body.append( 'action', 'simpay_setup_wizard_subscribe_email' );
		body.append( 'nonce', subscribeNonce );
		body.append( 'email', adminEmail );

		apiFetch( {
			url: ajaxUrl,
			method: 'POST',
			body,
		} )
			// Susbcriptions can fail, just move forward.
			.finally( () => {
				setIsBusy( false );
				goNext();
			} );
	}

	return (
		<>
			<CardBody>
				<p>
					{ __(
						'Get helpful suggestions from WP Simple Pay on how to optimize your payment forms and grow your business or increase donations.',
						'simple-pay'
					) }
				</p>

				<TextControl
					label={ __( 'Your Email Address:', 'simple-pay' ) }
					value={ adminEmail }
					className="simpay-setup-wizard-large-input"
					onChange={ ( value ) => setAdminEmail( value ) }
					help={ __(
						'Your email is needed so you receive recommendations.',
						'simple-pay'
					) }
					ref={ toFocus }
					disabled={ isBusy }
				/>

				<hr />

				<AnalyticsOptIn>
					<AnalyticsLabel>
						{ __(
							'Help make WP Simple Pay better for everyone',
							'simple-pay'
						) }

						<div>
							{ isShowingAnalyticsFaq && (
								<AnalyticsFaq position="top center">
									{ __(
										'By allowing us to track usage data we can better help you because we know which WordPress configurations, themes, and plugins we should test.',
										'simple-pay'
									) }
								</AnalyticsFaq>
							) }
							<Icon
								size={ 20 }
								icon={ info }
								onMouseEnter={ () =>
									setIsShowingAnalyticsFaq( true )
								}
								onMouseLeave={ () =>
									setIsShowingAnalyticsFaq( false )
								}
							/>
						</div>
					</AnalyticsLabel>

					<AnalyticsToggle
						label={ __( 'Yes, count me in', 'simple-pay' ) }
						id="email_payment-confirmation"
						checked={ isOptedIn }
						disabled={ isBusy }
						onChange={ ( checked ) => {
							setIsOptedIn( checked );
						} }
					/>
				</AnalyticsOptIn>
			</CardBody>

			<CardFooter justify="space-between" align="center">
				<div>
					<Button
						isLink
						variant="link"
						onClick={ goPrev }
						className="simpay-setup-wizard-subtle-link"
						disabled={ isBusy }
					>
						{ __( '← Previous Step', 'simple-pay' ) }
					</Button>
				</div>

				<div style={ { display: 'flex', justifyContent: 'center' } }>
					<Button
						isLink
						variant="link"
						onClick={ onSkip }
						style={ { marginRight: '16px' } }
						className="simpay-setup-wizard-subtle-link"
						disabled={ isBusy }
					>
						{ __( 'Skip Step', 'simple-pay' ) }
					</Button>

					<ContinueButton
						onClick={ onSave }
						isBusy={ isBusy }
						disabled={ isBusy }
					>
						{ __( 'Save and Continue →', 'simple-pay' ) }
					</ContinueButton>
				</div>
			</CardFooter>
		</>
	);
}
