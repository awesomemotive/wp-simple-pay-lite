/**
 * Internal dependencies
 */
import { Cart as BaseCart } from '@wpsimplepay/cart';
import LineItem from './line-item.js';

/**
 * Cart for Stripe Checkout form types.
 *
 * @since 3.7.0
 */
export const Cart = class Cart extends BaseCart {
	/**
	 * @since 3.7.0
	 *
	 * @param {Object} args Cart arguments.
	 */
	constructor( args ) {
		super( args );

		// Define the type of line item to use.
		this.LineItem = LineItem;
	}

	/**
	 * Retrieves subtotal.
	 *
	 * Taxes are added to each line item amount to create a subtotal.
	 * This is done because Stripe Checkout Sessions do not support
	 * `tax_percent` or `tax_rates`.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart subtotal.
	 */
	getSubtotal() {
		let subtotal = 0;

		this.getLineItems().forEach( ( item ) => {
			subtotal += item.getSubtotal();
		} );

		return subtotal;
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

		const {
			percent_off: percentOff,
			amount_off: amountOff,
		} = coupon;

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
		let tax = 0;
		let i = 0;

		const taxDecimal = this.getTaxDecimal();
		const lineItems = this.getLineItems();

		lineItems.forEach( ( item ) => {
			for ( i = 0; i < item.getQuantity(); i++ ) {
				tax += Math.round( item.getUnitPrice() * taxDecimal );
			}
		} );

		return tax;
	}

	/**
	 * Retrieves the total.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart total.
	 */
	getTotal() {
		const subtotal = this.getSubtotal();
		const discount = this.getDiscount();

		return Math.round( subtotal - discount );
	}
};
