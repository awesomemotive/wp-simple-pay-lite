/* global spGeneral, jQuery  */

var spShared = {};

(function( $ ) {
	'use strict';

	var body;

	spShared = {

		init: function() {

			// Set main vars on init.
			body = $( document.body );

			// Format currency inputs after they lose focus
			body.find( '.simpay-currency-format' ).on( 'blur.simpayCurrencyFormat', function( e ) {
				spShared.formatCurrencyField( $( this ) );
			} );

			// Validate amount fields
			body.find( '.simpay-amount-input' ).on( 'blur.simpayValidateAmount', function( e ) {
				spShared.validateAmount( $( this ) );
			} );

			// Triggers
			// Trigger the currency format for page load
			body.find( '.simpay-currency-format' ).trigger( 'blur.simpayCurrencyFormat' );

			// Trigger the amount inputs for page load
			body.find( '.simpay-amount-input' ).trigger( 'blur.simpayValidateAmount' );
		},

		// Log to console if SCRIPT_DEBUG PHP constant set to true.
		debugLog: function( key, value ) {

			if ( ( 'undefined' !== typeof spGeneral ) && ( true === spGeneral.booleans.scriptDebug ) ) {
				console.log( key, value );
			}
		},

		formatCurrencyField: function( elem ) {

			elem.val( function( index, value ) {

				// Some fields we want to allow nothing to be entered but still be formatted as an amount field
				if ( elem.hasClass( 'simpay-allow-empty' ) && !elem.val() ) {
					return '';
				}

				return accounting.formatMoney( accounting.unformat( value, spGeneral.strings.decimalSeparator ), '', 2, spGeneral.strings.thousandSeparator, spGeneral.strings.decimalSeparator );
			} );
		},

		validateAmount: function( elem ) {

			var amount = spShared.unformatCurrency( elem.val() );

			// If the amount doesn't exist or is  less than 1
			if ( !amount || spGeneral.integers.minAmount > parseFloat( amount ) ) {
				elem.val( '' );

				return false;
			}

			// Set the correct decimal separator according to settings
			amount = amount.replace( '.', spGeneral.strings.decimalSeparator );

			// Check if current number is negative or not.
			if ( -1 !== amount.indexOf( '-' ) ) {
				amount = amount.replace( '-', '' );
			}

			// Update format price string in input field.
			elem.val( amount );
		},

		unformatCurrency: function( amount ) {

			amount = accounting.unformat( amount, spGeneral.strings.decimalSeparator ).toString();

			if ( !spGeneral.booleans.isZeroDecimal ) {

				// Set default value for number of decimals.
				amount = parseFloat( amount ).toFixed( 2 );
			}

			return amount;

		}
	};

	$( document ).ready( function( $ ) {

		spShared.init();
	} );

}( jQuery ) );
