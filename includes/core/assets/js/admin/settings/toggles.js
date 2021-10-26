/* global simpayAdminSettingToggles */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * @typedef {Object} Setting
 *
 * @property {Object} settingObj Setting object.
 * @property {string} settingObj.id Setting ID.
 * @property {mixed} settingObj.value Value the setting should equal to trigger the
 *                                 toggles.
 * @property {string[]} settingObj.toggles Setting IDs to toggle.
 */

/**
 * Typedef {Object} SettingInput
 *
 * @param {string} currentValue Current value.
 * @param {HTMLElement} setting Setting input.
 */

const SETTING_TOGGLES = simpayAdminSettingToggles || [];

/**
 * Retrieves the setting input (or select) and the currnet value.
 *
 * @param {Setting} settingObj Setting object.
 * @return {SettingInput}
 */
function getSetting( settingObj ) {
	const { id: settingId, value: settingValue, toggles } = settingObj;

	// Settting row <tr>.
	const settingRowEl = document.querySelector(
		`.simpay-settings-${ settingId }`
	);

	if ( ! settingRowEl ) {
		return {};
	}

	// Setting input.
	let setting = settingRowEl.querySelector( 'input' );

	if ( ! setting ) {
		setting = settingRowEl.querySelector( 'select' );
	}

	// Setting current value.
	let currentValue = setting.value;

	if ( 'checkbox' === setting.type ) {
		currentValue = true === setting.checked ? 'yes' : 'no';
	}

	return {
		setting,
		currentValue,
	};
}

/**
 * Toggle setting row visibility based on the setting input's current value.
 *
 * @param {Setting} settingObj Setting object.
 */
function toggleSettings( settingObj ) {
	const { id, value: settingValue, toggles } = settingObj;
	const { setting, currentValue } = getSetting( settingObj );

	// Don't adjust dependent settings if this one is already hidden.
	const currentRow = document.querySelector( `.simpay-settings-${ id }` );

	if ( 'none' === currentRow.style.display ) {
		return;
	}

	// Toggle setting row visibility.
	const toToggleRowEls = toggles.map( ( setting ) => {
		return document.querySelector( `.simpay-settings-${ setting }` );
	} );

	toToggleRowEls.forEach( ( el ) => {
		if ( ! el ) {
			return;
		}

		// Show row if the current value of the setting matches expectations.
		el.style.display = currentValue === settingValue ? 'table-row' : 'none';
	} );

	// Run through again with the dependent settings to ensure the current value
	// is used to set the visibility.
	toggles.forEach( ( settingId ) => {
		const currentSettingObj = _.find( SETTING_TOGGLES, {
			id: settingId,
		} );

		if ( currentSettingObj ) {
			toggleSettings( currentSettingObj );
		}
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	// Setup toggles.
	SETTING_TOGGLES.forEach( ( settingObj ) => {
		// Toggle on page load.
		toggleSettings( settingObj );

		// Bind to setting input changes.
		const { setting } = getSetting( settingObj );
		setting.addEventListener( 'change', () =>
			toggleSettings( settingObj )
		);
	} );
} );
