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
	const {
		error: onError,
		disable: disableForm,
		__unstableLegacyFormData,
	} = paymentForm;

	let customerId = null;
	const { hasCustomerFields } = __unstableLegacyFormData;

	onError( '' );
	disableForm();

	// Only generate a custom Customer if we need to map on-page form fields.
	if ( hasCustomerFields ) {
		const customerData = await customers
			.create( {}, paymentForm )
			.catch( onError );

		if ( ! customerData ) {
			return;
		}

		const {
			customer: { id },
		} = customerData;

		customerId = id;
	}

	// Generate a Checkout Session.
	const session = await sessions
		.create(
			{
				customer_id: customerId,
				payment_method_type: __unstableLegacyFormData.paymentMethods
					? __unstableLegacyFormData.paymentMethods[ 0 ].id
					: 'card',
			},
			paymentForm
		)
		.catch( onError );

	// Bail if there was an error.
	if ( ! session ) {
		return;
	}

	const {
		sessionId,
		session: { url },
		redirect_type: redirectType,
	} = session;

	// Redirect to Stripe.
	if ( 'stripe' === redirectType ) {
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

	window.location.href = url;
}

export default submit;
