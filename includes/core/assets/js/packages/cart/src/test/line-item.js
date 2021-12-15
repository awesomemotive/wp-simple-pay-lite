/**
 * Internal dependencies
 */
import { Cart, LineItem } from './../';

describe( 'LineItem', () => {
	let cart;

	beforeEach( () => {
		cart = new Cart( {
			taxPercent: 0,
			coupon: false,
			isNonDecimalCurrency: false,
		} );

		// Stub in LineItem constructor.
		// This is normally done through the payment method's cart implementation.
		// @todo Figure out how to mock this better?
		cart.LineItem = LineItem;
	} );

	describe( 'setup', () => {
		it( 'should throw with invalid cart', () => {
			expect( () => {
				new LineItem();
			} ).toThrow( new Error( 'Item must have an cart.' ) );
		} );

		it( 'should throw with invalid ID', () => {
			expect( () => {
				new LineItem(
					{
						id: 123,
					},
					cart
				);
			} ).toThrow( new Error( 'Item ID must be a string.' ) );
		} );

		it( 'should throw with invalid title', () => {
			expect( () => {
				new LineItem(
					{
						id: 'foo',
						title: 123,
					},
					cart
				);
			} ).toThrow( new Error( 'Item title must be a string.' ) );
		} );

		it( 'should throw with invalid amount', () => {
			expect( () => {
				new LineItem(
					{
						id: 'foo',
						title: 'Foo',
						amount: 'abc',
					},
					cart
				);
			} ).toThrow( new Error( 'Item amount must be an integer.' ) );
		} );

		it( 'should throw with invalid quantity', () => {
			expect( () => {
				new LineItem(
					{
						id: 'foo',
						title: 'Foo',
						amount: 1000,
						quantity: 'abc',
					},
					cart
				);
			} ).toThrow( new Error( 'Item quantity must be an integer.' ) );
		} );

		it( 'should throw with invalid subscription', () => {
			expect( () => {
				new LineItem(
					{
						id: 'foo',
						title: 'Foo',
						amount: 1000,
						quantity: 1,
						subscription: true,
					},
					cart
				);
			} ).toThrow(
				new Error(
					'Item subscription data must be a false or contain subscription data.'
				)
			);
		} );
	} );

	describe( 'update', () => {
		it( 'should keep existing properties', () => {
			const item = new LineItem(
				{
					id: 'foo',
					title: 'Foo',
					amount: 1000,
					quantity: 1,
					subscription: false,
					price: {
						id: 'price_123',
						unit_amount: 100,
						currency: 'usd',
					},
				},
				cart
			);

			item.update( {
				title: 'Bar',
				quantity: 4,
			} );

			expect( item ).toMatchObject( {
				id: 'foo',
				title: 'Bar',
				amount: 1000,
				quantity: 4,
				subscription: false,
				price: {
					id: 'price_123',
					unit_amount: 100,
					currency: 'usd',
				},
			} );
		} );
	} );

	describe( 'remove', () => {
		beforeEach( () => {
			cart.addLineItem( {
				id: 'foo',
				title: 'Foo',
				amount: 1000,
			} );

			cart.addLineItem( {
				id: 'bar',
				title: 'Bar',
				amount: 5000,
			} );
		} );

		it( 'should only remove single item', () => {
			const item = cart.getLineItem( 'bar' );
			item.remove();

			expect( cart.getLineItems().length ).toBe( 1 );
		} );
	} );
} );
