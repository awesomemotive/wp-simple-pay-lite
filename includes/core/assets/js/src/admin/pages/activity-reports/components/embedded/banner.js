import { loadConnectAndInitialize } from '@stripe/connect-js';
import {
	ConnectNotificationBanner,
	ConnectComponentsProvider,
} from '@stripe/react-connect-js';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Spinner } from '@wordpress/components';
import useRestApiReport from '../../hooks/use-rest-api-report';

export default function Banner() {
	const [ isLoading, setIsLoading ] = useState( true );
	const [ hasError, setHasError ] = useState( false );
	const [ connectInstance, setConnectInstance ] = useState( null );
	const [ notifications, setNotifications ] = useState( {
		total: 0,
		actionRequired: 0,
	} );

	// Use the REST API hook instead of direct apiFetch
	const embeddingSecretReport = useRestApiReport(
		'/wpsp/__internal__/embedding-secret/banner',
		{},
		[]
	);

	const handleNotificationsChange = ( response ) => {
		setNotifications( {
			total: response.total,
			actionRequired: response.actionRequired,
		} );
	};

	useEffect( () => {
		async function initializeConnect() {
			try {
				if ( embeddingSecretReport.isLoading || ! embeddingSecretReport.data ) {
					return;
				}

				const { client_secret: initialClientSecret, publishable_key: publishableKey } = 
					embeddingSecretReport.data;

				document
					.querySelector(
						'#simpay-admin-page-activity-reports-embedded-error'
					)
					.setAttribute( 'hidden', '' );

				const instance = await loadConnectAndInitialize( {
					publishableKey,
					fetchClientSecret: async () => {
						return initialClientSecret;
					},
				} );

				setConnectInstance( instance );
			} catch ( error ) {
				setHasError( true );
				document
					.querySelector(
						'#simpay-admin-page-activity-reports-embedded-error'
					)
					.removeAttribute( 'hidden' );
			} finally {
				setIsLoading( false );
			}
		}

		initializeConnect();
	}, [ embeddingSecretReport ] );

	if ( hasError || notifications.actionRequired === 0 ) {
		return null;
	}

	return (
		<Card elevation={ 2 }>
			<CardHeader>
				<h2 className="simpay-activity-reports-card-title">
					{ __( 'Notifications', 'simple-pay' ) }
				</h2>
				<div className="simpay-notification-warning">
					{ __(
						'Action required: Please resolve the notifications below.',
						'simple-pay'
					) }
				</div>
			</CardHeader>
			<CardBody>
				{ isLoading ? (
					<div className="simpay-activity-reports-loading">
						<Spinner />
					</div>
				) : (
					<div className="container">
						<ConnectComponentsProvider
							connectInstance={ connectInstance }
						>
							<ConnectNotificationBanner
								onNotificationsChange={
									handleNotificationsChange
								}
								setCollectionOptions={ {
									fields: 'eventually_due',
									futureRequirements: 'include',
								} }
							/>
						</ConnectComponentsProvider>
					</div>
				) }
			</CardBody>
		</Card>
	);
}
