/**
 * Internal dependencies
 */
import { customers, sessions } from '@wpsimplepay/api';

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Submit Stripe Checkout Payment Form.
 *
 * @since 4.2.0
 *
 * @param {PaymentForm} paymentForm
 */
async function submit( paymentForm ) {
	const { error: onError, __unstableLegacyFormData } = paymentForm;

	let customerId = null;
	const { hasCustomerFields } = __unstableLegacyFormData;

	// Only generate a custom Customer if we need to map on-page form fields.
	if ( hasCustomerFields ) {
		const {
			customer: { id },
		} = await customers.create( {}, paymentForm );

		customerId = id;
	}

	// Generate a Checkout Session.
	const { sessionId } = await sessions.create(
		{
			customer_id: customerId,
		},
		paymentForm
	);

	// Redirect to Stripe.
	return paymentForm.stripeInstance
		.redirectToCheckout( {
			sessionId,
		} )
		.then( ( result ) => {
			if ( result.error ) {
				onError( result.error );
			}

			return result;
		} );
}

export default submit;
