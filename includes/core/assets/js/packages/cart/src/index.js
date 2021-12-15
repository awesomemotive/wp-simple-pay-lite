export * from './cart.js';
export * from './line-item.js';

/**
 * Converts current `formData` to a structured cart.
 *
 * @since 3.7.0
 *
 * @param {Object} formData Legacy form data.
 * @return {Object} data Cart data.
 */
export function convertFormDataToCartData( formData ) {
	const { debugLog, convertToCents } = window.spShared;

	const {
		booleans: { isZeroDecimal: isNonDecimalCurrency },
	} = window.spGeneral;

	const {
		stripeParams: { currency },

		// Items.
		amount,
		isSubscription,
		quantity,
		isTrial,
		planInterval,
		planIntervalCount,

		// Fees.
		feePercent,
		feeAmount,
		taxPercent,
	} = formData;

	const data = {
		currency,
		items: [],
		taxPercent: 0,
		isNonDecimalCurrency,
	};

	//
	// Add items.
	//

	// Initial Setup Fee.
	data.items.push( {
		id: 'setup-fee',
		title: 'Initial Setup Fee',
		amount: 0,
		quantity: 1,
		subscription: false,
	} );

	// Plan Setup Fee.
	data.items.push( {
		id: 'plan-setup-fee',
		title: 'Plan Setup Fee',
		amount: 0,
		quantity: 1,
		subscription: false,
	} );

	// Base item.
	if ( isSubscription ) {
		data.items.push( {
			id: 'base',
			title: 'Subscription',
			amount: convertToCents( amount ),
			quantity,
			subscription: {
				isTrial,
				interval: planInterval,
				intervalCount: planIntervalCount,
			},
		} );
	} else {
		let singleAmount = amount;

		if ( ! isNaN( feeAmount ) && feeAmount > 0 ) {
			singleAmount += feeAmount;

			debugLog(
				'feeAmount:',
				'Arbitrary fee amounts should be added to the base amount directly.'
			);
		}

		data.items.push( {
			id: 'base',
			title: 'One-time amount',
			amount: convertToCents( singleAmount ),
			quantity,
			subscription: false,
		} );
	}

	// Tax.
	if ( ! isNaN( taxPercent ) ) {
		data.taxPercent = taxPercent;
	}

	// Add any arbitrary fee percentage to tax percentage.
	// @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1161
	if ( ! isNaN( feePercent ) && feePercent > 0 ) {
		data.taxPercent = data.taxPercent + feePercent;
		debugLog(
			'feePercent:',
			'Arbitrary fee percentages should be added to the taxPercent directly.'
		);
	}

	return data;
}
