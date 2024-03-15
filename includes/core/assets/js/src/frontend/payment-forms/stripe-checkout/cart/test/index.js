/**
 * Internal dependencies
 */
import { Cart } from './../';

describe( 'StripeCheckout', () => {
	let cart;

	beforeEach( () => {
		cart = new Cart( {
			taxPercent: 5.575,
		} );

		cart.addLineItem( {
			id: 'foo',
			title: 'Foo',
			amount: 1200,
			quantity: 4,
		} );

		cart.addLineItem( {
			id: 'bar',
			title: 'Bar',
			amount: 7600,
			quantity: 1,
		} );
	} );

	/**
	 * LineItem
	 */
	describe( 'LineItem', () => {
		describe( 'getTax', () => {
			it( 'should return tax amount', () => {
				const item = cart.getLineItem( 'foo' );

				expect( item.getTax() ).toEqual( 1132 );
			} );
		} );

		describe( 'getTotal', () => {
			it( 'should equal the subtotal', () => {
				expect( cart.getTotal() ).toEqual( cart.getSubtotal() );
			} );
		} );
	} );

	/**
	 * Cart
	 */
	describe( 'Cart', () => {
		// Taxes.
		describe( 'getTax', () => {
			it( 'should add tax amount for each line item quantity', () => {
				expect( cart.getTax() ).toEqual( 692 );
			} );
		} );

		// Discounts.
		describe( 'getDiscount', () => {
			it( 'should return 0 with no coupon applied', () => {
				cart.update( {
					coupon: false,
				} );

				expect( cart.getDiscount() ).toEqual( 0 );
			} );

			// Flat discounts.
			describe( 'flat amount', () => {
				it( 'should return amount off', () => {
					cart.update( {
						coupon: {
							amount_off: 1200,
						},
					} );

					expect( cart.getDiscount() ).toEqual( 1200 );
				} );
			} );

			// Percent discounts.
			describe( 'percentage', () => {
				it( 'should return amount off', () => {
					cart.update( {
						coupon: {
							percent_off: 12,
						},
					} );

					expect( cart.getDiscount() ).toEqual( 1571 );
				} );
			} );
		} );

		// Total
		describe( 'getTotal', () => {
			it( 'should remove discount amount from subtotal', () => {
				cart.update( {
					coupon: {
						percent_off: 12,
					},
				} );

				expect( cart.getTotal() ).toEqual( 11521 );
			} );
		} );
	} );
} );
