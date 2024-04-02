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
};
