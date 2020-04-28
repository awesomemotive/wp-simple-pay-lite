/**
 * Internal dependencies
 */
import { setup } from './setup.js';
import { submit } from './submit.js';
import { onPaymentFormError as onError } from '@wpsimplepay/core/frontend/payment-forms';

export default {
	setup,
	submit,
	onError,
};
