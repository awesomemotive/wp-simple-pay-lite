/**
 * Internal dependencies
 */
import { registerPaymentFormType } from '@wpsimplepay/payment-forms';
import { default as setup } from './setup.js';
import { default as submit } from './submit.js';
import { default as enable } from './enable.js';
import { default as disable } from './disable.js';
import { default as error } from './error.js';

const type = 'stripe-checkout';

registerPaymentFormType( type, {
	type,
	setup,
	submit,
	enable,
	disable,
	error,
} );
