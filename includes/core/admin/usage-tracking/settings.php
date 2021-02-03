<?php
/**
 * Usage Tracking: Settings
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Admin\Usage_Tracking;

use SimplePay\Core\Settings\Setting_Checkbox;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings for usage tracking.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	/**
	 * Filters if Usage Tracking should be enabled by default.
	 *
	 * @since 4.0.0
	 *
	 * @param bool
	 */
	$default = apply_filters( 'simpay_usage_tracking_enabled_default', false );

	$settings->add(
		new Setting_Checkbox(
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
			)
		)
	);
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );
