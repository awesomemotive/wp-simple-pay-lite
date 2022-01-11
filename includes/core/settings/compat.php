<?php
/**
 * Settings: Compatibility.
 *
 * Route < 4.0 URLs and setting registration.
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings\Compat;

use SimplePay\Core\Settings;
use SimplePay\Core\Utils;
use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects and reroutes legacy URLs for settings.
 *
 * @since 4.0.0
 */
function redirect() {
	if ( ! isset( $_GET['page'] ) || 'simpay_settings' !== $_GET['page'] ) {
		return;
	}

	if ( isset( $_GET['tab'] ) ) {
		$tab     = sanitize_text_field( $_GET['tab'] );
		$section = $tab;

		switch ( $tab ) {
			case 'license':
				$section = 'general';
				break;
			case 'keys':
				$section = 'stripe';
				break;
			case 'display':
				$section = 'payment-confirmations';
				break;
		}

		if ( $section !== $tab ) {
			wp_safe_redirect(
				Settings\get_url(
					array(
						'section' => $section,
					)
				)
			);
		}
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\\redirect' );

/**
 * Captures legacy filters and reregisters subsections with the new API.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	$sections = array(
		'keys',
		'display',
		'general',
	);

	foreach ( $sections as $section ) {
		$filtered_sections = apply_filters(
			'simpay_add_settings_' . $section . '_sections',
			array()
		);

		foreach ( $filtered_sections as $section_id => $section ) {
			$subsection = $subsections->add(
				new Settings\Subsection(
					array(
						'section' => 'stripe',
						'id'      => $section_id,
						'label'   => $section['title'],
					)
				)
			);

			if ( is_wp_error( $subsection ) ) {
				_doing_it_wrong(
					__FUNCTION__,
					$setting->get_error_message(),
					'4.0.0'
				);
			}
		}
	}
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Captures legacy filters and reregisters settings with the new API.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	$sections = array(
		'keys',
		'display',
		'general',
	);

	foreach ( $sections as $section ) {
		$filtered_fields = apply_filters(
			'simpay_add_settings_' . $section . '_fields',
			array()
		);

		switch ( $section ) {
			case 'keys':
				$section = 'stripe';
				break;
			case 'display':
				$section = 'payment-confirmations';
				break;
		}

		foreach ( $filtered_fields as $subsection => $fields ) {
			foreach ( $fields as $field ) {
				$type = $field['type'];

				switch ( $type ) {
					case 'standard':
						$setting_constructor = '\SimplePay\Core\Settings\Setting_Input';
						break;
					case 'select':
						$setting_constructor = '\SimplePay\Core\Settings\Setting_Select';
						break;
					case 'checkbox':
						$setting_constructor = '\SimplePay\Core\Settings\Setting_Checkbox';
						break;
					case 'radio':
						$setting_constructor = '\SimplePay\Core\Settings\Setting_Radio';
						break;
					default:
						$setting_constructor = '\SimplePay\Core\Settings\Setting';
						break;
				}

				$setting = $settings->add(
					new $setting_constructor(
						array(
							'section'     => $section,
							'subsection'  => $subsection,
							'id'          => $field['id'],
							'label'       => $field['title'],
							'type'        => isset( $field['subtype'] )
								? $field['subtype']
								: 'text',
							'options'     => isset( $field['options'] )
								? $field['options']
								: array(),
							'value'       => isset( $field['value'] )
								? $field['value']
								: $field['default'],
							'description' => isset( $field['description'] )
								? $field['description']
								: '',
							'output'      => function() {
								_doing_it_wrong(
									__FUNCTION__,
									__(
										'Ambiguous legacy setting found. Please reregister with Settings API.',
										'stripe'
									),
									'4.0.0'
								);
							},
						)
					)
				);

				if ( is_wp_error( $setting ) ) {
					_doing_it_wrong(
						__FUNCTION__,
						$setting->get_error_message(),
						'4.0.0'
					);
				}
			}
		}
	}
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Maps a legacy setting key to the migrated setting key.
 *
 * @since 4.0.0
 *
 * @param string $legacy_setting Legacy setting key.
 * @return string
 */
function get_setting_key( $legacy_setting ) {
	switch ( $legacy_setting ) {
		case 'country';
			$legacy_setting = 'account_country';
			break;
		case 'site';
			$legacy_setting = 'recaptcha_site_key';
			break;
		case 'secret';
			$legacy_setting = 'recaptcha_secret_key';
			break;
		case 'threshold';
			$legacy_setting = 'recaptcha_score_threshold';
			break;
		case 'locale':
			$legacy_setting = 'stripe_checkout_locale';
			break;
		case 'elements_locale':
			$legacy_setting = 'stripe_elements_locale';
			break;
		case 'endpoint_secret':
			$legacy_setting = simpay_is_test_mode()
				? 'test_webhook_endpoint_secret'
				: 'live_webhook_endpoint_secret';
			break;
		case 'secret_key':
			$legacy_setting = simpay_is_test_mode()
				? 'test_secret_key'
				: 'live_secret_key';
			break;
		case 'publishable_key':
			$legacy_setting = simpay_is_test_mode()
				? 'test_publishable_key'
				: 'live_publishable_key';
			break;
	}

	return $legacy_setting;
}

/**
 * Shims API key retrieval with the value of the migrated settings to ensure
 * the API can always be used.
 *
 * @since 4.0.0
 *
 * @param mixed  $value   Value of the option. If stored serialized, it will be
 *                        unserialized prior to being returned.
 * @param string $option Option name.
 * @return array
 */
function option_simpay_settings_keys( $value, $option ) {
	// Shim "Test Mode" setting.
	if ( isset( $value['mode'] ) && isset( $value['mode']['test_mode'] ) ) {
		$value['mode']['test_mode'] = simpay_get_setting( 'test_mode', 'enabled' );
	}

	// Shim Test Mode key settings.
	if ( isset( $value['test_keys'] ) && isset( $value['test_keys']['secret_key'] ) ) {
		$value['test_keys']['secret_key'] = simpay_get_setting( 'test_secret_key', '' );
	}

	if ( isset( $value['test_keys'] ) && isset( $value['test_keys']['publishable_key'] ) ) {
		$value['test_keys']['publishable_key'] = simpay_get_setting( 'test_publishable_key', '' );
	}

	// Live Mode key settings.
	if ( isset( $value['live_keys'] ) && isset( $value['live_keys']['secret_key'] ) ) {
		$value['live_keys']['secret_key'] = simpay_get_setting( 'live_secret_key', '' );
	}

	if ( isset( $value['live_keys'] ) && isset( $value['live_keys']['publishable_key'] ) ) {
		$value['live_keys']['publishable_key'] = simpay_get_setting( 'live_publishable_key', '' );
	}

	return $value;
}
add_filter( 'option_simpay_settings_keys', __NAMESPACE__ . '\\option_simpay_settings_keys', 10, 3 );
