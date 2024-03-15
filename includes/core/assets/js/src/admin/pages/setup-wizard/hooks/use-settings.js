/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const DEFAULT_SETTINGS = {
	simpay_settings: [],
};

/**
 * Returns helpers to interact with WP Simple Pay settings.
 *
 * @return {Object} Settings helpers.
 */
export function useSettings() {
	const shape = [ 'root', 'site', undefined ];

	const { settings, rawSettings } = useSelect(
		( select ) => {
			const { getEditedEntityRecord, getRawEntityRecord } = select(
				'core'
			);

			return {
				settings: getEditedEntityRecord( ...shape ) || DEFAULT_SETTINGS,
				rawSettings: getRawEntityRecord( ...shape ) || DEFAULT_SETTINGS,
			};
		},
		[ shape ]
	);

	const { editEntityRecord, saveEditedEntityRecord } = useDispatch( 'core' );
	const { createSuccessNotice } = useDispatch( 'core/notices' );

	/**
	 * Edits settings.
	 *
	 * @param {Object} changedSettings simpay_settings to change.
	 */
	function editSettings( changedSettings ) {
		editEntityRecord( ...shape, {
			simpay_settings: {
				...settings.simpay_settings,
				...changedSettings,
			},
		} );
	}

	/**
	 * Saves the edited settings and shows a success notice.
	 */
	function saveSettings() {
		saveEditedEntityRecord( ...shape, settings );
		createSuccessNotice( __( 'Settings saved', 'simple-pay' ), {
			type: 'snackbar',
		} );
	}

	/**
	 * Discards edits.
	 */
	function discardChanges() {
		editSettings( rawSettings.simpay_settings );
	}

	return {
		settings: settings.simpay_settings,
		rawSettings: rawSettings.simpay_settings,
		discardChanges,
		editSettings,
		saveSettings,
	};
}
