<?php
/**
 * Settings Registration: General
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings\General;

use SimplePay\Core\Settings;
use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the settings section.
 *
 * @param \SimplePay\Core\Settings\Section_Collection $sections Sections collection.
 */
function register_section( $sections ) {
	// General.
	$sections->add(
		new Settings\Section(
			array(
				'id'       => 'general',
				'label'    => esc_html_x( 'General', 'settings section label', 'stripe' ),
				'priority' => 10,
			)
		)
	);
}
add_action( 'simpay_register_settings_sections', __NAMESPACE__ . '\\register_section' );

/**
 * Registers settings subsections.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	// General/Currency.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'currency',
				'section'  => 'general',
				'label'    => esc_html_x( 'Currency', 'settings subsection label', 'stripe' ),
				'priority' => 10,
			)
		)
	);

	// General/Misc.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'advanced',
				'section'  => 'general',
				'label'    => esc_html_x( 'Advanced', 'settings subsection label', 'stripe' ),
				'priority' => 60,
			)
		)
	);
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers the settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	register_currency_settings( $settings );
	register_advanced_settings( $settings );
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Registers currency settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_currency_settings( $settings ) {
	// Currrency.
	$currencies    = i18n\get_stripe_currencies();
	$currency_list = array();

	foreach ( $currencies as $currency_code => $currency_label ) {
		$currency_list[ $currency_code ] = sprintf(
			'%s (%s)',
			$currency_label,
			simpay_get_currency_symbol( $currency_code )
		);
	}

	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'         => 'currency',
				'section'    => 'general',
				'subsection' => 'currency',
				'label'      => esc_html_x(
					'Default Currency',
					'setting label',
					'stripe'
				),
				'options'    => $currency_list,
				'value'      => simpay_get_setting( 'currency', 'USD' ),
				'priority'   => 10,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Currency position.
	$currency = simpay_get_setting( 'currency', 'USD' );
	$symbol   = simpay_get_currency_symbol( $currency );

	$formatted_amount = simpay_format_currency(
		simpay_get_currency_minimum( $currency ),
		$currency,
		false
	);

	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'         => 'currency_position',
				'section'    => 'general',
				'subsection' => 'currency',
				'label'      => esc_html_x( 'Currency Position', 'setting label', 'stripe' ),
				'options'    => array(
					'left'        => esc_html(
						sprintf(
							/* translators: %1$s Currency symbol. %2$s Sample amount. */
							__( 'Left (%1$s%2$s)', 'stripe' ),
							$symbol,
							$formatted_amount
						)
					),
					'right'       => esc_html(
						sprintf(
							/* translators: %1$s Currency symbol. %2$s Sample amount. */
							__( 'Right (%1$s%2$s)', 'stripe' ),
							$formatted_amount,
							$symbol
						)
					),
					'left_space'  => esc_html(
						sprintf(
							/* translators: %1$s Currency symbol. %2$s Sample amount. */
							__( 'Left with Space (%1$s %2$s)', 'stripe' ),
							$symbol,
							$formatted_amount
						)
					),
					'right_space' => esc_html(
						sprintf(
							/* translators: %1$s Currency symbol. %2$s Sample amount. */
							__( 'Right with Space (%1$s %2$s)', 'stripe' ),
							$formatted_amount,
							$symbol
						)
					),
				),
				'value'      => simpay_get_setting( 'currency_position', 'left' ),
				'priority'   => 20,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Comma separator.
	$settings->add(
		new Settings\Setting_Checkbox(
			array(
				'id'          => 'separator',
				'section'     => 'general',
				'subsection'  => 'currency',
				'label'       => esc_html_x( 'Separator', 'setting label', 'stripe' ),
				'input_label' => esc_html__(
					'Use a comma when formatting decimal amounts and use a period to separate thousands.',
					'stripe'
				),
				'value'       => simpay_get_setting( 'separator', 'no' ),
				'description' => wpautop(
					esc_html__(
						'If enabled, amounts will be formatted as "1.234,56" instead of "1,234.56".',
						'stripe'
					)
				),
				'priority'    => 30,
				'schema'     => array(
					'type' => 'string',
					'enum' => array( 'yes', 'no' ),
				),
			)
		)
	);
}

/**
 * Registers advanced settings.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_advanced_settings( $settings ) {
	$license = simpay_get_license();

	if ( true === $license->is_lite() ) {
		/**
		 * Filters if Usage Tracking should be enabled by default.
		 *
		 * @since 4.0.0
		 *
		 * @param bool
		 */
		$default = apply_filters( 'simpay_usage_tracking_enabled_default', false );

		$settings->add(
			new Settings\Setting_Checkbox(
				array(
					'id'          => 'usage_tracking_opt_in',
					'section'     => 'general',
					'subsection'  => 'advanced',
					'label'       => esc_html_x(
						'Usage Analytics',
						'setting label',
						'stripe'
					),
					'input_label' => esc_html__( 'Allow usage analytics', 'stripe' ),
					'value'       => simpay_get_setting(
						'usage_tracking_opt_in',
						false === $default ? 'no' : 'yes'
					),
					'description' => wpautop(
						esc_html__(
							'Your site will be considered as we evaluate new features and determine the best improvements to make. No sensitive data is tracked.',
							'stripe'
						)
					),
					'priority'    => 30,
					'schema'      => array(
						'type'    => 'string',
						'enum'    => array( 'yes', 'no' ),
						'default' => false === $default ? 'no' : 'yes',
					),
				)
			)
		);
	}

	// Save Settings.
	$settings->add(
		new Settings\Setting_Checkbox(
			array(
				'id'          => 'save_settings',
				'section'     => 'general',
				'subsection'  => 'advanced',
				'label'       => esc_html_x(
					'Save Plugin Settings',
					'setting label',
					'stripe'
				),
				'input_label' => esc_html_x(
					'Save plugin settings',
					'setting input label',
					'stripe'
				),
				'value'       => simpay_get_setting( 'save_settings', 'yes' ),
				'description' => wpautop(
					esc_html(
						sprintf(
							/* translators: %s Plugin name. */
							__(
								'If UN-checked, all %s plugin data will be removed when the plugin is deleted. However, your data saved with Stripe will not be deleted.',
								'stripe'
							),
							SIMPLE_PAY_PLUGIN_NAME
						)
					)
				),
				'priority'    => 50,
				'schema'      => array(
					'type' => 'string',
					'enum' => array( 'yes', 'no' ),
				),
			)
		)
	);
}