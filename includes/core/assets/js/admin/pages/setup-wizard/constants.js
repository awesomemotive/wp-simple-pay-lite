/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	Analytics,
	Emails,
	License,
	NextStepsLite,
	NextStepsPro,
	Stripe,
} from './steps';

export const STEPS = {
	pro: [
		{
			id: 'license',
			title: __( 'Activate Your License', 'simple-pay' ),
			content: License,
		},
		{
			id: 'stripe',
			title: __( 'Connect to Stripe', 'simple-pay' ),
			content: Stripe,
		},
		{
			id: 'emails',
			title: __( 'Configure Emails', 'simple-pay' ),
			content: Emails,
		},
		{
			id: 'next-steps',
			title: __( '🎉 Setup Complete', 'simple-pay' ),
			content: NextStepsPro,
		},
	],
	lite: [
		{
			id: 'stripe',
			title: __( 'Connect to Stripe', 'simple-pay' ),
			content: Stripe,
		},
		{
			id: 'analytics',
			title: __(
				'Help Improve WP Simple Pay + Smart Recommendations',
				'simple-pay'
			),
			content: Analytics,
		},
		{
			id: 'next-steps',
			title: __( '🎉 Setup Complete', 'simple-pay' ),
			content: NextStepsLite,
		},
	],
};
