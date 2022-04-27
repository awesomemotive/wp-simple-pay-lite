/**
 * Internal dependencies
 */
import LineItem from './line-item.js';

/**
 * Cart
 */
export const Cart = class Cart {
	/**
	 * Creates a cart.
	 *
	 * @since 3.7.0
	 *
	 * @see `update()`
	 *
	 * @param {Object} args Cart arguments.
	 * @return {Cart} Cart.
	 */
	constructor( args ) {
		// Set defaults.
		// @todo add support for classProperties.
		this.items = [];
		this.currency = 'usd';
		this.taxPercent = 0;
		this.taxRates = [];
		this.coupon = false;
		this.isNonDecimalCurrency = false;

		this.update( args );
	}

	/**
	 * Updates the cart.
	 *
	 * @since 3.7.0
	 *
	 * @param {Object} args Cart arguments.
	 * @param {number} args.taxPercent Tax percentage.
	 * @param {Array} args.taxRates Tax rates.
	 * @param {false|Object} args.coupon Stripe Coupon.
	 * @param {boolean} args.isNonDecimalCurrency If the currency is non-decimal based.
	 * @return {Cart} Cart.
	 */
	update( args ) {
		// Parse and retrieve specific arguments.
		const {
			currency,
			taxPercent,
			taxRates,
			coupon,
			isNonDecimalCurrency,
		} = {
			...this,
			...args,
		};

		// Set currency.
		if ( 'string' !== typeof currency ) {
			throw {
				id: 'invalid-currency',
				message: 'Currency must be a string.',
			};
		} else {
			this.currency = currency;
		}

		// Set fees.
		if ( isNaN( taxPercent ) ) {
			throw {
				id: 'invalid-tax-percent',
				message: 'Tax percentage must be a number.',
			};
		} else {
			this.taxPercent = parseFloat( taxPercent );
		}

		// Tax rates.
		if ( taxRates && Array.isArray( taxRates ) ) {
			this.taxRates = taxRates;
		}

		// Set coupon.
		// @todo Validate coupon data.
		if ( ! ( false === coupon || 'object' === typeof coupon ) ) {
			throw {
				id: 'invalid-coupon',
				message: 'Coupon must be a false or contain coupon data.',
			};
		} else {
			this.coupon = coupon;
		}

		if ( 'boolean' !== typeof isNonDecimalCurrency ) {
			throw {
				id: 'invalid-non-decimal-currency',
				message: 'Declaring a non-decimal currency must be a boolean.',
			};
		} else {
			this.isNonDecimalCurrency = isNonDecimalCurrency;
		}

		return this;
	}

	/**
	 * Resets the cart.
	 *
	 * @since 3.7.0
	 *
	 * @return {Cart} Cart.
	 */
	reset() {
		return new Cart();
	}

	/**
	 * Returns the currency used by the base item, and therefore, the whole cart.
	 *
	 * @since 4.1.0
	 *
	 * @return {string}
	 */
	getCurrency() {
		let currency;
		const { price } = this.getLineItem( 'base' );

		if ( null === price ) {
			currency = spGeneral.strings.currency;
		} else {
			currency = price.currency;
		}

		return currency;
	}

	/**
	 * Returns the currency symbol used by the base item, and therefore,
	 * the whole cart.
	 *
	 * @since 4.1.0
	 *
	 * @return {string}
	 */
	getCurrencySymbol() {
		let currencySymbol;
		const { price } = this.getLineItem( 'base' );

		if ( null === price ) {
			currencySymbol = spGeneral.strings.currencySymbol;
		} else {
			currencySymbol = price.currency_symbol;
		}

		return currencySymbol;
	}

	/**
	 * Determines if the currenet cart is using a zero decimal currency.
	 *
	 * @since 4.1.0
	 *
	 * @return {bool}
	 */
	isZeroDecimal() {
		const { price } = this.getLineItem( 'base' );
		const { is_zero_decimal: isZeroDecimal } = price;

		return isZeroDecimal;
	}

	/**
	 * Retrieves coupon information.
	 *
	 * @since 3.7.0
	 *
	 * @return {Object} Stripe Coupon.
	 */
	getCoupon() {
		return this.coupon;
	}

	/**
	 * Retrieves a cart's inclusive tax percentage.
	 *
	 * @since 3.7.0
	 *
	 * @param {string} taxCalculation Tax caclulation type.
	 * @return {number} Cart's inclusive tax percentage.
	 */
	getTaxPercent( taxCalculation ) {
		return this.getTaxRates().reduce(
			( percent, { calculation, percentage } ) => {
				if ( taxCalculation !== calculation ) {
					return percent;
				}

				return ( percent += percentage );
			},
			0
		);
	}

	/**
	 * Retrieves the cart's tax decimal amount for calculations.
	 *
	 * @since 3.7.0
	 *
	 * @return {number}
	 */
	getTaxDecimal() {
		return this.taxPercent / 100;
	}

	/**
	 * Retrieves the cart's tax rates.
	 *
	 * @since 4.1.0
	 *
	 * @return {Array}
	 */
	getTaxRates() {
		return this.taxRates;
	}

	/**
	 * Retrieves the items in the cart.
	 *
	 * @since 3.7.0
	 *
	 * @return {Array} List of cart items.
	 */
	getLineItems() {
		return this.items;
	}

	/**
	 * Retrieves subtotal.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart subtotal.
	 */
	getSubtotal() {
		return this.getLineItems().reduce( ( subtotal, lineItem ) => {
			// Return early if the line item has a trial.
			if ( lineItem.hasFreeTrial() ) {
				return subtotal;
			}

			// Add the subtotal of a line item without a trial.
			return ( subtotal += Math.round(
				lineItem.getUnitPrice() * lineItem.getQuantity()
			) );
		}, 0 );
	}

	/**
	 * Retrieves the total discount amount.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart discount amount.
	 */
	getDiscount() {
		const coupon = this.getCoupon();
		const { percent_off: percentOff, amount_off: amountOff } = coupon;

		let discount = 0;

		if ( false === coupon ) {
			return discount;
		}

		if ( percentOff ) {
			discount += Math.round( this.getSubtotal() * ( percentOff / 100 ) );
		} else if ( amountOff ) {
			discount += amountOff;
		}

		return discount;
	}

	/**
	 * Retrieves the total tax amount.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart tax.
	 */
	getTax() {
		return this.getLineItems().reduce( ( tax, lineItem ) => {
			return ( tax += lineItem.getTax() );
		}, 0 );
	}

	/**
	 * Retrieves the cart's tax amounts for corresponding tax rate IDs.
	 *
	 * @since 4.1.0
	 *
	 * @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1198#issuecomment-776724336
	 *
	 * @return {Object[]}
	 */
	getAppliedTaxRates() {
		const lineItems = this.getLineItems();

		const exclusiveTaxRates = this.getTaxRates().filter(
			( { calculation } ) => calculation !== 'inclusive'
		);
		const totalExclusiveTaxDecimal =
			this.getTaxPercent( 'exclusive' ) / 100;

		const inclusiveTaxRates = this.getTaxRates().filter(
			( { calculation } ) => calculation !== 'exclusive'
		);
		const inclusiveTaxPercent = this.getTaxPercent( 'inclusive' );
		const totalInclusiveTaxDecimal = inclusiveTaxPercent / 100;

		const taxRateAmounts = {};

		lineItems.forEach( ( lineItem ) => {
			if ( lineItem.hasFreeTrial() ) {
				return;
			}

			const lineExclusiveTax = lineItem.getTax();
			const lineTaxableAmount = lineItem.getTaxableAmount();
			const lineInclusiveTaxAmount = lineItem.getInclusiveTaxAmount();

			exclusiveTaxRates.forEach( ( taxRate, i ) => {
				const { id, percentage } = taxRate;
				const taxDecimal = percentage / 100;
				let taxAmount = 0;

				if ( i === exclusiveTaxRates.length - 1 ) {
					const otherExclusiveTaxRates = exclusiveTaxRates.filter(
						( { id: taxRateId } ) => taxRateId !== id
					);

					const otherExclusiveTaxPercent = otherExclusiveTaxRates.reduce(
						( percent, { percentage } ) => {
							return ( percent += percentage );
						},
						0
					);

					const otherExclusiveTaxDecimal =
						otherExclusiveTaxPercent / 100;

					const remainingExclusiveTax = Math.floor(
						lineTaxableAmount * otherExclusiveTaxDecimal
					);

					taxAmount = lineExclusiveTax - remainingExclusiveTax;
				} else {
					taxAmount = Math.floor(
						lineExclusiveTax *
							( taxDecimal / totalExclusiveTaxDecimal )
					);
				}

				taxRateAmounts[ id ] = [
					...( taxRateAmounts[ id ] || [] ),
					taxAmount,
				];
			} );

			inclusiveTaxRates.forEach( ( taxRate, i ) => {
				const { id, percentage } = taxRate;
				const taxDecimal = percentage / 100;
				let taxAmount;

				if ( i === inclusiveTaxRates.length - 1 ) {
					const otherInclusiveTaxRates = inclusiveTaxRates.filter(
						( { id: taxRateId } ) => taxRateId !== id
					);

					const otherInclusiveTaxAmount = otherInclusiveTaxRates.reduce(
						( taxAmount, { percentage } ) => {
							return ( taxAmount += Math.floor(
								lineInclusiveTaxAmount *
									( percentage /
										100 /
										totalInclusiveTaxDecimal )
							) );
						},
						0
					);

					taxAmount =
						lineInclusiveTaxAmount - otherInclusiveTaxAmount;
				} else {
					taxAmount = Math.floor(
						lineInclusiveTaxAmount *
							( taxDecimal / totalInclusiveTaxDecimal )
					);
				}

				taxRateAmounts[ id ] = [
					...( taxRateAmounts[ id ] || [] ),
					taxAmount,
				];
			} );
		} );

		return taxRateAmounts;
	}

	/**
	 * Retrieves the total.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart total.
	 */
	getTotal() {
		return this.getLineItems().reduce( ( total, lineItem ) => {
			return ( total += lineItem.getTotal() );
		}, 0 );
	}

	/**
	 * Retrieves the total due today.
	 *
	 * @since 4.5.0
	 *
	 * @return {number} Cart total due today.
	 */
	getTotalDueToday() {
		return this.getLineItems().reduce( ( total, lineItem ) => {
			// Return current total if the line item has a free trial.
			if ( lineItem.hasFreeTrial() ) {
				return total;
			}

			// Add line item total to existing total.
			return ( total += lineItem.getTotal() );
		}, 0 );
	}

	/**
	 * Retrieves the recurring total.
	 *
	 * Calculates amounts manually by applying the whole discount to the line item.
	 * Assumes discounts apply indefinitely.
	 *
	 * @since 4.1.0
	 *
	 * @return {number} Cart recurring total.
	 */
	getRecurringTotal() {
		const recurring = this.getLineItem( 'base' );
		let recurringSubtotal =
			recurring.getUnitPrice() * recurring.getQuantity();
		recurringSubtotal = Math.round(
			recurringSubtotal - this.getDiscount()
		);

		const taxRates = this.getTaxRates();
		const taxRate = this.getTaxPercent( 'inclusive' ) / 100;

		const inclusiveTaxAmount = Math.round(
			recurringSubtotal - recurringSubtotal / ( 1 + taxRate )
		);

		const postInclusiveTaxAmount = Math.round(
			recurringSubtotal - inclusiveTaxAmount
		);
		const taxAmount = taxRates.reduce(
			( tax, { percentage, calculation } ) => {
				if ( 'inclusive' === calculation ) {
					return tax;
				}

				return ( tax += Math.round(
					postInclusiveTaxAmount * ( percentage / 100 )
				) );
			},
			0
		);

		return Math.round( recurringSubtotal + taxAmount );
	}

	/**
	 * Retrieves the recurring total without a discount.
	 *
	 * @since 4.4.5
	 *
	 * @return {number} Cart recurring total.
	 */
	getRecurringNoDiscountTotal() {
		const recurring = this.getLineItem( 'base' );
		const recurringSubtotal =
			recurring.getUnitPrice() * recurring.getQuantity();

		const taxRates = this.getTaxRates();
		const taxRate = this.getTaxPercent( 'inclusive' ) / 100;

		const inclusiveTaxAmount = Math.round(
			recurringSubtotal - recurringSubtotal / ( 1 + taxRate )
		);

		const postInclusiveTaxAmount = Math.round(
			recurringSubtotal - inclusiveTaxAmount
		);
		const taxAmount = taxRates.reduce(
			( tax, { percentage, calculation } ) => {
				if ( 'inclusive' === calculation ) {
					return tax;
				}

				return ( tax += Math.round(
					postInclusiveTaxAmount * ( percentage / 100 )
				) );
			},
			0
		);

		return Math.round( recurringSubtotal + taxAmount );
	}

	/**
	 * Retrieves the recurring amount for the next invoice.
	 *
	 * @since 4.4.5
	 *
	 * @return {number} Cart reucurring total due today.
	 */
	getNextInvoiceTotal() {
		const recurring = this.getLineItem( 'base' );
		const coupon = this.getCoupon();
		const {
			percent_off: couponPercentOff,
			amount_off: couponAmountOff,
			duration: couponDuration,
		} = coupon;

		let recurringSubtotal =
			recurring.getUnitPrice() * recurring.getQuantity();

		// Calculate the discount for the next invoice.
		// Cannot use getDiscount() because it discounts off the amount due today.
		if ( coupon && couponDuration !== 'once' ) {
			let discount = 0;

			if ( couponPercentOff ) {
				discount += Math.round(
					recurringSubtotal * ( couponPercentOff / 100 )
				);
			} else if ( couponAmountOff ) {
				discount += couponAmountOff;
			}

			recurringSubtotal = Math.round( recurringSubtotal - discount );
		}

		const taxRates = this.getTaxRates();
		const taxRate = this.getTaxPercent( 'inclusive' ) / 100;

		const inclusiveTaxAmount = Math.round(
			recurringSubtotal - recurringSubtotal / ( 1 + taxRate )
		);

		const postInclusiveTaxAmount = Math.round(
			recurringSubtotal - inclusiveTaxAmount
		);
		const taxAmount = taxRates.reduce(
			( tax, { percentage, calculation } ) => {
				if ( 'inclusive' === calculation ) {
					return tax;
				}

				return ( tax += Math.round(
					postInclusiveTaxAmount * ( percentage / 100 )
				) );
			},
			0
		);

		return Math.round( recurringSubtotal + taxAmount );
	}

	/**
	 * Retrieves an item.
	 *
	 * @since 3.7.0
	 *
	 * @param {string} id Cart line item ID.
	 * @return {LineItem} Cart line item.
	 */
	getLineItem( id ) {
		const items = this.getLineItems();

		// Can't use `find` because it is not supported in IE.
		const filteredItems = items.filter(
			( { id: itemId } ) => itemId === id
		);

		if ( 0 === filteredItems.length ) {
			throw {
				id: 'invalid-line-item',
				message: `Unable to retrieve line item "${ id }"`,
			};
		}

		return filteredItems[ 0 ];
	}

	/**
	 * Adds a line item to the cart.
	 *
	 * @since 3.7.0
	 *
	 * @param {Object|LineItem} item Cart line item or arguments to create one.
	 * @return {LineItem} Added line item.
	 */
	addLineItem( item ) {
		let lineitem;

		if ( true === item instanceof LineItem ) {
			lineitem = item;
		} else {
			lineitem = new this.LineItem( item, this );
		}

		this.items.push( lineitem );

		return lineitem;
	}

	/**
	 * Determines if the cart has a free trial item.
	 *
	 * @since 4.4.5
	 */
	hasFreeTrial() {
		return (
			this.getLineItems().filter( ( lineItem ) =>
				lineItem.hasFreeTrial()
			).length > 0
		);
	}
};
