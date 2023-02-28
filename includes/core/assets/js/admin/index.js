/* global simpayAdmin, spGeneral, jQuery */

/**
 * Internal dependencies.
 */
import hooks from '@wpsimplepay/hooks';
import './settings/toggles.js';
import './settings/test-mode.js';
import './settings/license.js';
import { toggleStripeConnectNotice } from './settings/stripe-connect.js';
import './settings/recaptcha.js';
import './payment-form';
import './addons.js';
import './utils.js';

/**
 * Globallly accessible object of WP Simple Pay-related (admin) functionality.
 */
window.wpsp = window.wpsp || {
	hooks,
};

const {
	licenseLevel,
	i18n: { trashFormConfirm },
} = simpayAdmin;

let spAdmin = {};

( function ( $ ) {
	'use strict';

	let body, spFormSettings;

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
				spFormSettings.on(
					'click',
					'.postbox .simpay-hndle, .postbox .simpay-handlediv',
					window.postboxes.handle_click
				);
			}

			// Stripe Connect toggle notice.
			hooks.addAction(
				'settings.toggleTestMode',
				'wpsp/settings/stripe-connect',
				toggleStripeConnectNotice
			);

			// Setting toggles.
			this.handleFormBuilderSettingToggles();

			hooks.addAction(
				'customFieldAdded',
				'wpsp/payment-form',
				this.handleFormBuilderSettingToggles
			);

			// Init internal link to tab clicks.
			spFormSettings.on(
				'click.simpayTabLink',
				'.simpay-tab-link',
				function ( e ) {
					e.preventDefault();
					spAdmin.handleInternalLinkToTabClicks( $( this ) );
				}
			);

			// Remove image preview click.
			spFormSettings.on(
				'click.simpayImagePreview',
				'.simpay-remove-image-preview',
				function ( e ) {
					spAdmin.handleRemoveImagePreviewClick( e );
				}
			);

			// Use chosen for select fields
			this.setupChosen();

			// Media Uploader
			this.addMediaFields();

			this.stripeConnect();

			// Payment Mode.
			//
			// Disable modes that are not globally available.
			const paymentModeSelector = $( '.simpay-payment-modes' );

			if ( paymentModeSelector.length ) {
				const paymentModes = paymentModeSelector.find( 'input' );

				paymentModes.each( function () {
					const mode = $( this );

					if (
						! paymentModeSelector.hasClass(
							'simpay-payment-mode--' + mode.val()
						)
					) {
						mode.attr( 'disabled', true );
					}
				} );
			}

			this.bindTrashWarning();

			body.trigger( 'simpayAdminInit' );
		},

		/**
		 * Handles the setup of form builder setting toggles.
		 *
		 * @since 4.6.5
		 *
		 * @return {void}
		 */
		handleFormBuilderSettingToggles() {
			[
				'_amount_type',
				'_success_redirect_type',
				'_subscription_type',
				'_subscription_custom_amount',
				'_form_type',
				'_tax_status',
				'_enable_payment_page',
				'_inventory',
				'_inventory_behavior',
				'_schedule_start',
				'_schedule_end',
				'.simpay-total-amount-label-recurring',
				'.simpay-total-amount-label-tax',
				'.simpay-shipping-address',
				'.simpay-text-multiline',
				'.simpay-dropdown-type',
				'.simpay-radio-type',
				'.simpay-price-enable-custom',
				'.simpay-price-type input',
				'.simpay-email-link-enabled',
			].forEach( ( input ) => {
				// Allow classes to be passed, but prefer an input name.
				let inputEl = $( input );
				let isCustomFieldToggle = false;

				if ( '.' !== input.substring( 0, 1 ) ) {
					inputEl = `[name="${ input }"]`;
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
					.on( 'change', inputEl, function () {
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
						parent
							.find(
								$( `.simpay-show-if[data-if="${ input }"]` )
							)
							.each( function () {
								const content = $( this );
								const showIf = content.data( 'is' );

								// All all initially.
								content.hide();

								// Show items where `[data-if=""]` contains the toggle input value.
								if ( showIf.includes( current ) ) {
									content.show().css( 'display', 'block' );
								}
							} );
					} );

				// Trigger initial state.
				$( inputEl ).filter( ':checkbox' ).trigger( 'change' );
				$( inputEl ).filter( ':checked' ).trigger( 'change' );

				if ( $( inputEl ).is( 'select' ) ) {
					$( inputEl ).trigger( 'change' );
				}
			} );
		},

		handleRemoveImagePreviewClick( e ) {
			e.preventDefault();

			$( e.target )
				.parents( 'td' )
				.find( '.simpay-image-preview-wrap' )
				.val( '' )
				.hide();

			$( e.target )
				.parents( 'td' )
				.find( '.simpay-field-image-url' )
				.val( '' );

			$( e.target )
				.parents( 'td' )
				.find( '.simpay-remove-image-preview' )
				.hide();
		},

		addMediaFields() {
			$( '.simpay-media-uploader' ).on( 'click', function ( e ) {
				e.preventDefault();

				// Extend the wp.media object
				const simpayMediaUploader = ( wp.media.frames.file_frame =
					wp.media( {
						title: simpayAdmin.i18n.mediaTitle,
						button: {
							text: simpayAdmin.i18n.mediaButtonText,
						},
						multiple: false,
					} ) );

				const $that = $( this );

				// When a file is selected, grab the URL and set it as the text field's value
				simpayMediaUploader.on( 'select', function () {
					const attachment = simpayMediaUploader
							.state()
							.get( 'selection' )
							.first()
							.toJSON(),
						inputField = $that
							.parents( 'td' )
							.find( '.simpay-field-image-url' ), // Get the field previous to our button, aka our input field.
						image =
							'id' === inputField.data( 'fvalue' )
								? attachment.id
								: attachment.url;

					// Update our image preview
					$that
						.parents( 'td' )
						.find( '.simpay-image-preview-wrap' )
						.show();

					$that
						.parents( 'td' )
						.find( '.simpay-remove-image-preview' )
						.show();

					$that
						.parents( 'td' )
						.find( '.simpay-image-preview' )
						.prop( 'src', image );

					inputField.val( image );
				} );

				// Open the uploader dialog
				simpayMediaUploader.open();
			} );
		},

		setupChosen() {
			const chosenSelect = $(
				'.simpay-chosen-select, .simpay-chosen-search'
			);

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
			tabLinks.on( 'click', function ( e ) {
				e.preventDefault();

				const currentTabLinkParent = $( this ).parent();

				// Assign current tab element to var from link href attribute.
				const currentTabEl = $( $( this ).attr( 'href' ) );

				// Set the hash in the URL so after saving we get the same tab
				const hash = $( this ).attr( 'href' );
				history.pushState( null, null, hash );

				// Avoid jumping to ID after setting anchor.
				setTimeout( function () {
					window.scrollTo( 0, 0 );
				}, 1 );

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

			let activeTab = '#form-display-options-settings-panel';

			// Auto open tab if in url hash.
			if ( location.hash.length ) {
				activeTab = location.hash;
			}

			const activeTabLink = $(
				'ul.simpay-tabs a[href="' + activeTab + '"]'
			);

			$( '[name="simpay_form_settings_tab"]' ).val( activeTab );

			if ( activeTabLink.length ) {
				activeTabLink.click();
			}
		},

		stripeConnect() {
			const rows =
				'tr:nth-child(2), tr:nth-child(3), tr:nth-child(4), tr:nth-child(5)';

			$( '.simpay-settings.stripe-account .form-table' )
				.find( rows )
				.hide();

			$( '#wpsp-api-keys-row-reveal button' ).on(
				'click',
				function ( e ) {
					e.preventDefault();

					$( '.simpay-settings.stripe-account .form-table' )
						.find( rows )
						.show();

					$( '#wpsp-api-keys-row-hide' ).show();
					$( this ).parent().hide();
					$( '.wpsp-manual-key-warning' ).show();
				}
			);

			$( '#wpsp-api-keys-row-hide button' ).on( 'click', function ( e ) {
				e.preventDefault();

				$( '.simpay-settings.stripe-account .form-table' )
					.find( rows )
					.hide();

				$( '#wpsp-api-keys-row-reveal' ).show();
				$( this ).parent().hide();
				$( '.wpsp-manual-key-warning' ).hide();
			} );
		},

		// Handle links within tab content to other tabs.
		// When one is clicked, trigger the corresponding tab link click.
		handleInternalLinkToTabClicks( el ) {
			const tabToShowId = el.data( 'show-tab' ),
				tabToShowLinkEl = body.find( '.' + tabToShowId + '-tab a' );

			tabToShowLinkEl.click();
		},

		/**
		 * Warns users that trashing an existing payment form can affect
		 * subscription-related functionality.
		 *
		 * @since 4.6.7
		 */
		bindTrashWarning() {
			if (
				! [ 'plus', 'professional', 'elite', 'ultimate' ].includes(
					licenseLevel
				)
			) {
				return;
			}

			// Post row action.
			$( '.post-type-simple-pay .submitdelete' ).click( function (
				event
			) {
				// eslint-disable-next-line no-alert, no-undef
				if ( ! confirm( trashFormConfirm ) ) {
					event.preventDefault();
				}
			} );

			// Bulk actions.
			$( '.post-type-simple-pay #posts-filter' ).submit( function (
				event
			) {
				const action = $( this ).find( 'select[name="action"]' ).val();

				if ( 'trash' === action ) {
					// eslint-disable-next-line no-alert, no-undef
					if ( ! confirm( trashFormConfirm ) ) {
						event.preventDefault();
					}
				}
			} );
		},
	};

	$( document ).ready( function ( $ ) {
		spAdmin.init();
	} );
} )( jQuery );
