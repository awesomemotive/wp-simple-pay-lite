/**
 * Primes a cart based on Payment Form data.
 *
 * @todo This might not be any better than the previous convertFormDataToCartData
 *       but it helps reduce code duplication between payment form types.
 *
 * @param {PaymentForm} paymentForm Payment Form
 * @param {Cart} cart Payment Form cart.
 */
export function __unstableUpdatePaymentFormCart( paymentForm, cart ) {
	const {
		state: { price, taxRates },
	} = paymentForm;

	// Create a cart from the default price.
	const {
		unit_amount: unitAmount,
		currency,
		can_recur: canRecur,
		recurring,
	} = price;

	cart.update( {
		currency,
		taxRates,
	} );

	cart.addLineItem( {
		id: 'setup-fee',
		title: 'Initial Setup Fee',
		amount: 0,
		quantity: 1,
		subscription: false,
	} );

	cart.addLineItem( {
		id: 'plan-setup-fee',
		title: 'Plan Setup Fee',
		amount: 0,
		quantity: 1,
		subscription: false,
	} );

	cart.addLineItem( {
		id: 'base',
		price,
		title: recurring && false === canRecur ? 'Subscription' : 'One Time',
		amount: unitAmount,
		quantity: 1,
		subscription:
			recurring && false === canRecur
				? {
						isTrial: !! recurring.trial_period_days,
						interval: recurring.interval,
						intervalCount: recurring.interval_count,
				  }
				: false,
	} );

	return cart;
}
