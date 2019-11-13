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

var spAdmin = {};

( function( $ ) {
	'use strict';

	var body,
		spFormSettings;

	spAdmin = {

		init: function() {

			// Wait to do this here due to weird loading order of scripts.
			// @todo Redo script dependency management.
			wpspHooks.addAction( 'settings.toggleTestMode', 'wpsp/settings/stripe-connect', toggleStripeConnectNotice );

			// Set main vars on init.
			body = $( document.body );
			spFormSettings = body.find( '#simpay-form-settings' );

			// Use chosen for select fields
			this.setupChosen();

			// Media Uploader
			this.addMediaFields();

			// Init admin metabox tab clicks.
			this.handleMetaboxTabClick();

			this.stripeConnect();

			// Init internal link to tab clicks.
			spFormSettings.on( 'click.simpayTabLink', '.simpay-tab-link', function( e ) {
				e.preventDefault();
				spAdmin.handleInternalLinkToTabClicks( $( this ) );
			} );

			// Remove image preview click.
			spFormSettings.on( 'click.simpayImagePreview', '.simpay-remove-image-preview', function( e ) {
				spAdmin.handleRemoveImagePreviewClick( e );
			} );

			// Checkbox section toggles
			spFormSettings.find( '.simpay-panel-field' ).on( 'change.simpaySectionToggle', '.simpay-section-toggle', function( e ) {
				spAdmin.initSectionToggle( $( this ) );
			} );

			// Show spinner for button clicks
			body.find( '.simpay-button' ).on( 'click.simpayShowSpinner', function( e ) {
				spAdmin.showSpinner( $( this ) );
			} );

			// Handle the preview button functionality
			body.find( '#simpay-preview-button' ).on( 'click.simpayPreviewButton', function( e ) {
				spAdmin.handlePreviewButton( $( this ), e );
			} );

			// Handle the submit when they press enter
			body.find( '#post' ).on( 'keypress.simpaySubmitOnEnter', function( e ) {
				spAdmin.handleSubmitOnEnter( $( this ), e );
			} );

			// Multi Toggles (like a radio button with multiple-options)
			spFormSettings.find( '.simpay-multi-toggle input[type="radio"]:checked' ).each( function() {
				spAdmin.initMultiToggle( $( this ) );
			} );

			spFormSettings.on( 'change.simpayMultiToggle', '.simpay-multi-toggle input[type="radio"]', function() {
				spAdmin.initMultiToggle( $( this ) );
			} );

			// Trigger focus out (blur) for all amount input fields on page load.
			// Should only need for admin. Used to be in shared.js.
			body.find( '.simpay-amount-input' ).trigger( 'blur.validateAndUpdateAmount' );

			body.trigger( 'simpayAdminInit' );
		},

		// TODO This working or needed now?

		handleSubmitOnEnter: function( el, e ) {

			var keyCode,
				form,
				draftButton,
				publishButton;

			// Get the keycode
			keyCode = ( e.keyCode ? e.keyCode : e.which );

			// Check if the enter button was pressed
			if ( 13 === keyCode ) {

				form = el.closest( 'form' );

				draftButton = form.find( '#save-post' );
				publishButton = form.find( '#publish' );

				// If there is a draft button found click it otherwise use the publish button.
				if ( draftButton.length > 0 ) {
					draftButton.click();
				} else {
					publishButton.click();
				}
			}
		},

		handlePreviewButton: function( el, e ) {

			var prevFormAction,
				formElem;

			e.preventDefault();

			// Get the form this button belongs to
			formElem = el.closest( 'form' );

			// Get the form action we need to fall back to
			prevFormAction = formElem.attr( 'action' );

			// Temporarily change the action of our form to point to the preview page
			formElem.attr( 'action', el.data( 'action' ) );
			formElem.attr( 'target', '_blank' );

			formElem.submit();

			// Revert form action to original and set the target back
			formElem.attr( 'action', prevFormAction );
			formElem.attr( 'target', '' );
		},

		showSpinner: function( el ) {
			el.parent().find( '.spinner' ).css( 'visibility', 'visible' );
		},

		handleRemoveImagePreviewClick: function( e ) {

			e.preventDefault();

			spFormSettings.find( '.simpay-image-preview-wrap' ).hide();

			spFormSettings.find( '#_image_url' ).val( '' );
		},

		addMediaFields: function() {

			var simpayMediaUploader;

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
						text: spGeneral.i18n.mediaButtonText
					}, multiple: false
				} );

				// When a file is selected, grab the URL and set it as the text field's value
				simpayMediaUploader.on( 'select', function() {

					var attachment = simpayMediaUploader.state().get( 'selection' ).first().toJSON(),
						inputField = window.simpayMediaUploaderInputField.prev(), // Get the field previous to our button, aka our input field.
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

		setupChosen: function() {

			var chosenSelect = $( '.simpay-chosen-select, .simpay-chosen-search' );

			chosenSelect.chosen( { disable_search_threshold: 20 } );
			chosenSelect.chosen();
		},

		// Tabbed Panels in Settings Meta Box.
		// All nav list items are inactive (no "active" class) except first by default.
		// All tab panel content containers are hidden ("simpay-panel-hidden" class) except first by default.
		// Can make specific panel active on initial page load via url hash.

		handleMetaboxTabClick: function() {

			var tabLinks = $( 'ul.simpay-tabs a' );
			var panels = $( 'div.simpay-panel' );
			var allTabLinkParents = tabLinks.parents( 'li' );
			var activeTabLink = {};

			// When a tab link is clicked.
			tabLinks.on( 'click', function( e ) {

				var currentTabLinkParent = $( this ).parent();

				// Assign current tab element to var from link href attribute.
				var currentTabEl = $( $( this ).attr( 'href' ) );

				// Set the hash in the URL so after saving we get the same tab
				location.hash = $( this ).attr( 'href' );

				e.preventDefault();

				// Remove active class from all tabs.
				allTabLinkParents.removeClass( 'active' );

				// Add active class back to current tab.
				currentTabLinkParent.addClass( 'active' );

				// Hide content with all tab panels.
				panels.addClass( 'simpay-panel-hidden' );

				// Show current tab's content.
				currentTabEl.removeClass( 'simpay-panel-hidden' );

				e.stopPropagation();
			} );

			// Auto open tab if in url hash.
			if ( location.hash.length ) {

				// This prevents the hash being used like an anchor
				setTimeout( function() {
					window.scrollTo( 0, 0 );
				}, 1 );

				activeTabLink = $( 'ul.simpay-tabs a[href="' + location.hash + '"]' );

				if ( activeTabLink.length ) {
					activeTabLink.click();
				}
			}
		},

		stripeConnect: function() {
			// Hide initially.
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
		handleInternalLinkToTabClicks: function( el ) {

			var tabToShowId = el.data( 'show-tab' ),
				tabToShowLinkEl = body.find( '.' + tabToShowId + '-tab a' );

			tabToShowLinkEl.click();
		},

		initSectionToggle: function( el ) {

			// TODO DRY
			//var sectionElem = el.closest( '.simpay-panel-field' ).parent().find( showElem );

			var showElem = el.data( 'show' );

			if ( el.is( ':checked' ) ) {
				el.closest( '.simpay-panel-field' ).parent().find( showElem ).show();
			} else {
				el.closest( '.simpay-panel-field' ).parent().find( showElem ).hide();
			}
		},

		initMultiToggle: function( el ) {

			var selectedId = el.attr( 'id' );

			// Hide all options first. This allows us to show multiple sections with the classes
			el.closest( '.simpay-field-radios-inline' ).find( 'input[type="radio"]' ).each( function( i ) {

				// $( this ) in this context is the current iteration, not what is set to elem. so we need to keep it here
				spFormSettings.find( '.toggle-' + $( this ).attr( 'id' ) ).hide();
			} );

			// Show elements that have the correct class
			spFormSettings.find( '.toggle-' + selectedId ).show();
		}
	};

	$( document ).ready( function( $ ) {
		spAdmin.init();
	} );

}( jQuery ) );
