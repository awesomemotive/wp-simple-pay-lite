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
import { useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { CardFooter, CardBody, ContinueButton } from './../../components';
import { useSettings } from './../../hooks';

export function Emails( { goPrev, goNext, licenseData } ) {
	const {
		settings,
		rawSettings,
		discardChanges,
		editSettings,
		saveSettings,
	} = useSettings();

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

	function onSave() {
		saveSettings();
		goNext();
	}

	const {
		'email_payment-confirmation': emailPaymentConfirmation,
		'email_payment-notification': emailPaymentNotification,
		'email_payment-notification_to': emailPaymentNotificationTo,
		'email_upcoming-invoice': emailUpcomingInvoice,
		'email_invoice-confirmation': emailInvoiceConfirmation,
	} = settings;
	const {
		features: {
			subscriptions: hasSubscriptions,
			enhanced_subscriptions: hasEnhancedSubscriptions,
		},
	} = licenseData;

	return (
		<>
			<CardBody>
				<ul className="simpay-setup-wizard-toggle-list">
					<li>
						<label htmlFor="email_payment-confirmation">
							<h3>{ __( 'Payment Receipt', 'simple-pay' ) }</h3>
							<p>
								{ __(
									'Send a payment receipt email to the customer upon successful payment.',
									'simple-pay'
								) }
							</p>
						</label>
						<FormToggle
							id="email_payment-confirmation"
							checked={ 'no' !== emailPaymentConfirmation }
							onChange={ ( { target } ) =>
								editSettings( {
									'email_payment-confirmation': target.checked
										? 'yes'
										: 'no',
								} )
							}
						/>
					</li>

					<li>
						<label htmlFor="email_payment-notification">
							<h3>
								{ __( 'Payment Notification', 'simple-pay' ) }
							</h3>
							<p>
								{ __(
									'Receive an email notification when a new payment is made.',
									'simple-pay'
								) }
							</p>
						</label>
						<FormToggle
							id="email_payment-notification"
							checked={ 'no' !== emailPaymentNotification }
							onChange={ ( { target } ) =>
								editSettings( {
									'email_payment-notification': target.checked
										? 'yes'
										: 'no',
								} )
							}
						/>
					</li>

					{ 'no' !== emailPaymentNotification && (
						<li className="simpay-setup-wizard-toggle-list__child">
							<TextControl
								label={ __( 'Send to:', 'simple-pay' ) }
								value={ emailPaymentNotificationTo }
								onChange={ ( value ) =>
									editSettings( {
										'email_payment-notification_to': value,
									} )
								}
							/>
						</li>
					) }

					{ hasEnhancedSubscriptions && (
						<li>
							<label htmlFor="email_invoice-confirmation">
								<h3>
									{ __( 'Invoice Receipt', 'simple-pay' ) }
								</h3>
								<p>
									{ __(
										'Send a payment receipt email to the customer upon successful invoice.',
										'simple-pay'
									) }
								</p>
							</label>
							<FormToggle
								id="email_payment-confirmation"
								checked={ 'no' !== emailInvoiceConfirmation }
								onChange={ ( { target } ) =>
									editSettings( {
										'email_invoice-confirmation': target.checked
											? 'yes'
											: 'no',
									} )
								}
							/>
						</li>
					) }

					{ hasSubscriptions && (
						<li>
							<label htmlFor="email_upcoming-invoice">
								<h3>
									{ __( 'Upcoming Invoice', 'simple-pay' ) }
								</h3>
								<p>
									{ __(
										'Remind customers of upcoming invoices and allow payment method changes.',
										'simple-pay'
									) }
								</p>
							</label>
							<FormToggle
								id="email_upcoming-invoice"
								checked={ 'no' !== emailUpcomingInvoice }
								onChange={ ( { target } ) =>
									editSettings( {
										'email_upcoming-invoice': target.checked
											? 'yes'
											: 'no',
									} )
								}
							/>
						</li>
					) }
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

					<ContinueButton onClick={ onSave } ref={ toFocus }>
						{ __( 'Save and Continue →', 'simple-pay' ) }
					</ContinueButton>
				</div>
			</CardFooter>
		</>
	);
}
