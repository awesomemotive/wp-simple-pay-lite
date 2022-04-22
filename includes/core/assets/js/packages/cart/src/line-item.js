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
	 * @param {Object} args.price Price option data.
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
		this.price = null;

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
	 * @param {Object} args.price Price option data.
	 * @return {LineItem} Line item.
	 */
	update( args ) {
		// Parse and retrieve specific arguments.
		const { id, title, amount, quantity, subscription, price } = {
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
		if (
			! ( false === subscription || 'object' === typeof subscription )
		) {
			throw {
				id: 'invalid-line-item-subscription',
				message:
					'Item subscription data must be a false or contain subscription data.',
			};
		} else {
			this.subscription = subscription;
		}

		// Price option data.
		if ( typeof price === 'object' && price !== null ) {
			this.price = price;
		} else {
			this.price = null;
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
		const remainingItems = allItems.filter( ( { id } ) => this.id !== id );

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
	 * Retrieves the cart line item's discount.
	 *
	 * @since 4.1.0
	 *
	 * @return {number} Cart line item discount.
	 */
	getDiscount() {
		const cartDiscount = this.cart.getDiscount();

		if ( 0 === cartDiscount ) {
			return 0;
		}

		const nonZeroLineItems = this.cart
			.getLineItems()
			.filter( ( lineItem ) => {
				return (
					! lineItem.hasFreeTrial() && 0 !== lineItem.getUnitPrice()
				);
			} );

		return Math.round( cartDiscount / nonZeroLineItems.length );
	}

	/**
	 * Retrieves a cart line item's subtotal.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item subtotal.
	 */
	getSubtotal() {
		if ( this.hasFreeTrial() ) {
			return 0;
		}

		const amount = this.getUnitPrice() * this.getQuantity();

		if ( 0 === amount ) {
			return amount;
		}

		return Math.round( amount - this.getDiscount() );
	}

	/**
	 * Retrieves a cart line item's inclusive tax amount.
	 *
	 * @since 4.1.0
	 *
	 * @return {number} Inclusive tax amount.
	 */
	getInclusiveTaxAmount() {
		const taxRate = this.cart.getTaxPercent( 'inclusive' ) / 100;
		const subtotal = this.getSubtotal();

		const inclusiveTaxAmount = Math.round(
			subtotal - subtotal / ( 1 + taxRate )
		);

		return Math.round( subtotal - ( subtotal - inclusiveTaxAmount ) );
	}

	/**
	 * Retrieves a cart line item's taxable amount.
	 *
	 * @since 4.1.0
	 *
	 * @return {number} Taxable amount.
	 */
	getTaxableAmount() {
		const subtotal = this.getSubtotal();
		const inclusiveTaxAmount = this.getInclusiveTaxAmount();

		return Math.round( subtotal - inclusiveTaxAmount );
	}

	/**
	 * Retrieves a cart line item's tax.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item tax.
	 */
	getTax() {
		const taxableAmount = this.getTaxableAmount();
		const taxPercent = this.cart.getTaxPercent( 'exclusive' );

		return Math.round( taxableAmount * ( taxPercent / 100 ) );
	}

	/**
	 * Retrieves a cart line item's total.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item total.
	 */
	getTotal() {
		return this.getSubtotal() + this.getTax();
	}

	/**
	 * Determines if the line item has a free trial.
	 *
	 * @since 4.4.5
	 *
	 * @return {bool} If the line item has a free trial.
	 */
	hasFreeTrial() {
		const { price } = this;

		if ( ! price ) {
			return false;
		}

		return price.recurring && price.recurring.trial_period_days;
	}
};

export default LineItem;
