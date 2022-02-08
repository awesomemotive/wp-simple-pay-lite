/* global simpaySetupWizard */

/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	Button,
	FormToggle,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	createInterpolateElement,
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { CardFooter, CardBody, ContinueButton } from './../../components';
import { useSettings } from './../../hooks';

const {
	adminEmail: bootAdminEmail,
	subscribeNonce,
	isLite,
	ajaxUrl,
	analyticsDocsUrl,
} = simpaySetupWizard;

export function Analytics( { goPrev, goNext } ) {
	const {
		settings,
		rawSettings,
		discardChanges,
		editSettings,
		saveSettings,
	} = useSettings();

	const [ isOptedIn, setIsOptedIn ] = useState( '0' === isLite );
	const [ adminEmail, setAdminEmail ] = useState( bootAdminEmail );
	const [ isBusy, setIsBusy ] = useState( false );

	const toFocus = useRef();

	useLayoutEffect( () => {
		if ( ! toFocus.current ) {
			return;
		}

		toFocus.current.focus();
	}, [ rawSettings ] );

	useEffect( () => {
		if ( ! settings || ! settings.usage_tracking_opt_in ) {
			return;
		}

		setIsOptedIn( 'yes' === settings.usage_tracking_opt_in );
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
					label={ __( 'Your email address:', 'simple-pay' ) }
					value={ adminEmail }
					className="simpay-setup-wizard-large-input"
					onChange={ ( value ) => setAdminEmail( value ) }
					help={ __(
						'Your email is needed, so you can receive recommendations.',
						'simple-pay'
					) }
					ref={ toFocus }
				/>

				<hr />

				<ul className="simpay-setup-wizard-toggle-list">
					<li>
						<label htmlFor="email_payment-confirmation">
							<strong>
								{ __( 'Send usage analytics', 'simple-pay' ) }
							</strong>
							<p>
								{ createInterpolateElement(
									__(
										'Get improved features by sharing data via <a>usage analytics</a> that shows us how you are using WP Simple Pay.',
										'simple-pay'
									),
									{
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										a: <a href={ analyticsDocsUrl } />,
									}
								) }
							</p>
						</label>

						<FormToggle
							id="email_payment-confirmation"
							checked={ isOptedIn }
							onChange={ ( { target } ) => {
								setIsOptedIn( target.checked );
								editSettings( {
									usage_tracking_opt_in: target.checked
										? 'yes'
										: 'no',
								} );
							} }
						/>
					</li>
				</ul>
			</CardBody>

			<CardFooter justify="space-between" align="center">
				<div>
					<Button
						isLink
						variant="link"
						onClick={ goPrev }
						className="simpay-setup-wizard-subtle-link"
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
					>
						{ __( 'Skip Step', 'simple-pay' ) }
					</Button>

					<ContinueButton onClick={ onSave } isBusy={ isBusy }>
						{ __( 'Save and Continue →', 'simple-pay' ) }
					</ContinueButton>
				</div>
			</CardFooter>
		</>
	);
}
