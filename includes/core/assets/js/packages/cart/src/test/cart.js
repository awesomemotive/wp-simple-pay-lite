/**
 * Internal dependencies
 */
import { Cart, LineItem } from './../';

describe( 'Cart', () => {
	describe( 'setup', () => {
		it( 'should throw with invalid currency', () => {
			expect( () => {
				new Cart( {
					currency: 123,
				} );
			} ).toThrow( new Error( 'Currency must be a string.' ) );
		} );

		it( 'should throw with invalid tax percent', () => {
			expect( () => {
				new Cart( {
					taxPercent: 'abc',
				} );
			} ).toThrow( new Error( 'Tax percentage must be a number.' ) );
		} );

		it( 'should convert tax percents to floating point numbers', () => {
			const cart = new Cart( {
				taxPercent: '3.3744',
			} );

			expect( typeof cart.taxPercent ).toBe( 'number' );
		} );

		it( 'should throw with invalid coupon', () => {
			expect( () => {
				new Cart( {
					coupon: 9000,
				} );
			} ).toThrow(
				new Error( 'Coupon must be a false or contain coupon data.' )
			);
		} );

		it( 'should throw with invalid currency decimal setting', () => {
			expect( () => {
				new Cart( {
					isNonDecimalCurrency: 'yes',
				} );
			} ).toThrow(
				new Error(
					'Declaring a non-decimal currency must be a boolean.'
				)
			);
		} );
	} );

	describe( 'update', () => {
		it( 'should keep existing properties', () => {
			const cart = new Cart( {
				taxPercent: 0,
				coupon: false,
				isNonDecimalCurrency: false,
			} );

			cart.update( {
				coupon: {
					amount_off: 1000,
				},
			} );

			expect( cart ).toMatchObject( {
				taxPercent: 0,
				coupon: {
					amount_off: 1000,
				},
				isNonDecimalCurrency: false,
			} );
		} );
	} );

	describe( 'getLineItem', () => {
		let cart;

		beforeEach( () => {
			cart = new Cart();
			// Stub in LineItem constructor.
			// This is normally done through the payment method's cart implementation.
			// @todo Figure out how to mock this better?
			cart.LineItem = LineItem;

			cart.addLineItem( {
				id: 'foo',
				title: 'Foo',
				amount: 1000,
				quantity: 4,
			} );

			cart.addLineItem( {
				id: 'bar',
				title: 'Bar',
				amount: 1234,
				quantity: 1,
			} );
		} );

		it( 'should return all items', () => {
			expect( cart.getLineItems().length ).toBe( 2 );
		} );
	} );

	describe( 'addLineItem', () => {
		let cart;

		beforeEach( () => {
			cart = new Cart();
			// Stub in LineItem constructor.
			// This is normally done through the payment method's cart implementation.
			// @todo Figure out how to mock this better?
			cart.LineItem = LineItem;
		} );

		it( 'should be added via line item data', () => {
			const item = cart.addLineItem( {
				id: 'foo',
				title: 'Foo',
				amount: 1000,
				quantity: 4,
			} );

			expect( item instanceof LineItem ).toBe( true );

			expect( cart.getLineItems().length ).toBe( 1 );
		} );

		it( 'should be added via LineItem', () => {
			let item = new LineItem(
				{
					id: 'foo',
					title: 'Foo',
					amount: 1000,
					quantity: 4,
				},
				cart
			);

			item = cart.addLineItem( item );

			expect( item instanceof LineItem ).toBe( true );

			expect( cart.getLineItems().length ).toBe( 1 );
		} );
	} );
} );
