<?php
/**
 * Settings: Registration
 *
 * All main settings are stored under a single simpay_options option.
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings;

use SimplePay\Core\Utils;
use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings sections.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
 */
function register_sections( $registry ) {
	if ( ! is_admin() ) {
		return;
	}

	// Add Settings Sections registry to Collections registry.
	$sections = new Section_Collection();
	$registry->add( 'settings-sections', $sections );

	/**
	 * Allows further settings sections to be registered.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Section_Collection $sections Sections collection.
	 */
	do_action( 'simpay_register_settings_sections', $sections );
}
add_action( 'simpay_register_collections', __NAMESPACE__ . '\\register_sections' );

/**
 * Registers settings subsections.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
 */
function register_subsections( $registry ) {
	if ( ! is_admin() ) {
		return;
	}

	// Add Settings Sections registry to Collections registry.
	$subsections = new Subsection_Collection();
	$registry->add( 'settings-subsections', $subsections );

	/**
	 * Allows further settings subsections to be registered.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
	 */
	do_action( 'simpay_register_settings_subsections', $subsections );
}
add_action( 'simpay_register_collections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
 */
function register_settings( $registry ) {
	if ( ! is_admin() ) {
		return;
	}

	// Add Settings Sections registry to Collections registry.
	$settings = new Setting_Collection();
	$registry->add( 'settings', $settings );

	/**
	 * Allows further settings subsections to be registered.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	do_action( 'simpay_register_settings', $settings );
}
add_action( 'simpay_register_collections', __NAMESPACE__ . '\\register_settings' );

/**
 * Transforms Sections/Subsections/Settings in to WordPress Settings API
 * compatible registration.
 *
 * @since 4.0.0
 */
function register_wp_settings_api() {
	// Register the single option that stores all settings.
	register_setting(
		'simpay_settings',
		'simpay_settings',
		array(
			'type'              => 'array',
			'description'       => '',
			'sanitize_callback' => 'SimplePay\\Core\\Settings\\save_wp_settings_api',
			'default'           => array(),
			'show_in_rest'      => false,
		)
	);

	// Register subsections.
	// Used to output settings fields on the page.
	//
	// General/Currency, General/reCAPTCHA, etc.
	$subsections = Utils\get_collection( 'settings-subsections' );

	if ( false !== $subsections ) {
		foreach ( $subsections->get_items() as $subsection ) {
			add_settings_section(
				sprintf(
					'simpay_settings_%s_%s',
					$subsection->section,
					$subsection->id
				),
				$subsection->label,
				'',
				sprintf(
					'simpay_settings_%s_%s',
					$subsection->section,
					$subsection->id
				)
			);
		}
	}

	// Register setting fields.
	$settings = Utils\get_collection( 'settings' );

	if ( false !== $settings ) {
		foreach ( $settings->get_items() as $setting ) {
			$args = array(
				'class'     => sprintf(
					'simpay-settings-%s',
					$setting->id
				),
				'label_for' => sprintf(
					'simpay-settings-%s-%s-%s',
					$setting->section,
					$setting->subsection,
					$setting->id
				),
			);

			add_settings_field(
				$setting->id,
				$setting->label,
				array(
					$setting,
					'output',
				),
				sprintf(
					'simpay_settings_%s_%s',
					$setting->section,
					$setting->subsection
				),
				sprintf(
					'simpay_settings_%s_%s',
					$setting->section,
					$setting->subsection
				),
				$args
			);
		}
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_wp_settings_api' );

/**
 * Saves the registered settings.
 *
 * @since 4.0.0
 *
 * @param array $to_save New setting values to save.
 * @return array Setting values to save.
 */
function save_wp_settings_api( $to_save ) {
	$subsection = ! empty( $_POST['subsection'] )
		? sanitize_key( $_POST['subsection'] )
		: false;

	$saved_settings = (array) get_option( 'simpay_settings', array() );

	// Current user cannot manage options, return current value.
	if ( true !== current_user_can( 'manage_options' ) ) {
		return $saved_settings;
	}

	$settings = Utils\get_collection( 'settings' );

	// Registered settings cannot be found, return current value.
	if ( false === $settings ) {
		return $saved_settings;
	}

	$to_save = (array) $to_save;

	// Saving via the Settings form.
	//
	// This is mainly to handle checkbox values that do not get sent to the server
	// when unchecked.
	if ( false !== $subsection ) {
		$registered_settings = $settings->by( 'subsection', $subsection );

		if ( ! empty( $saved_settings ) ) {
			$removed_settings = array_diff(
				array_keys( $registered_settings ),
				array_keys( $to_save )
			);

			foreach ( $removed_settings as $setting_key ) {
				$to_save[ $setting_key ] = 'no';
			}
		}
	}

	// Merge new settings with previously saved.
	$new_settings = array_merge(
		$saved_settings,
		$to_save
	);

	/**
	 * Filters settings before being saved.
	 *
	 * @since 4.0.0
	 *
	 * @param array $new_settings Updated settings.
	 * @param array $to_save      Original setting(s) sent in to be updated.
	 */
	$new_settings = apply_filters( 'simpay_update_settings', $new_settings, $to_save );

	return $new_settings;
}
