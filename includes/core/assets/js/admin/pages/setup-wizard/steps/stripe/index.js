/* global simpaySetupWizard */

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, check } from '@wordpress/icons';
import {
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { getQueryArg, removeQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import {
	CardFooter,
	CardBody,
	StripeButton,
	StripeLogo,
} from './../../components';

const FEATURES = [
	__( 'Secure payments', 'simple-pay' ),
	__( 'Transparent pricing', 'simple-pay' ),
	__( '135+ currencies', 'simple-pay' ),
	__( '35+ countries, 40+ languages', 'simple-pay' ),
	__( 'Optimized for conversions', 'simple-pay' ),
];

const { stripeConnectUrl } = simpaySetupWizard;

export function Stripe( { goNext, goPrev, hasPrev } ) {
	const [ isConnecting, setIsConnecting ] = useState( false );
	const [ isRedirecting, setIsRedirecting ] = useState( false );

	const isConnectedFlag = getQueryArg(
		window.location.href,
		'stripe-is-connected'
	);

	const isNotConnectedFlag = getQueryArg(
		window.location.href,
		'wpsp_gateway_connect_error'
	);

	const toFocus = useRef();

	useLayoutEffect( () => {
		if ( ! toFocus.current ) {
			return;
		}

		toFocus.current.focus();
	}, [] );

	const { createSuccessNotice } = useDispatch( 'core/notices' );

	useEffect( () => {
		if ( ! isConnectedFlag || isNotConnectedFlag ) {
			return;
		}

		setIsConnecting( true );

		const goNextTimeout = setTimeout( () => {
			goNext();

			createSuccessNotice( __( 'Connected to Stripe', 'simple-pay' ), {
				type: 'snackbar',
			} );

			window.history.replaceState(
				'',
				'',
				removeQueryArgs( window.location.href, 'stripe-is-connected' )
			);
		}, 1200 );

		return () => {
			clearTimeout( goNextTimeout );
		};
	}, [ isConnectedFlag ] );

	useEffect( () => {
		if ( ! isNotConnectedFlag ) {
			return;
		}

		createSuccessNotice(
			__(
				'Unable to connect to Stripe. Please try again.',
				'simple-pay'
			),
			{
				type: 'snackbar',
			}
		);
	}, [ isNotConnectedFlag ] );

	return (
		<>
			<CardBody>
				<div className="simpay-setup-wizard-content-list">
					<div className="simpay-setup-wizard-content-list__content">
						<p>
							{ __(
								"Millions of companies of all sizes—from startups to Fortune 500s—use Stripe's software and APIs to accept payments, send payouts, and manage their businesses online.",
								'simple-pay'
							) }
						</p>

						<p>
							<strong>
								{ __(
									'Create or connect an existing account to instantly start accepting payments.',
									'simple-pay'
								) }
							</strong>
						</p>
					</div>

					<div className="simpay-setup-wizard-content-list__list">
						<ul className="simpay-setup-wizard-check-list">
							{ FEATURES.map( ( feature ) => (
								<li key={ feature }>
									<Icon icon={ check } />
									{ feature }
								</li>
							) ) }
						</ul>
					</div>
				</div>
			</CardBody>

			<CardFooter justify="space-between" align="center">
				<div>
					{ hasPrev && (
						<Button
							isLink
							variant="link"
							onClick={ goPrev }
							className="simpay-setup-wizard-subtle-link"
							disabled={ isConnecting || isRedirecting }
						>
							{ __( '← Previous Step', 'simple-pay' ) }
						</Button>
					) }
				</div>

				<div style={ { display: 'flex', justifyContent: 'center' } }>
					<StripeButton
						href={ stripeConnectUrl }
						onClick={ () => setIsRedirecting( true ) }
						isBusy={ isConnecting || isRedirecting }
						disabled={ isConnecting || isRedirecting }
						ref={ toFocus }
					>
						{ isConnecting ? (
							__( 'Connecting…', 'simple-pay' )
						) : (
							<>
								{ __( 'Connect with', 'simple-pay' ) }
								<StripeLogo />
							</>
						) }
					</StripeButton>
				</div>
			</CardFooter>
		</>
	);
}
