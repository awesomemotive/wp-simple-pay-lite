/* global simpay_smtp, jQuery */

/**
 * SMTP Sub-page.
 *
 * @since 4.3.0
 */

'use strict';

const SimPaySMTP =
	window.SimPaySMTP ||
	( function ( document, window, $ ) {
		/**
		 * Elements.
		 *
		 * @since 4.3.0
		 *
		 * @type {Object}
		 */
		let el = {};

		/**
		 * Public functions and properties.
		 *
		 * @since 4.3.0
		 *
		 * @type {Object}
		 */
		const app = {
			/**
			 * Start the engine.
			 *
			 * @since 4.3.0
			 */
			init() {
				$( app.ready );
			},

			/**
			 * Document ready.
			 *
			 * @since 4.3.0
			 */
			ready() {
				app.initVars();
				app.events();
			},

			/**
			 * Init constiables.
			 *
			 * @since 4.3.0
			 */
			initVars() {
				el = {
					$stepInstall: $( 'section.step-install' ),
					$stepInstallNum: $( 'section.step-install .num img' ),
					$stepSetup: $( 'section.step-setup' ),
					$stepSetupNum: $( 'section.step-setup .num img' ),
				};
			},

			/**
			 * Register JS events.
			 *
			 * @since 4.3.0
			 */
			events() {
				// Step 'Install' button click.
				el.$stepInstall.on( 'click', 'button', app.stepInstallClick );

				// Step 'Setup' button click.
				el.$stepSetup.on( 'click', 'button', app.gotoURL );
			},

			/**
			 * Step 'Install' button click.
			 *
			 * @param e
			 * @since 4.3.0
			 */
			stepInstallClick( e ) {
				e.preventDefault();

				let $btn = $( this ),
					action = $btn.attr( 'data-action' ),
					plugin = $btn.attr( 'data-plugin' ),
					btnTextOrigin = $btn.text(),
					ajaxAction = '';

				if ( $btn.hasClass( 'disabled' ) ) {
					return;
				}

				switch ( action ) {
					case 'activate':
						ajaxAction = 'simpay_activate_plugin';
						$btn.text( simpay_smtp.activating );
						break;

					case 'install':
						ajaxAction = 'simpay_install_plugin';
						$btn.text( simpay_smtp.installing );
						break;

					case 'goto-url':
						window.location.href = $btn.attr( 'data-url' );
						return;

					default:
						return;
				}

				$btn.addClass( 'disabled' );
				app.showSpinner( el.$stepInstallNum );

				const data = {
					action: ajaxAction,
					nonce: simpay_smtp.nonce,
					plugin,
				};

				$.post( simpay_smtp.ajax_url, data )

					.done( function ( res ) {
						app.stepInstallDone( res, $btn, action );
					} )

					.fail( function () {
						$btn.removeClass( 'disabled' );
						$btn.text( btnTextOrigin );
					} )

					.always( function () {
						app.hideSpinner( el.$stepInstallNum );
					} );
			},

			/**
			 * Done part of the 'Install' step.
			 *
			 * @since 4.3.0
			 *
			 * @param {Object} res    Result of $.post() query.
			 * @param {jQuery} $btn   Button.
			 * @param {string} action Action (for more info look at the app.stepInstallClick() function).
			 */
			stepInstallDone( res, $btn, action ) {
				const success =
					action === 'install'
						? res.success && res.data.is_activated
						: res.success;

				if ( success ) {
					el.$stepInstallNum.attr(
						'src',
						el.$stepInstallNum
							.attr( 'src' )
							.replace( 'step-1.', 'step-complete.' )
					);
					$btn.addClass( 'grey' )
						.removeClass( 'button-primary' )
						.text( simpay_smtp.activated );
					app.stepInstallPluginStatus();

					return;
				}

				const activationFail =
						( 'install' === action &&
							res.success &&
							! res.data.is_activated ) ||
						'activate' === action,
					url = ! activationFail
						? simpay_smtp.manual_install_url
						: simpay_smtp.manual_activate_url,
					msg = ! activationFail
						? simpay_smtp.error_could_not_install
						: simpay_smtp.error_could_not_activate,
					btn = ! activationFail
						? simpay_smtp.download_now
						: simpay_smtp.plugins_page;

				$btn.removeClass( 'grey disabled' )
					.text( btn )
					.attr( 'data-action', 'goto-url' )
					.attr( 'data-url', url );
				$btn.after( '<p class="error">' + msg + '</p>' );
			},

			/**
			 * Callback for step 'Install' completion.
			 *
			 * @since 4.3.0
			 */
			stepInstallPluginStatus() {
				const data = {
					action: 'simpay_smtp_page_check_plugin_status',
					nonce: simpay_smtp.nonce,
				};

				$.post( simpay_smtp.ajax_url, data ).done(
					app.stepInstallPluginStatusDone
				);
			},

			/**
			 * Done part of the callback for step 'Install' completion.
			 *
			 * @since 4.3.0
			 *
			 * @param {Object} res Result of $.post() query.
			 */
			stepInstallPluginStatusDone( res ) {
				if ( ! res.success ) {
					return;
				}

				el.$stepSetup.removeClass( 'grey' );
				el.$stepSetupBtn = el.$stepSetup.find( 'button' );
				el.$stepSetupBtn
					.removeClass( 'grey disabled' )
					.addClass( 'button-primary' );

				if ( res.data.setup_status > 0 ) {
					el.$stepSetupNum.attr(
						'src',
						el.$stepSetupNum
							.attr( 'src' )
							.replace( 'step-2.svg', 'step-complete.svg' )
					);
					el.$stepSetupBtn
						.attr( 'data-url', simpay_smtp.smtp_settings_url )
						.text( simpay_smtp.smtp_settings );

					return;
				}

				el.$stepSetupBtn
					.attr( 'data-url', simpay_smtp.smtp_wizard_url )
					.text( simpay_smtp.smtp_wizard );
			},

			/**
			 * Go to URL by click on the button.
			 *
			 * @since 4.3.0
			 */
			gotoURL() {
				const $btn = $( this );

				if ( $btn.hasClass( 'disabled' ) ) {
					return;
				}

				window.location.href = $btn.attr( 'data-url' );
			},

			/**
			 * Display spinner.
			 *
			 * @since 4.3.0
			 *
			 * @param {jQuery} $el Section number image jQuery object.
			 */
			showSpinner( $el ) {
				$el.siblings( '.loader' ).removeClass( 'hidden' );
			},

			/**
			 * Hide spinner.
			 *
			 * @since 4.3.0
			 *
			 * @param {jQuery} $el Section number image jQuery object.
			 */
			hideSpinner( $el ) {
				$el.siblings( '.loader' ).addClass( 'hidden' );
			},
		};

		// Provide access to public functions/properties.
		return app;
	} )( document, window, jQuery );

// Initialize.
SimPaySMTP.init();
