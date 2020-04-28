/**
 * Internal dependencies
 */
import { LineItem as BaseLineItem } from '@wpsimplepay/cart';

/**
 * LineItem
 */
class LineItem extends BaseLineItem {
	/**
	 * Retrieves a cart line item's subtotal.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item subtotal.
	 */
	getSubtotal() {
		let subtotal = 0;
		let i = 0;

		const taxDecimal = this.cart.getTaxDecimal();

		// Calculate tax for each unit and create a sum.
		for ( i = 0; i < this.getQuantity(); i++ ) {
			const unitPrice = this.getUnitPrice();
			const unitPriceTaxAmount = Math.round( unitPrice * taxDecimal );

			subtotal += unitPrice + unitPriceTaxAmount;
		}

		return subtotal;
	}

	/**
	 * Retrieves a cart line item's total.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item total.
	 */
	getTotal() {
		return Math.round( this.getSubtotal() );
	}
}

export default LineItem;
