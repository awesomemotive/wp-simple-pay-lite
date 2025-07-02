import { loadConnectAndInitialize } from '@stripe/connect-js';
import {
	ConnectBalances,
	ConnectComponentsProvider,
} from '@stripe/react-connect-js';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Card, CardBody, CardHeader, Spinner } from '@wordpress/components';
import useRestApiReport from '../../hooks/use-rest-api-report';
import BalanceError from './balance-error';

export default function Balance({ onTestModeChange }) {
	const [ connectInstance, setConnectInstance ] = useState( null );
	const [ hasError, setHasError ] = useState( false );
	const [ isTestMode, setIsTestMode ] = useState( false );

	const { data: embeddingSecret, isLoading, error } = useRestApiReport(
		'/wpsp/__internal__/embedding-secret/balance',
		{},
		[]
	);

	useEffect(() => {
		// Check if error is due to test mode
		if (error?.data?.status === 400 && error?.message === 'This feature is only available in live mode') {
			setIsTestMode(true);
			onTestModeChange?.(true);
			return;
		}

		// Show error card for invalid license
		if (error?.code === 'rest_invalid_license') {
			setHasError(true);
			return;
		}
	}, [error, onTestModeChange]);

	useEffect( () => {
		async function initializeConnect() {
			try {
				if ( ! embeddingSecret ) {
					return;
				}

				const {
					client_secret: initialClientSecret,
					publishable_key: publishableKey,
				} = embeddingSecret;

				// Hide any existing error message
				const errorElement = document.querySelector(
					'#simpay-admin-page-activity-reports-embedded-error'
				);
				
				if (errorElement) {
					errorElement.setAttribute('hidden', '');
				}

				if (!publishableKey || !initialClientSecret) {
					throw new Error('Missing required Stripe credentials');
				}

				const instance = await loadConnectAndInitialize({
					publishableKey,
					fetchClientSecret: async () => {
						if (!initialClientSecret) {
							throw new Error('Client secret is required');
						}
						return initialClientSecret;
					},
				});

				setConnectInstance(instance);
			} catch (error) {
				console.error('Stripe Connect initialization error:', error);
				setHasError(true);
				
				const errorElement = document.querySelector(
					'#simpay-admin-page-activity-reports-embedded-error'
				);
				
				if (errorElement) {
					errorElement.removeAttribute('hidden');
				}
			}
		}

		initializeConnect();
	}, [embeddingSecret] );

	// Don't render anything if in test mode
	if (isTestMode) {
		return null;
	}

	return (
		<Card elevation={ 2 } style={ { width: '100%', height: '100%' } }>
			<CardHeader>
				<h2 className="simpay-activity-reports-card-title">
					{ __( 'Balance', 'simple-pay' ) }
				</h2>
			</CardHeader>
			<CardBody>
				{ hasError ? (
					<BalanceError />
				) : isLoading || ! connectInstance ? (
					<div className="simpay-activity-reports-loading">
						<Spinner />
					</div>
				) : (
					<div className="container">
						<ConnectComponentsProvider
							connectInstance={ connectInstance }
						>
							<ConnectBalances />
						</ConnectComponentsProvider>
					</div>
				) }
			</CardBody>
		</Card>
	);
}
