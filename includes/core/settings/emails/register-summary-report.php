<?php
/**
 * Settings: Emails > Summary Report
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Settings\Emails\SummaryReport;

use SimplePay\Core\Emails;
use Simplepay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers "Email > Summary Report" subsection.
 *
 * @since 4.7.3
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 * @return void
 */
function register_subsection( $subsections ) {
	$email = new Emails\Email\SummaryReportEmail;

	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => $email->get_id(),
				'section'  => 'emails',
				'label'    => esc_html( $email->get_label() ),
				'priority' => 20,
			)
		)
	);
}
add_action(
	'simpay_register_settings_subsections',
	__NAMESPACE__ . '\\register_subsection'
);

/**
 * Registers "Emails > Summary Report" settings.
 *
 * @since 4.7.3
 *
 * @param \SimplePay\Core\Settings\Settings_Collection $settings Settings collection.
 * @return void
 */
function register_settings( $settings ) {
	$email = new Emails\Email\SummaryReportEmail;

	// Enable/Disable.
	$settings->add(
		new Settings\Setting_Checkbox(
			array(
				'id'          => sprintf( 'email_%s', $email->get_id() ),
				'section'     => 'emails',
				'subsection'  => $email->get_id(),
				'label'       => $email->get_label(),
				'input_label' => $email->get_description(),
				'value'       => $email->is_enabled() ? 'yes' : 'no',
				'priority'    => 10,
				'schema'      => array(
					'type'    => 'string',
					'enum'    => array( 'yes', 'no' ),
					'default' => 'yes',
				),
				'toggles'     => array(
					'value'    => 'yes',
					'settings' => array(
						sprintf( 'email_%s_interval', $email->get_id() ),
						sprintf( 'email_%s_to', $email->get_id() ),
					),
				),
			)
		)
	);

	// To.
	$to = simpay_get_setting(
		sprintf( 'email_%s_to', $email->get_id() ),
		esc_html( get_bloginfo( 'admin_email' ) )
	);

	$settings->add(
		new Settings\Setting_Input(
			array(
				'id'         => sprintf( 'email_%s_to', $email->get_id() ),
				'section'    => 'emails',
				'subsection' => $email->get_id(),
				'label'      => esc_html_x(
					'Send To',
					'setting label',
					'stripe'
				),
				'value'      => $to,
				'classes'    => array(
					'regular-text',
				),
				'priority'   => 20,
				'schema'     => array(
					'type'    => 'string',
					'default' => get_option( 'admin_email', '' ),
				),
			)
		)
	);

	// Interval.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => sprintf( 'email_%s_interval', $email->get_id() ),
				'section'     => 'emails',
				'subsection'  => $email->get_id(),
				'label'       => esc_html__( 'Frequency', 'stripe' ),
				'value'       => simpay_get_setting(
					sprintf( 'email_%s_interval', $email->get_id() ),
					'weekly'
				),
				'options'     => $email->get_intervals(),
				'description' => wpautop(
					esc_html__(
						'Determines how often the summary report email will be sent.',
						'stripe'
					)
				),
				'priority'    => 30,
				'schema'      => array(
					'type'    => 'string',
					'enum'    => array( 'weekly', 'monthly' ),
					'default' => 'weekly',
				),
			)
		)
	);
}
add_action(
	'simpay_register_settings',
	__NAMESPACE__ . '\\register_settings'
);
