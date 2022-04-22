<?php
/**
 * Settings: Registration
 *
 * All main settings are stored under a single simpay_options option.
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
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
 * @since 4.4.2 Always register settings to collection regardless of admin context.
 *
 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
 */
function register_settings( $registry ) {
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
 * Builds schema for registered settings.
 *
 * @since 4.4.2
 *
 * @return array<mixed>
 */
function get_api_schema() {
	$settings = Utils\get_collection( 'settings' );
	$schema   = array(
		'type'       => 'object',
		'properties' => array(),
		'default'    => array(),
	);

	foreach ( $settings->get_items() as $setting ) {
		if ( empty( $setting->schema ) ) {
			continue;
		}

		$schema['properties'][ $setting->id ] = $setting->schema;

		if ( isset( $setting->schema['default'] ) ) {
			$schema['default'][ $setting->id ] = $setting->schema['default'];
		}
	}

	return $schema;
}

/**
 * Removes unregistered or schema-less settings from the setting value when being retrieved by the REST API.
 *
 * This prevents `rest_validate_value_from_schema()` from failing and nullifying the value.
 *
 * @since 4.4.2
 *
 * @param null   $value The setting value to validate. Return null to retrieve the value without modification.
 * @param string $name The setting name.
 * @return array<mixed>
 */
function pre_validate_rest_api_setting( $value, $name ) {
	if ( 'simpay_settings' !== $name ) {
		return $value;
	}

	$registered_settings = Utils\get_collection( 'settings' );

	if ( false === $registered_settings ) {
		return $value;
	}

	$settings = array();

	foreach ( $registered_settings->get_items() as $setting ) {
		if ( empty( $setting->schema ) ) {
			continue;
		}

		$default = isset( $setting->schema['default'] )
			? $setting->schema['default']
			: '';

		$persisted_setting = simpay_get_setting(
			$setting->id,
			$default
		);

		if ( empty( $persisted_setting ) ) {
			continue;
		}

		$settings[ $setting->id ] = $persisted_setting;
	}

	return $settings;
}
add_filter(
	'rest_pre_get_setting',
	__NAMESPACE__ . '\\pre_validate_rest_api_setting',
	10,
	2
);

/**
 * Registers the setting within the WordPress settings API.
 *
 * @since 4.4.2
 * @since 4.4.2 Extracted to its own callback to run on `init`.
 *
 * @return void
 */
function register_wp_setting() {
	register_setting(
		'simpay_settings',
		'simpay_settings',
		array(
			'type'              => 'object',
			'description'       => '',
			'sanitize_callback' => 'SimplePay\\Core\\Settings\\save_wp_settings_api',
			'default'           => array(),
			'show_in_rest'      => array(
				'schema' => get_api_schema(),
			),
		)
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_wp_setting', 99 );
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_wp_setting', 99 );

/**
 * Merges settings sent by the REST API with the existing settings.
 *
 * We do not expose some settings (like API keys) to the REST API so we need to ensure
 * they persist when the serialized simpay_settings option is updated via the REST API.
 *
 * @since 4.4.5
 *
 * @param array<mixed> $new_value New settings.
 * @param array<mixed> $old_value Old settings.
 * @return array<mixed> Merged settings if a REST API request.
 */
function merge_rest_api_updates( $new_value, $old_value ) {
	if ( 0 === did_action( 'rest_api_init' ) ) {
		return $new_value;
	}

	return array_merge(
		$old_value,
		$new_value
	);
}
add_filter(
	'pre_update_option_simpay_settings',
	__NAMESPACE__ . '\\merge_rest_api_updates',
	10,
	2
);

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

	// If this is a REST API request, sanitize via API schema.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return rest_sanitize_value_from_schema(
			$to_save,
			get_api_schema(),
			'simpay_settings'
		);
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
