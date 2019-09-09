/* global spGeneral, jQuery */

/**
 * Internal dependencies.
 */
import 'pro/admin/settings/license.js';
import toggleWebhookEndpointSecret from 'pro/admin/settings/webhooks.js';

var spAdminPro = {};

( function( $ ) {
	'use strict';

	var body,
		spFormSettings;

	spAdminPro = {

		init: function() {

			// Wait to do this here due to weird loading order of scripts.
			// @todo Redo script dependency management.
			wpspHooks.addAction( 'settings.toggleTestMode', 'wpsp/settings/webhooks', toggleWebhookEndpointSecret );

			// We need to set these in here because this is when the page is ready to grab this info.
			body = $( document.body );
			spFormSettings = body.find( '#simpay-form-settings' );

			this.loadCustomFieldMetaboxes();

			// Setup datepicker fields
			this.addDatepicker();

			// Disable subscriptions radio buttons when Recurring Amount Toggle found
			body.on( 'DOMNodeInserted.simpayDOMNodeInserted', '.simpay-custom-fields', this.disableSubscriptions );

			// Enable subscriptions radio buttons when Recurring Amount Toggle removed
			body.on( 'DOMNodeRemoved.simpayDOMNodeRemoved', '.simpay-custom-fields', this.enableSubscriptions );

			// Make custom fields sortable
			this.initSortableFields( spFormSettings.find( '.simpay-custom-fields' ) );

			// Add custom fields fields
			spFormSettings.find( '.add-field' ).on( 'click.simpayAddField', this.addField );

			// Remove custom fields
			spFormSettings.find( '.simpay-custom-fields' ).on( 'click.simpayRemoveField', '.simpay-remove-field-link', spAdminPro.removeField );

			// Meta-Boxes - Open/close
			spFormSettings.find( '.simpay-metaboxes-wrapper' ).on( 'click.simpayToggleMetabox', '.simpay-metabox h3', function( e ) {
				spAdminPro.initMetaboxToggle( $( this ), e );
			} );

			// Disable Amount based on certain selections (like subscriptions or a custom field used for amount)
			spFormSettings.on( 'change.simpayToggleAmountOptions', '.simpay-disable-amount input[type="radio"], .simpay-dropdown-amount, .simpay-radio-amount', function() {
				spAdminPro.toggleAmountOptions( $( this ) );
			} );

			// Handle tax percent length
			$( '.simpay-tax-percent-field' ).on( 'blur.simpayTaxPercentBlur', function() {
				spAdminPro.handleTaxPercent( $( this ) );
			} );

			// Trigger disabled subscriptions if needed on page load
			if ( spFormSettings.find( '.simpay-custom-field-recurring-amount-toggle' ).length > 0 ) {
				spFormSettings.find( '#_subscription_type input[type="radio"]' ).prop( 'disabled', true );
			}
		},

		handleTaxPercent: function( el ) {
			el.val( accounting.toFixed( el.val(), 4 ) );
		},

		disableSubscriptions: function( e ) {

			var el = e.target,
				subscriptionRadios = spFormSettings.find( '#_subscription_type input[type="radio"]' );

			if ( $( el ).hasClass( 'simpay-custom-field-recurring-amount-toggle' ) ) {

				spFormSettings.find( '#_subscription_type-disabled' ).click();
				subscriptionRadios.prop( 'disabled', true );
			}
		},

		enableSubscriptions: function( e ) {

			var el = e.target,
				subscriptionRadios = spFormSettings.find( '#_subscription_type input[type="radio"]' );

			if ( $( el ).hasClass( 'simpay-custom-field-recurring-amount-toggle' ) ) {

				subscriptionRadios.prop( 'disabled', false );
			}
		},

		initSortableFields: function( el ) {

			// Field ordering
			el.sortable( {
				items: '.simpay-field-metabox',
				cursor: 'move',
				axis: 'y',
				handle: 'h3',
				scrollSensitivity: 40,
				forcePlaceholderSize: true,
				opacity: 0.65,
				placeholder: 'simpay-metabox-sortable-placeholder',
				start: function( e, ui ) {
					ui.item.css( 'background-color', '#f6f6f6' );
				},
				stop: function( e, ui ) {
					ui.item.removeAttr( 'style' );
					spAdminPro.orderFields();
				},

				// @link https://core.trac.wordpress.org/changeset/35809
				helper: function( event, element ) {
					/* `helper: 'clone'` is equivalent to `return element.clone();`
					 * Cloning a checked radio and then inserting that clone next to the original
					 * radio unchecks the original radio (since only one of the two can be checked).
					 * We get around this by renaming the helper's inputs' name attributes so that,
					 * when the helper is inserted into the DOM for the sortable, no radios are
					 * duplicated, and no original radio gets unchecked.
					 */
					return element.clone()
						.find( ':input' )
						.attr( 'name', function( i, currentName ) {
							return 'sort_' + parseInt( Math.random() * 100000, 10 ).toString() + '_' + currentName;
						} )
						.end();
				}
			} );
		},

		addField: function( e ) {
			var size = spFormSettings.find( '.simpay-custom-fields .simpay-field-metabox' ).length,
				totalFields = size,
				wrapper = $( this ).closest( '#simpay-custom-fields-wrap' ),
				boxes = wrapper.find( '.simpay-metaboxes' ),
				nonMaxFields = [ 'total_amount', 'payment_button', 'custom_amount', 'plan_select' ],
				currentKey = parseInt( spFormSettings.find( '#custom-field-select option:selected' ).data( 'counter' ) ) + 1,
				selectField = spFormSettings.find( '#custom-field-select' ),
				fieldType = selectField.val(),
				currentId,
				data;

			boxes.find( '.simpay-field-metabox' ).each( function() {
				if ( $( this ).hasClass( 'simpay-custom-field-payment-button' ) ||
				     $( this ).hasClass( 'simpay-custom-field-custom-amount' ) ||
				     $( this ).hasClass( 'simpay-custom-field-plan-select' ) ||
				     $( this ).hasClass( 'simpay-custom-field-total-amount' ) ) {
					totalFields--;
				}
			} );

			data = {
				action: 'simpay_add_field',
				fieldType: fieldType,
				counter: currentKey,
				addFieldNonce: spFormSettings.find( '#simpay_custom_fields_nonce' ).val()
			};

			e.preventDefault();

			// Prevent addition if the field is not repeatable.
			if ( ! selectField.find( ':selected' ).data( 'repeatable' ) ) {
				var className = '.simpay-custom-field-' + fieldType.replace( '_', '-' );

				if ( spFormSettings.find( className ).length > 0 ) {
					alert( spGeneral.i18n.limitSingleField );

					return;
				}
			}

			if ( totalFields >= 20 && -1 === $.inArray( fieldType, nonMaxFields ) ) {
				alert( spGeneral.i18n.limitMaxFields );
				return;
			}

			spFormSettings.find( '.simpay-field-data' ).each( function() {
				if ( $( this ).is( ':visible' ) ) {
					$( this ).hide();
					$( this ).addClass( 'closed' );
				}
			} );

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: data,
				success: function( response ) {

					var temp = $( '<div/>' ).append( response );

					// Update field order value for this element
					temp.find( '.field-order' ).val( size );

					temp.find( 'input' ).each( function() {

						currentId = $( this ).attr( 'id' );

						// Rename HTML element ID's to use the current counter
						if ( 'undefined' !== typeof currentId ) {
							$( this ).attr( 'id', currentId.replace( /(\d+)/, parseInt( currentKey ) ) );
						}
					} );

					spAdminPro.orderFields();

					boxes.append( temp.html() );

					// If it is a date field we need to rerun the datepicker to make sure it is added to the dynamic element
					if ( 'date' === fieldType ) {
						spAdminPro.addDatepicker();
					}

					// Disable option to prevent duplicates.
					var chosenOption = selectField.find( 'option[value="' + fieldType + '"]' );

					if ( false === chosenOption.data( 'repeatable' ) ) {
						chosenOption.prop( 'disabled', true );
					}

					// Reset <select>.
					selectField.prop( 'selectedIndex', 0 );
				},
				error: function( response ) {
					spShared.debugLog( response );
				}
			} );

			// Update currentKey
			spFormSettings.find( '.simpay-field-select option' ).data( 'counter', currentKey );
		},

		removeField: function( e ) {
			e.preventDefault();

			var selectField = spFormSettings.find( '#custom-field-select' );

			if ( window.confirm( 'Are you sure you want to remove this field?' ) ) {
				var metabox = $( this ).closest( '.simpay-field-metabox' );
				var fieldType = metabox.data( 'type' );

				metabox.remove();
				selectField.find( 'option[value="' + fieldType + '"]' ).prop( 'disabled', false );
				spAdminPro.toggleAddFieldOptions( false );
			}
		},

		orderFields: function() {

			$( '.simpay-custom-fields .simpay-field-metabox' ).each( function( index, el ) {

				var fieldIndex = parseInt( $( el ).index( '.simpay-custom-fields .simpay-field-metabox' ) );

				$( '.field-order', el ).val( fieldIndex );

			} );
		},

		initMetaboxToggle: function( el, e ) {

			var parentElem = el.parent();

			e.preventDefault();

			// If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled
			if ( $( e.target ).filter( ':input, option, .sort' ).length ) {
				return;
			}

			if ( parentElem.hasClass( 'closed' ) ) {
				parentElem.removeClass( 'closed' );
			} else {
				parentElem.addClass( 'closed' );
			}

			el.next( '.simpay-metabox-content' ).stop().slideToggle();
		},

		loadCustomFieldMetaboxes: function() {

			var simpayCustomFields = body.find( '.simpay-custom-fields' ),
				simpayCustomFieldsMetaBox = simpayCustomFields.find( '.simpay-field-metabox' ).get();

			// First we need to sort all the custom fields by their "rel" attribute
			simpayCustomFieldsMetaBox.sort( function( a, b ) {
				var compA = parseInt( $( a ).attr( 'rel' ), 10 );
				var compB = parseInt( $( b ).attr( 'rel' ), 10 );
				return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;
			} );

			// After being sorted we append each one to the main content area where they are viewed
			$( simpayCustomFieldsMetaBox ).each( function( idx, itm ) {
				simpayCustomFields.append( itm );
			} );

			// Load all the custom field metaboxes closed on page load
			$( '.simpay-metabox.closed' ).each( function() {
				$( this ).find( '.simpay-metabox-content' ).hide();
			} );

		},

		addDatepicker: function() {

			var dateInput = $( '.simpay-date-input' );

			dateInput.datepicker();
			dateInput.datepicker( 'option', 'dateFormat', spGeneral.strings.dateFormat );
		},

		// TODO DRY

		toggleAmountOptions: function( el ) {

			var check = '',
				check2 = '',
				checkValue = el.val(),
				amountField = body.find( '.simpay-panel-field #_amount_type' ),
				amountFieldRadios = amountField.find( 'input[type="radio"]' ),
				amountOption = amountField.find( 'input[type="radio"]:checked' );

			if ( el.is( ':radio' ) ) {

				// Check is for multiple options that change it to disabled (where only one option enables)
				// Check2 is for the opposite. Where multiple options will keep it enabled but only one option will disable it.
				check = el.data( 'disable-amount-check' );
				check2 = el.data( 'disable-amount-single' );

				if ( ( ( undefined !== check ) && ( checkValue !== check ) ) || ( ( undefined !== check2 ) && ( checkValue === check2 ) ) ) {

					spAdminPro.setDisabledRadios( amountFieldRadios, true );
					body.find( '.toggle-_amount_type-one_time_set, .toggle-_amount_type-one_time_custom' ).hide();

				} else {

					spAdminPro.setDisabledRadios( amountFieldRadios, false );
					body.find( '.toggle-_amount_type-' + amountOption.val() ).show();
				}
			} else if ( el.is( ':checkbox' ) ) {

				if ( el.is( ':checked' ) ) {

					spAdminPro.setDisabledRadios( amountFieldRadios, true );
					body.find( '.toggle-_amount_type-one_time_set, .toggle-_amount_type-one_time_custom' ).hide();
				} else {

					spAdminPro.setDisabledRadios( amountFieldRadios, false );
					body.find( '.toggle-_amount_type-' + amountOption.val() ).show();
				}
			}
		},

		setDisabledRadios: function( el, disable ) {

			el.each( function() {
				if ( disable ) {
					$( this ).prop( 'disabled', true );
				} else {
					$( this ).prop( 'disabled', false );
				}
			} );
		}
	};

	$( document ).ready( function( $ ) {
		spAdminPro.init();
	} );

}( jQuery ) );
