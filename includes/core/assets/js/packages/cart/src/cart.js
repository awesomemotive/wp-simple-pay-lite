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
	 * @param {false|Object} args.coupon Stripe Coupon.
	 * @param {boolean} args.isNonDecimalCurrency If the currency is non-decimal based.
	 * @return {Cart} Cart.
	 */
	update( args ) {
		// Parse and retrieve specific arguments.
		const {
			currency,
			taxPercent,
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

		if ( 'boolean' !== typeof isNonDecimalCurrency  ) {
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
		return new Cart;
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
	 * Retrieves the cart's tax percent amount.
	 *
	 * @since 3.7.0
	 *
	 * @return {number}
	 */
	getTaxPercent() {
		return this.taxPercent;
	}

	/**
	 * Retrieves the cart's tax decimal amount for calculations.
	 *
	 * @since 3.7.0
	 *
	 * @return {number}
	 */
	getTaxDecimal() {
		return ( this.taxPercent / 100 );
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
		const filteredItems = items.filter( ( { id: itemId } ) => ( itemId === id ) );

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
};
