/* global spGeneral, jQuery, accounting */

( function ( $ ) {
	'use strict';

	let body;

	window.spShared = {
		init() {
			body = $( document.body );
		},

		/**
		 * Return amount as number value.
		 * Uses global decimal separator setting ("." or ",").
		 * accounting.unformat removes formatting/cruft first.
		 * Respects decimal separator, but ignores zero decimal currency setting.
		 * Also prevent negative values.
		 *
		 * @param amount
		 * @return number
		 */
		unformatCurrency( amount ) {
			return Math.abs(
				accounting.unformat(
					amount,
					spGeneral.strings.decimalSeparator
				)
			);
		},

		/**
		 * Return amount as formatted string.
		 * With or without currency symbol.
		 * Used for labels & amount inputs in admin & front-end.
		 * Uses global currency settings.
		 *
		 * @param amount
		 * @param _includeSymbol
		 * @param _currencySymbol
		 * @param _isZeroDecimal
		 * @return string
		 */
		formatCurrency(
			amount,
			_includeSymbol,
			_currencySymbol,
			_isZeroDecimal
		) {
			const includeSymbol = _includeSymbol || false;
			const currencySymbol =
				_currencySymbol || spGeneral.strings.currencySymbol;
			const isZeroDecimal = _isZeroDecimal || false;

			// Default format is to the left with no space.
			let format = '%s%v',
				args;

			if ( includeSymbol ) {
				// Account for other symbol placement formats (besides default left without space).
				switch ( spGeneral.strings.currencyPosition ) {
					case 'left_space':
						format = '%s %v'; // Left side with space
						break;

					case 'right':
						format = '%v%s'; // Right side without space
						break;

					case 'right_space':
						format = '%v %s'; // Right side with space
						break;
				}
			}

			args = {
				symbol: includeSymbol ? currencySymbol : '',
				decimal: spGeneral.strings.decimalSeparator,
				thousand: spGeneral.strings.thousandSeparator,
				precision: isZeroDecimal ? 0 : spGeneral.integers.decimalPlaces,
				format,
			};

			return accounting.formatMoney( amount, args );
		},

		/**
		 * Convert from cents to dollars (in USD).
		 * Uses global zero decimal currency setting.
		 * Leaves zero decimal currencies alone.
		 *
		 * @param amount
		 * @return number
		 */
		convertToDollars( amount ) {
			if ( ! spGeneral.booleans.isZeroDecimal ) {
				amount = accounting.toFixed( amount / 100, 2 );
			}

			return amount;
		},

		/**
		 * Convert from dollars to cents (in USD).
		 * Uses global zero decimal currency setting.
		 * Leaves zero decimal currencies alone.
		 *
		 * @param amount
		 * @return number
		 */
		convertToCents( amount ) {
			if ( ! spGeneral.booleans.isZeroDecimal ) {
				amount = Number( accounting.toFixed( amount * 100, 0 ) );
			}

			return amount;
		},

		/**
		 * Validate amount field client-side and update according to rules set by CSS classes.
		 * Some fields display blank instead of "0.00" or "0".
		 * Some fields require a minimum of "1.00" or "100" (100 currency units).
		 * Invalid characters and the negative symbol will be removed.
		 *
		 * @param {jQuery} Input to validate.
		 * @param el
		 */
		validateAndUpdateAmountInput( el ) {
			// Amount is intially a string.
			let amount = el.val();

			const globalMinAmount = Math.abs( spGeneral.integers.minAmount );

			// Convert amount to number value.
			amount = spShared.unformatCurrency( amount );

			// Update amount field to blank if specific class is present.
			// If zero, convert to blank and exit function.
			// Ex: Default Custom Amount, Setup Fee
			if ( el.hasClass( 'simpay-allow-blank-amount' ) ) {
				if ( 0 === amount ) {
					el.val( '' );
					return;
				}
			}

			// Validate & update fields to the global minimum amount (usually $1.00) if specific class is present.
			// Namely this is just on admin pages, separate from the custom amount minimum amount set per form.
			// Ex: One-Time Amount, Minimum Custom Amount
			if ( el.hasClass( 'simpay-minimum-amount-required' ) ) {
				if ( amount < globalMinAmount ) {
					amount = globalMinAmount;
				}
			}

			// Convert amount back to string with proper thousands & decimal separators, but without symbol.
			amount = spShared.formatCurrency( amount, false );

			// Update format price string in input field.
			// Exception: If they changed to 'number' type via filters don't reformat (default type is 'tel').
			if ( 'number' !== el[ 0 ].type ) {
				el.val( amount );
			}
		},

		/**
		 * Log debug messages to console.
		 * Alternative to console.log so doesn't show up in production environments.
		 * Instead, only if SCRIPT_DEBUG PHP constant set to true.
		 *
		 * @param key
		 * @param value
		 */
		debugLog( key, value ) {
			if (
				'undefined' !== typeof spGeneral &&
				true === spGeneral.booleans.scriptDebug
			) {
				console.log( key, value );
			}
		},
	};

	$( document ).ready( function ( $ ) {
		window.spShared.init();
	} );
} )( jQuery );
