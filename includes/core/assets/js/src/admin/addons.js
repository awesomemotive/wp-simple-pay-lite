/* global $, simpayAdmin */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

const s = {
	iconActivate:
		'<i class="fa fa-toggle-on fa-flip-horizontal" aria-hidden="true"></i>',
	iconDeactivate: '<i class="fa fa-toggle-on" aria-hidden="true"></i>',
	iconInstall: '<i class="fa fa-cloud-download" aria-hidden="true"></i>',
	iconSpinner: '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>',
	mediaFrame: false,
};

const { nonce, ajaxUrl, i18n } = simpayAdmin;

const {
	addonActivated,
	addonActivate,
	addonActive,
	addonDeactivate,
	addonInactive,
	addonInstall,
	addonError,
	pluginError,
} = i18n;

/**
 * Change plugin/addon state.
 *
 * @since 1.6.3
 *
 * @param {string}   plugin     Plugin slug or URL for download.
 * @param {string}   state      State status activate|deactivate|install.
 * @param {string}   pluginType Plugin type addon or plugin.
 * @param {Function} callback   Callback for get result from AJAX.
 */
function setAddonState( plugin, state, pluginType, callback ) {
	const actions = {
			activate: 'simpay_activate_addon',
			install: 'simpay_install_addon',
			deactivate: 'simpay_deactivate_addon',
		},
		action = actions[ state ];

	if ( ! action ) {
		return;
	}

	const data = {
		action,
		nonce,
		plugin,
		type: pluginType,
	};

	$.post( ajaxUrl, data, callback ).fail( function ( xhr ) {
		console.log( xhr.responseText );
	} );
}

/**
 * Toggles addon state.
 *
 * @param {PointerEvent} e Click event.
 */
function addonToggle( e ) {
	e.preventDefault();

	const $btn = $( e.target );
	let state, cssClass, stateText, buttonText, errorText, successText;

	if ( $btn.hasClass( 'status-go-to-url' ) ) {
		// Open url in new tab.
		window.open( $btn.attr( 'data-plugin' ), '_blank' );
		return;
	}

	const pluginType = $btn.attr( 'data-type' );

	$btn.prop( 'disabled', true ).addClass( 'loading' );
	$btn.html( s.iconSpinner );

	if ( $btn.hasClass( 'status-active' ) ) {
		// Deactivate.
		state = 'deactivate';
		cssClass = 'status-installed';

		if ( pluginType === 'plugin' ) {
			cssClass += ' button button-secondary';
		}

		stateText = addonInactive;
		buttonText = addonActivate;
		errorText = addonDeactivate;

		if ( pluginType === 'addon' ) {
			buttonText = s.iconActivate + buttonText;
			errorText = s.iconDeactivate + errorText;
		}
	} else if ( $btn.hasClass( 'status-installed' ) ) {
		// Activate.
		state = 'activate';
		cssClass = 'status-active';

		if ( pluginType === 'plugin' ) {
			cssClass += ' button button-secondary disabled';
		}

		stateText = addonActive;
		buttonText = addonDeactivate;

		if ( pluginType === 'addon' ) {
			buttonText = s.iconDeactivate + buttonText;
			errorText = s.iconActivate + addonActivate;
		} else if ( pluginType === 'plugin' ) {
			buttonText = addonActivated;
			errorText = addonActivate;
		}
	} else if ( $btn.hasClass( 'status-missing' ) ) {
		// Install & Activate.
		state = 'install';
		cssClass = 'status-active';

		if ( pluginType === 'plugin' ) {
			cssClass += ' button disabled';
		}

		stateText = addonActive;
		buttonText = addonActivated;
		errorText = s.iconInstall;

		if ( pluginType === 'addon' ) {
			buttonText = s.iconActivate + addonDeactivate;
			errorText += addonInstall;
		}
	} else {
		return;
	}

	const $addon = $btn.closest( '.simpay-addon' ),
		plugin = $btn.attr( 'data-plugin' );

	setAddonState( plugin, state, pluginType, function ( res ) {
		if ( res.success ) {
			if ( 'install' === state ) {
				$btn.attr( 'data-plugin', res.data.basename );

				successText = res.data.msg;

				if ( ! res.data.is_activated ) {
					stateText = addonInactive;
					buttonText =
						'plugin' === pluginType
							? addonActivate
							: s.iconActivate + addonActivate;
					cssClass =
						'plugin' === pluginType
							? 'status-installed button button-secondary'
							: 'status-installed';
				}
			} else {
				successText = res.data;
			}

			$addon
				.find( '.simpay-addon__actions' )
				.append( '<div class="msg success">' + successText + '</div>' );
			$addon
				.find( 'span.status-label' )
				.removeClass( 'status-active status-installed status-missing' )
				.addClass( cssClass )
				.removeClass(
					'button button-primary button-secondary disabled'
				)
				.text( stateText );
			$btn.removeClass( 'status-active status-installed status-missing' )
				.removeClass(
					'button button-primary button-secondary disabled'
				)
				.addClass( cssClass )
				.html( buttonText );
		} else {
			if ( 'object' === typeof res.data ) {
				if ( pluginType === 'addon' ) {
					$addon
						.find( '.simpay-addon__actions' )
						.append(
							'<div class="msg error">' + addonError + '</div>'
						);
				} else {
					$addon
						.find( '.simpay-addon__actions' )
						.append(
							'<div class="msg error">' + pluginError + '</div>'
						);
				}
			} else {
				$addon
					.find( '.simpay-addon__actions' )
					.append( '<div class="msg error">' + res.data + '</div>' );
			}

			if ( 'install' === state && 'plugin' === pluginType ) {
				$btn.addClass( 'status-go-to-url' ).removeClass(
					'status-missing'
				);
			}

			$btn.html( errorText );
		}

		$btn.prop( 'disabled', false ).removeClass( 'loading' );

		// Automatically clear addon messages after 3 seconds.
		setTimeout( function () {
			$addon.find( '.msg' ).remove();
		}, 3000 );
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	if ( ! document.querySelector( '.simpay-addons' ) ) {
		return;
	}

	const addOns = document.querySelectorAll( '.simpay-addon' );

	addOns.forEach( ( addOn ) => {
		addOn
			.querySelector( 'button' )
			.addEventListener( 'click', addonToggle );
	} );
} );
