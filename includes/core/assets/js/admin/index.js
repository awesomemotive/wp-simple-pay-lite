/* global spGeneral, jQuery, wpspHooks */

/**
 * WordPress dependencies
 */
import { createHooks } from '@wordpress/hooks';

/**
 * Internal dependencies.
 */
import 'admin/settings/test-mode.js';
import toggleStripeConnectNotice from 'admin/settings/stripe-connect.js';

// Since we rely on WordPress 4.9 and package our own copy of @wordpress/hooks
// it is up to us to create a hooks instance to use in our app.
window.wpspHooks = createHooks();

let spAdmin = {};

( function( $ ) {
	'use strict';

	let body,
		spFormSettings;

	spAdmin = {

		init() {
			// Set main vars on init.
			body = $( document.body );
			spFormSettings = body.find( '#simpay-form-settings' );

			// Init admin metabox tab clicks.
			this.handleMetaboxTabClick();

			//
			// Handle toggles.
			//

			// Sortable metabox implementation.
			// Must attach to wrapper to handle live DOM additions.
			if ( window.postboxes && window.postboxes.handle_click ) {
				spFormSettings.on( 'click', '.postbox .simpay-hndle, .postbox .simpay-handlediv', window.postboxes.handle_click );
			}

			// Radios settings.
			[
				'_amount_type',
				'_success_redirect_type',
				'_subscription_type',
				'_subscription_custom_amount',
				'_form_display_type',
				'.simpay-total-amount-label-recurring',
				'.simpay-total-amount-label-tax',
				'.simpay-shipping-address',
				'.simpay-text-multiline',
				'.simpay-dropdown-type input',
				'.simpay-radio-type input',
			].forEach( ( input ) => {
				// Allow classes to be passed, but prefer an input name.
				let inputEl = $( input );
				let isCustomFieldToggle = false;

				if ( '.' !== input.substring( 0, 1 ) ) {
					inputEl = `input[name="${ input }"]`;
				} else {
					inputEl = input;
					isCustomFieldToggle = true;
				}

				// Find each setting.
				$( '#simpay-form-settings' )
					/**
					 * Toggles content based on an input's current value.
					 *
					 * @since 3.8.0
					 */
					.on( 'change', inputEl, function() {
						let parent = $( '#simpay-form-settings' );

						if ( true === isCustomFieldToggle ) {
							parent = $( this ).closest( '.simpay-field-data' );
						}

						// Get the toggled setting value.
						let current = $( this ).val();

						// Alter the current "value" if using a checkbox, which always has
						// a property value of `yes`.
						if ( $( this ).is( ':checkbox' ) ) {
							current = $( this ).is( ':checked' ) ? 'yes' : 'no';
						}

						// Find all linked content via `[data-toggle=""]` where the matching
						// values will be shown and others hidden.
						parent.find( $( `.simpay-show-if[data-if="${ input }"]` ) ).each( function() {
							const content = $( this );
							const showIf = content.data( 'is' );

							// All all initially.
							content.hide();

							// Show items where `[data-if=""]` contains the toggle input value.
							if ( showIf.includes( current ) ) {
								content.show();
							}
						} );
					} );

				// Trigger initial state.
				$( inputEl )
					.filter( ':checked' )
					.trigger( 'change' );
			} );

			// Wait to do this here due to weird loading order of scripts.
			// @todo Redo script dependency management.
			wpspHooks.addAction( 'settings.toggleTestMode', 'wpsp/settings/stripe-connect', toggleStripeConnectNotice );

			// Init internal link to tab clicks.
			spFormSettings.on( 'click.simpayTabLink', '.simpay-tab-link', function( e ) {
				e.preventDefault();
				spAdmin.handleInternalLinkToTabClicks( $( this ) );
			} );

			// Remove image preview click.
			spFormSettings.on( 'click.simpayImagePreview', '.simpay-remove-image-preview', function( e ) {
				spAdmin.handleRemoveImagePreviewClick( e );
			} );

			// Trigger focus out (blur) for all amount input fields on page load.
			// Should only need for admin. Used to be in shared.js.
			body.find( '.simpay-amount-input' ).trigger( 'blur.validateAndUpdateAmount' );

			// Use chosen for select fields
			this.setupChosen();

			// Media Uploader
			this.addMediaFields();

			this.stripeConnect();

			body.trigger( 'simpayAdminInit' );
		},

		handleRemoveImagePreviewClick( e ) {
			e.preventDefault();

			spFormSettings.find( '.simpay-image-preview-wrap' ).hide();

			spFormSettings.find( '#_image_url' ).val( '' );
		},

		addMediaFields() {
			let simpayMediaUploader;

			$( '.simpay-media-uploader' ).on( 'click', function( e ) {
				e.preventDefault();

				// This is our button
				window.simpayMediaUploaderInputField = $( this );

				// If the uploader object has already been created, reopen the dialog
				if ( simpayMediaUploader ) {
					simpayMediaUploader.open();
					return;
				}

				// Extend the wp.media object
				simpayMediaUploader = wp.media.frames.file_frame = wp.media( {
					title: spGeneral.i18n.mediaTitle,
					button: {
						text: spGeneral.i18n.mediaButtonText,
					}, multiple: false,
				} );

				// When a file is selected, grab the URL and set it as the text field's value
				simpayMediaUploader.on( 'select', function() {
					const attachment = simpayMediaUploader.state().get( 'selection' ).first().toJSON(),
						inputField = $( '#_image_url' ), // Get the field previous to our button, aka our input field.
						image = ( 'id' === inputField.data( 'fvalue' ) ? attachment.id : attachment.url );

					// Update our image preview
					$( '.simpay-image-preview-wrap' ).show();
					$( '.simpay-image-preview' ).prop( 'src', image );

					inputField.val( image );
				} );

				// Open the uploader dialog
				simpayMediaUploader.open();
			} );
		},

		setupChosen() {
			const chosenSelect = $( '.simpay-chosen-select, .simpay-chosen-search' );

			chosenSelect.chosen( { disable_search_threshold: 20 } );
			chosenSelect.chosen();
		},

		// Tabbed Panels in Settings Meta Box.
		// All nav list items are inactive (no "active" class) except first by default.
		// All tab panel content containers are hidden ("simpay-panel-hidden" class) except first by default.
		// Can make specific panel active on initial page load via url hash.

		handleMetaboxTabClick() {
			const tabLinks = $( 'ul.simpay-tabs a' );
			const panels = $( 'div.simpay-panel' );
			const allTabLinkParents = tabLinks.parents( 'li' );

			// When a tab link is clicked.
			tabLinks.on( 'click', function( e ) {
				e.preventDefault();

				const currentTabLinkParent = $( this ).parent();

				// Assign current tab element to var from link href attribute.
				const currentTabEl = $( $( this ).attr( 'href' ) );

				// Set the hash in the URL so after saving we get the same tab
				const hash = $( this ).attr( 'href' );
				history.pushState( null, null, hash );

				$( '[name="simpay_form_settings_tab"]' ).val( hash );

				$( '.simpay-panels > .spinner' ).hide();

				// Remove active class from all tabs.
				allTabLinkParents.removeClass( 'active' );

				// Add active class back to current tab.
				currentTabLinkParent.addClass( 'active' );

				// Hide content with all tab panels.
				panels.addClass( 'simpay-panel-hidden' );

				// Show current tab's content.
				currentTabEl.removeClass( 'simpay-panel-hidden' );
			} );

			let activeTab = '#payment-options-settings-panel';

			// Auto open tab if in url hash.
			if ( location.hash.length ) {
				activeTab = location.hash;
			}

			const activeTabLink = $( 'ul.simpay-tabs a[href="' + activeTab + '"]' );

			$( '[name="simpay_form_settings_tab"]' ).val( activeTab );

			if ( activeTabLink.length ) {
				activeTabLink.click();
			}
		},

		stripeConnect() {
			$( '#simpay-settings-keys-mode-test-mode' ).closest( '.form-table' ).prev().hide().prev().hide();

			$( '#wpsp-api-keys-row-reveal button' ).click( function( e ) {
				e.preventDefault();

				$( '#simpay-settings-keys-mode-test-mode' ).closest( '.form-table' ).prev().show().prev().show();
				$( '#wpsp-api-keys-row-hide' ).show();
				$( this ).parent().hide();
			} );

			$( '#wpsp-api-keys-row-hide button' ).click( function( e ) {
				e.preventDefault();

				$( '#simpay-settings-keys-mode-test-mode' ).closest( '.form-table' ).prev().hide().prev().hide();
				$( '#wpsp-api-keys-row-reveal' ).show();
				$( this ).parent().hide();
			} );
		},

		// Handle links within tab content to other tabs.
		// When one is clicked, trigger the corresponding tab link click.
		handleInternalLinkToTabClicks( el ) {
			const tabToShowId = el.data( 'show-tab' ),
				tabToShowLinkEl = body.find( '.' + tabToShowId + '-tab a' );

			tabToShowLinkEl.click();
		},
	};

	$( document ).ready( function( $ ) {
		spAdmin.init();
	} );
}( jQuery ) );
