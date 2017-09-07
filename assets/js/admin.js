/* global spGeneral */

var spAdmin = {};

(function( $ ) {
	'use strict';

	var body,
		spFormSettings;

	spAdmin = {

		init: function() {

			// We need to set these in here because this is when the page is ready to grab this info.
			body = $( document.body );
			spFormSettings = body.find( '#simpay-form-settings' );

			// Use chosen for select fields
			this.setupChosen();

			// Media Uploader
			this.addMediaFields();

			// Init admin metabox tab clicks.
			this.handleMetaboxTabClick();

			// Init internal link to tab clicks.
			spFormSettings.find( '.simpay-tab-link' ).on( 'click.simpayTabLink', function( e ) {
				e.preventDefault();
				spAdmin.handleInternalLinkToTabClicks( $( this ) );
			} );

			// Remove image preview click.
			spFormSettings.on( 'click.simpayImagePreview', '.simpay-remove-image-preview', function( e ) {
				spAdmin.handleRemoveImagePreviewClick( e );
			} );

			// Section toggles
			spFormSettings.find( '.simpay-panel-field' ).on( 'click.simpaySectionToggle', '.simpay-section-toggle', function( e ) {
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
				spAdmin.handleSubmitOnEnter( $( this ) );
			} );

			// Open upgrade link in a new window.
			body.find( 'a[href="admin.php?page=simpay_upgrade"]' ).on( 'click', function() {
				spAdmin.handleUpgradeLink( $( this ) );
			} );

			body.trigger( 'simpayAdminInit' );
		},

		handleUpgradeLink: function( elem ) {
			elem.attr( 'target', '_blank' );
		},

		handleSubmitOnEnter: function( elem ) {

			var keyCode,
				form,
				draftButton,
				publishButton;

			// Get the keycode
			keyCode = ( event.keyCode ? event.keyCode : event.which );

			// Check if the enter button was pressed
			if ( 13 === keyCode ) {

				form = elem.closest( 'form' );

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

		handlePreviewButton: function( elem, e ) {

			var prevFormAction,
				formElem;

			e.preventDefault();

			// Get the form this button belongs to
			formElem = elem.closest( 'form' );

			// Get the form action we need to fall back to
			prevFormAction = formElem.attr( 'action' );

			// Temporarily change the action of our form to point to the preview page
			formElem.attr( 'action', elem.data( 'action' ) );
			formElem.attr( 'target', '_blank' );

			formElem.submit();

			// Revert form action to original and set the target back
			formElem.attr( 'action', prevFormAction );
			formElem.attr( 'target', '' );
		},

		showSpinner: function( elem ) {
			elem.parent().find( '.spinner' ).css( 'visibility', 'visible' );
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

				activeTabLink = $( 'ul.simpay-tabs a[href="' + location.hash + '"]' );

				if ( activeTabLink.length ) {
					activeTabLink.click();
				}
			}
		},

		// Handle links within tab content to other tabs.
		// When one is clicked, trigger the corresponding tab link click.
		handleInternalLinkToTabClicks: function( elem ) {

			var tabToShowId = elem.data( 'show-tab' ),
				tabToShowLinkEl = body.find( '.' + tabToShowId + '-tab a' );

			tabToShowLinkEl.click();
		},

		initSectionToggle: function( elem ) {

			var showElem = elem.data( 'show' );

			if ( elem.is( ':checked' ) ) {
				elem.closest( '.simpay-panel-field' ).parent().find( showElem ).show();
			} else {
				elem.closest( '.simpay-panel-field' ).parent().find( showElem ).hide();
			}
		}
	};

	$( document ).ready( function( $ ) {

		spAdmin.init();
	} );

}( jQuery ) );
