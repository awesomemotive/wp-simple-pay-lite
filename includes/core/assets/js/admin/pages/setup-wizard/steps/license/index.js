/* global simpaySetupWizard */

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import {
	createInterpolateElement,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { CardFooter, CardBody, ContinueButton } from './../../components';

const {
	ajaxUrl,
	accountLicensesUrl,
	licenseNonce,
	license,
} = simpaySetupWizard;
const { key: existingLicense } = license;

export function License( { goNext, setLicenseData } ) {
	const [ licenseKey, setLicenseKey ] = useState( existingLicense );
	const [ isBusy, setIsBusy ] = useState( false );
	const [ error, setError ] = useState( null );
	const { createSuccessNotice } = useDispatch( 'core/notices' );
	const toFocus = useRef();

	useLayoutEffect( () => {
		if ( ! toFocus.current ) {
			return;
		}

		toFocus.current.focus();
	}, [] );

	/**
	 * Activate a license key via admin-ajax.php
	 */
	function onActivate() {
		setIsBusy( true );

		// eslint-disable-next-line no-undef
		const body = new FormData();
		body.append( 'action', 'simpay_activate_license' );
		body.append( 'nonce', licenseNonce );
		body.append( 'license', licenseKey );

		apiFetch( {
			url: ajaxUrl,
			method: 'POST',
			body,
		} )
			.then( ( { success, data: { message, license } } ) => {
				if ( ! success ) {
					throw {
						message,
					};
				}

				setLicenseData( license );

				createSuccessNotice( __( 'License activated', 'simple-pay' ), {
					type: 'snackbar',
				} );

				setIsBusy( false );
				goNext();
			} )
			.catch( ( { message } ) => {
				setError( message );
				setIsBusy( false );
			} );
	}

	const classNames = classnames( 'simpay-setup-wizard-large-input', {
		'is-error': error,
	} );

	return (
		<>
			<CardBody>
				<p>
					{ createInterpolateElement(
						__(
							'To get started, please enter your license key. Retrieve your license key from your <strong><a>WP Simple Pay account</a></strong> or purchase receipt email.',
							'simple-pay'
						),
						{
							strong: <strong />,
							a: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a
									href={ accountLicensesUrl }
									target="_blank"
									rel="noopener noreferrer"
								/>
							),
						}
					) }
				</p>

				<TextControl
					label={ __( 'License Key', 'simple-pay' ) }
					value={ licenseKey }
					className={ classNames }
					onChange={ ( value ) => setLicenseKey( value ) }
					disabled={ isBusy }
					help={
						error
							? error
							: __(
									'An active license ensures automatic updates and the latest features.',
									'simple-pay'
							  )
					}
					ref={ toFocus }
				/>
			</CardBody>

			<CardFooter justify="flex-end" align="center">
				<ContinueButton
					onClick={ onActivate }
					disabled={ '' === licenseKey || isBusy }
					isBusy={ isBusy }
				>
					{ __( 'Activate and Continue →', 'simple-pay' ) }
				</ContinueButton>
			</CardFooter>
		</>
	);
}
