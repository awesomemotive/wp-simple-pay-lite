/* global jQuery */

var spSubAdmin = {};

( function( $ ) {
	'use strict';

	var body,
		spSubSettings;

	spSubAdmin = {

		init: function() {

			// We need to initialize these here because that's when the document is finally ready
			body = $( document.body );
			spSubSettings = body.find( '#subscription-options-settings-panel' );

			this.loadMultiPlanSubscriptions();

			// Initialize sortable fields for multi-plans
			this.initSortablePlans( spSubSettings.find( '.simpay-multi-subscriptions tbody' ) );

			// Add plan button
			spSubSettings.find( '.simpay-add-plan' ).on( 'click.simpayAddPlan', function( e ) {

				e.preventDefault();

				spSubAdmin.addPlan( e );
			} );

			// Remove Plan action
			spSubSettings.find( '.simpay-panel-field' ).on( 'click.simpayRemovePlan', '.simpay-remove-plan', function( e ) {
				spSubAdmin.removePlan( $( this ), e );
			} );

			// Search for and set the default plan on load.
			spSubAdmin.setDefaultPlan();

			// Update default subscription
			spSubSettings.find( '.simpay-multi-subscriptions' ).on( 'click.simpayUpdateDefaultPlan', '.simpay-multi-plan-default input[type="radio"]', function( e ) {
				spSubAdmin.updateDefaultPlan( $( this ) );
			} );

			// Trigger update of plan ID on change of select
			spSubSettings.find( '.simpay-multi-subscriptions' ).on( 'change.simpayUpdatePlanSelect', '.simpay-multi-plan-select', function( e ) {
				spSubAdmin.updatePlanSelect( $( this ) );
			} );

			// Enable/Disable single subscription plan dropdown
			spSubSettings.find( '#_subscription_custom_amount' ).find( 'input[type="radio"]' ).on( 'change.simpayToggleSubscription', function( e ) {
				spSubAdmin.togglePlans( $( this ) );
			} );

			// Trigger for default plan value if none are selected
			if ( '' === spSubSettings.find( '#simpay-multi-plan-default-value' ).val() ) {
				spSubSettings.find( '.simpay-multi-plan-default input[type="radio"]:first' ).trigger( 'click.simpayUpdateDefaultPlan' );
			}
		},

		initSortablePlans: function( el ) {

			el.sortable( {
				items: 'tr',
				cursor: 'move',
				axis: 'y',
				handle: 'td.sort-handle',
				scrollSensitivity: 40,
				forcePlaceholderSize: true,
				opacity: 0.65,
				stop: function( e, ui ) {
					spSubAdmin.orderPlans();
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

		loadMultiPlanSubscriptions: function() {

			var simpayPlans = spSubSettings.find( '.simpay-multi-sub' ).get();

			simpayPlans.sort( function( a, b ) {
				var compA = parseInt( $( a ).attr( 'rel' ), 10 );
				var compB = parseInt( $( b ).attr( 'rel' ), 10 );
				return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;
			} );

			spSubSettings.find( simpayPlans ).each( function( idx, itm ) {
				spSubSettings.find( '.simpay-multi-subscriptions tbody' ).append( itm );
			} );
		},

		togglePlans: function( el ) {

			// TODO DRY

			if ( 'enabled' === el.val() && el.is( ':checked' ) ) {
				body.find( '#_single_plan' ).prop( 'disabled', true ).trigger( 'chosen:updated' );
			} else {
				body.find( '#_single_plan' ).prop( 'disabled', false ).trigger( 'chosen:updated' );
			}
		},

		updatePlanSelect: function( el ) {

			var fieldKey = el.parent().data( 'field-key' );

			if ( spSubSettings.find( '#simpay-subscription-multi-plan-default-' + fieldKey + '-yes' ).is( ':checked' ) ) {
				spSubSettings.find( '#simpay-multi-plan-default-value' ).val( el.find( 'option:selected' ).val() );
			}
		},

		/**
		 * Validate (and potentially fix) the default subscription plan.
		 */
		setDefaultPlan: function() {
			var savedDefaultField = document.getElementById( 'simpay-multi-plan-default-value' );
			var selectedDefaultField = document.querySelector( '.simpay-multi-plan-default input:checked' );

			// Selected radio matches saved meta, do nothing.
			if ( selectedDefaultField && ( selectedDefaultField.dataset.planId === savedDefaultField.value ) ) {
				return;
			}

			// There is a selected default but it doesn't match, so update the hidden field.
			if ( selectedDefaultField && ( selectedDefaultField.dataset.planId !== savedDefaultField.value ) ) {
				savedDefaultField.value = selectedDefaultField.dataset.planId;
			}

			// There is no selected default, updated the saved meta with the first option.
			if ( ! selectedDefaultField ) {
				var firstRadio = document.querySelector( '.simpay-multi-plan-default input' );

				if ( ! firstRadio ) {
					return;
				}

				// Check...
				firstRadio.checked = true;

				// Set hidden value.
				savedDefaultField.value = firstRadio.dataset.planId;
			}
		},

		updateDefaultPlan: function( el ) {

			var plan = el.closest( '.simpay-multi-plan-default' ).parent().find( '.simpay-multi-plan-select' ).find( 'option:selected' ).val();

			spSubSettings.find( '#simpay-multi-plan-default-value' ).val( plan );
		},

		orderPlans: function() {

			spSubSettings.find( '.simpay-multi-sub' ).each( function( index, el ) {

				var planIndex = parseInt( $( el ).index( '.simpay-multi-sub' ) );

				spSubSettings.find( '.plan-order', el ).val( planIndex );
			} );
		},

		addPlan: function( e ) {

			var wrapper = spSubSettings.find( '.simpay-multi-subscriptions tbody' ); // Main table
			var currentKey = parseInt( spSubSettings.find( '.simpay-multi-sub:last' ).data( 'field-key' ) ) + 1; // Counter from tr

			var data = {
				action: 'simpay_add_plan',
				counter: currentKey,
				addPlanNonce: body.find( '#simpay_add_plan_nonce' ).val()
			};

			e.preventDefault();

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: data,
				success: function( response ) {

					wrapper.append( response );
					wrapper.find( 'select' ).chosen();

				},
				error: function( response ) {
					spShared.debugLog( response );
				}
			} );
		},

		removePlan: function( el, e ) {
			e.preventDefault();

			el.closest( '.simpay-multi-sub' ).remove();
			spSubAdmin.setDefaultPlan();
		}
	};

	$( document ).ready( function( $ ) {
		spSubAdmin.init();
	} );

}( jQuery ) );
