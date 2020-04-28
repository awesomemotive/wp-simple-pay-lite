/**
 * Internal dependencies
 */
import { Cart } from './cart.js';

/**
 * LineItem
 */
export const LineItem = class LineItem {
	/**
	 * Create a line item.
	 *
	 * @since 3.7.0
	 *
	 * @param {Object} args Line item arguments.
	 * @param {string} args.id Cart line item ID.
	 * @param {string} args.title Cart line item title.
	 * @param {number} args.amount Cart line item amount.
	 * @param {number} args.quantity Cart line item quantity.
	 * @param {boolean|Object} args.subscription Cart line item subscription data.
	 * @param {boolean} args.subscription.isTrial Cart line item subscription trial designation.
	 * @param {number} args.subscription.intervalCount Cart line item subscription interval count.
	 * @param {number} args.subscription.interval Cart line item subscription interval.
	 * @param {Cart} cart Cart this line item is attached to.
	 * @return {LineItem} LineItem
	 */
	constructor( args, cart ) {
		// Set defaults.
		// @todo add support for classProperties.
		this.cart = null;
		this.id = null;
		this.title = null;
		this.amount = 0;
		this.quantity = 1;
		this.subscription = false;

		if ( 'object' !== typeof cart ) {
			throw {
				id: 'invalid-line-item-cart',
				message: 'Item must have an cart.',
			};
		} else {
			this.cart = cart;
		}

		this.update( args );
	}

	/**
	 * Updates a line item.
	 *
	 * @since 3.7.0
	 *
	 * @param {Object} args Cart line item arguments.
	 * @param {string} args.id Cart line item ID.
	 * @param {string} args.title Cart line item title.
	 * @param {number} args.amount Cart line item amount.
	 * @param {number} args.quantity Cart line item quantity.
	 * @param {boolean|Object} args.subscription Cart line item subscription data.
	 * @param {boolean} args.subscription.isTrial Cart line item subscription trial designation.
	 * @param {number} args.subscription.intervalCount Cart line item subscription interval count.
	 * @param {number} args.subscription.interval Cart line item subscription interval.
	 * @return {LineItem} Line item.
	 */
	update( args ) {
		// Parse and retrieve specific arguments.
		const {
			id,
			title,
			amount,
			quantity,
			subscription,
		} = {
			...this,
			...args,
		};

		// ID must be a string.
		if ( 'string' !== typeof id ) {
			throw {
				id: 'invalid-line-item-id',
				message: 'Item ID must be a string.',
			};
		} else {
			this.id = id;
		}

		// Title must be a string.
		// Not currently shown in any UI, but may be in the future.
		if ( 'string' !== typeof title ) {
			throw {
				id: 'invalid-line-item-title',
				message: 'Item title must be a string.',
			};
		} else {
			this.title = title;
		}

		// Amount must be a number.
		if ( ! Number.isSafeInteger( amount ) ) {
			throw {
				id: 'invalid-line-item-amount',
				message: 'Item amount must be an integer.',
			};
		} else {
			this.amount = amount;
		}

		// Quantity must be a number.
		if ( ! Number.isSafeInteger( quantity ) ) {
			throw {
				id: 'invalid-line-item-quantity',
				message: 'Item quantity must be an integer.',
			};
		} else {
			this.quantity = quantity;
		}

		// Subscription must be false or contain subscription data.
		// @todo Validate subscription data.
		if ( ! ( false === subscription || 'object' === typeof subscription ) ) {
			throw {
				id: 'invalid-line-item-subscription',
				message: 'Item subscription data must be a false or contain subscription data.',
			};
		} else {
			this.subscription = subscription;
		}

		return this;
	}

	/**
	 * Removes the line item from the cart.
	 *
	 * @since 3.7.0
	 *
	 * @return {LineItem} Removed line item.
	 */
	remove() {
		const allItems = this.cart.getLineItems();
		const remainingItems = allItems.filter( ( { id } ) => ( this.id !== id ) );

		this.cart.items = remainingItems;

		return this;
	}

	/**
	 * Retrieves a cart line item's unit price.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item unit price.
	 */
	getUnitPrice() {
		return this.amount;
	}

	/**
	 * Retrieves the cart line item's quantity.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item quantity.
	 */
	getQuantity() {
		return this.quantity;
	}
	
	/**
	 * Retrieves a cart line item's tax.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item tax.
	 */
	getTax() {
		let tax = 0;
		let i = 0;

		const taxDecimal = this.cart.getTaxDecimal();
		const quantity = this.getQuantity();
		const subtotal = this.getSubtotal();

		for ( i = 0; i < quantity; i++ ) {
			tax += Math.round( subtotal * taxDecimal );
		}

		return tax;
	}
};

export default LineItem;
