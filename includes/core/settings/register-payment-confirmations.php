<?php
/**
 * Settings Registration: Payment Confirmation
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 *
 * @todo This should be inside of a "payment confirmations" module.
 * Currently other related things exist inside of includes/core/payments
 */

namespace SimplePay\Core\Settings\Payment_Confirmations;

use SimplePay\Core\Utils;
use SimplePay\Core\Settings;
use SimplePay\Core\i18n;
use SimplePay\Core\Payments\Payment_Confirmation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the settings section.
 *
 * @param \SimplePay\Core\Settings\Section_Collection $sections Sections collection.
 */
function register_section( $sections ) {
	// Payment Confirmations.
	$sections->add(
		new Settings\Section(
			array(
				'id'       => 'payment-confirmations',
				'label'    => esc_html_x(
					'Payment Confirmations',
					'settings section label',
					'stripe'
				),
				'priority' => 30,
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
	// Payment Confirmations/Pages.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'pages',
				'section'  => 'payment-confirmations',
				'label'    => esc_html_x( 'Pages', 'settings subsection label', 'stripe' ),
				'priority' => 10,
			)
		)
	);

	// Payment Confirmations/One-Time Amount.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'one-time',
				'section'  => 'payment-confirmations',
				'label'    => esc_html_x( 'One Time Payment', 'settings subsection label', 'stripe' ),
				'priority' => 20,
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
	register_page_settings( $settings );
	register_one_time_payment_confirmation_settings( $settings );
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Registers page settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_page_settings( $settings ) {
	global $wpdb;

	$pages = $wpdb->get_results(
		"SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' ORDER BY ID"
	);

	$page_list = array();

	foreach ( $pages as $page ) {
		$page_list[ $page->ID ] = $page->post_title;
	}

	// Payment succeeded.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'success_page',
				'section'     => 'payment-confirmations',
				'subsection'  => 'pages',
				'label'       => esc_html_x(
					'Payment Success Page',
					'setting label',
					'stripe'
				),
				'options'     => $page_list,
				'value'       => simpay_get_setting( 'success_page', '' ),
				'description' => wpautop(
					sprintf(
						/* translators: %1$s [simpay_payment_receipt] shortcode. */
						esc_html__(
							'The page customers are sent to after completing a payment. The shortcode %s needs to be on this page.',
							'stripe'
						),
						'<code>[simpay_payment_receipt]</code>'
					)
				),
				'priority'    => 10,
				'schema'      => array(
					'type' => 'integer',
				),
			)
		)
	);

	// Payment failed.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'failure_page',
				'section'     => 'payment-confirmations',
				'subsection'  => 'pages',
				'label'       => esc_html_x(
					'Payment Failure Page',
					'setting label',
					'stripe'
				),
				'options'     => $page_list,
				'value'       => simpay_get_setting( 'failure_page', '' ),
				'description' => wpautop(
					esc_html__(
						'The page customers are sent to after a failed payment.',
						'stripe'
					)
				),
				'priority'    => 20,
				'schema'      => array(
					'type' => 'integer',
				),
			)
		)
	);

	// Payment cancelled.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'cancelled_page',
				'section'     => 'payment-confirmations',
				'subsection'  => 'pages',
				'label'       => esc_html_x(
					'Payment Cancelled Page',
					'setting label',
					'stripe'
				),
				'options'     => $page_list,
				'value'       => simpay_get_setting(
					'cancelled_page',
					simpay_get_setting( 'failure_page', '' )
				),
				'description' => wpautop(
					esc_html__(
						'The page customers are sent to after a cancelling a Stripe.com Checkout Session.',
						'stripe'
					)
				),
				'priority'    => 30,
				'schema'      => array(
					'type' => 'integer',
				),
			)
		)
	);
}

/**
 * Registers "One Time" payment confirmation settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_one_time_payment_confirmation_settings( $settings ) {
	$settings->add(
		new Settings\Setting(
			array(
				'id'         => 'one_time_payment_details',
				'section'    => 'payment-confirmations',
				'subsection' => 'one-time',
				'label'      => esc_html_x(
					'Confirmation Message',
					'setting label',
					'stripe'
				),
				'output'     => function() {
					wp_editor(
						simpay_get_setting(
							'one_time_payment_details',
							Payment_Confirmation\get_one_time_amount_message_default()
						),
						'one_time_payment_details',
						array(
							'textarea_name' => 'simpay_settings[one_time_payment_details]',
							'textarea_rows' => 10,
						)
					);

					Payment_Confirmation\Template_Tags\__unstable_print_tag_list(
						esc_html__(
							'Enter what your customers will see after a successful payment.',
							'stripe'
						),
						Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions()
					);
				},
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);
}
