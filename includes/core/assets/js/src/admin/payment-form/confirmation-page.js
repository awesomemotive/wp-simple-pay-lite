/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Bind color picker.
 *
 * @since 4.12.0
 */
function bindColorPicker() {
	const customColorEl = document.getElementById(
		'confirmation-page-background-color-custom'
	);

	if ( ! customColorEl ) {
		return;
	}

	$( '#confirmation-page-background-color-custom' ).wpColorPicker();

	const customButtonEl = document.querySelector(
		'.simpay-payment-page-background-color .wp-color-result'
	);

	const colorButtonEls = document.querySelectorAll(
		'.simpay-payment-page-background-color'
	);

	customButtonEl.addEventListener( 'click', ( e ) => {
		colorButtonEls.forEach( ( el ) => {
			el.querySelector( 'input' ).checked = false;
		} );

		e.target.classList.add( 'is-selected' );
		$( e.target ).parent().find( 'input' ).prop( 'checked', true );
	} );

	colorButtonEls.forEach( ( el ) => {
		el.querySelector( 'input' ).addEventListener( 'change', () => {
			customButtonEl.classList.remove( 'is-selected' );
		} );
	} );
}

/**
 *
 * Bind payment page mode.
 *
 * @since 4.12.0
 *
 * @return {void}
 */
function bindPaymentPageMode() {
	const paymentPageModeCheckbox = document.getElementById(
		'_enable_payment_page'
	);
	if ( ! paymentPageModeCheckbox ) {
		return;
	}

	const toggleUsePaymentPageConfgurationOption = () => {
		const usePaymentPageConfigurationOption = document.getElementById(
			'_confirmation_page_use_payment_page_config_wrapper'
		);

		if ( ! usePaymentPageConfigurationOption ) {
			return;
		}

		const appearanceSettings = document.getElementById(
			'simpay-dedicated-confimration-page-customization-options'
		);

		if ( paymentPageModeCheckbox.checked ) {
			usePaymentPageConfigurationOption.style.display = '';
			toggleAppearnaceSettings();
		} else {
			setTimeout( () => {
				usePaymentPageConfigurationOption.style.display = 'none';
				appearanceSettings.classList.remove( 'simpay-panel-hidden' );
				toggleAppearnaceSettings();
			}, 100 );
		}
	};
	toggleUsePaymentPageConfgurationOption( paymentPageModeCheckbox );

	paymentPageModeCheckbox.addEventListener(
		'change',
		toggleUsePaymentPageConfgurationOption
	);

	const appearanceMode = document.getElementById(
		'_confirmation_page_use_payment_page_config'
	);
	appearanceMode.addEventListener( 'change', () => {
		toggleAppearnaceSettings();
	} );
	toggleAppearnaceSettings();
}
/**
 * Show/Hide the customization settings based on the appearance mode.
 */
function toggleAppearnaceSettings() {
	const appearanceMode = document.getElementById(
		'_confirmation_page_use_payment_page_config'
	);
	const appearanceSettings = document.getElementById(
		'simpay-dedicated-confimration-page-customization-options'
	);
	const paymentPageModeCheckbox = document.getElementById(
		'_enable_payment_page'
	);

	const modes = document.getElementsByName( '_success_redirect_type' );
	let dedicatedMode = false;

	modes.forEach( ( mode ) => {
		if ( mode.value === 'dedicated' && mode.checked ) {
			dedicatedMode = true;
		}
	} );

	setTimeout( () => {
		if (
			paymentPageModeCheckbox.checked &&
			appearanceMode.checked &&
			dedicatedMode
		) {
			appearanceSettings.classList.add( 'simpay-panel-hidden' );
		} else {
			appearanceSettings.classList.remove( 'simpay-panel-hidden' );
		}
	}, 100 );
}

domReady( () => {
	bindColorPicker();
	bindPaymentPageMode();
} );
