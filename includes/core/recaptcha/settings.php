<?php
/**
 * reCAPTCHA: Settings
 *
 * @package SimplePay\Core\reCAPTCHA
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\reCAPTCHA;

use SimplePay\Core\Utils;
use SimplePay\Core\Settings\Subsection;
use SimplePay\Core\Settings\Setting;
use SimplePay\Core\Settings\Setting_Input;
use SimplePay\Core\Settings\Setting_Select;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the setting subsection.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_settings_subsection( $subsections ) {
	$subsections->add(
		new Subsection(
			array(
				'id'       => 'recaptcha',
				'section'  => 'general',
				'label'    => esc_html_x( 'reCAPTCHA', 'settings subsection label', 'stripe' ),
				'priority' => 30,
			)
		)
	);
}
add_action(
	'simpay_register_settings_subsections',
	__NAMESPACE__ . '\\register_settings_subsection'
);

/**
 * Registers settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	// Setup.
	$settings->add(
		new Setting(
			array(
				'id'         => 'recaptcha_setup',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x( 'Setup', 'recaptcha setup setting label', 'stripe' ),
				'output'     => __NAMESPACE__ . '\\setup_description',
				'priority'   => 10,
			)
		)
	);

	// Site Key.
	$settings->add(
		new Setting_Input(
			array(
				'id'         => 'recaptcha_site_key',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x( 'Site Key', 'recaptcha setting label', 'stripe' ),
				'value'      => simpay_get_setting( 'recaptcha_site_key', '' ),
				'classes'    => array(
					'regular-text',
				),
				'priority'   => 20,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Secret Key.
	$settings->add(
		new Setting_Input(
			array(
				'id'         => 'recaptcha_secret_key',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x( 'Secret Key', 'recaptcha setting label', 'stripe' ),
				'value'      => simpay_get_setting( 'recaptcha_secret_key', '' ),
				'classes'    => array(
					'regular-text',
				),
				'priority'   => 30,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Score Threshold.
	$settings->add(
		new Setting_Select(
			array(
				'id'          => 'recaptcha_score_threshold',
				'section'     => 'general',
				'subsection'  => 'recaptcha',
				'label'       => esc_html_x(
					'Score Threshold',
					'recaptcha setting label',
					'stripe'
				),
				'value'       => simpay_get_setting(
					'recaptcha_score_threshold',
					'default'
				),
				'options'     => array(
					'default'    => esc_html__( 'Default', 'stripe' ),
					'aggressive' => esc_html__( 'Aggressive', 'stripe' ),
				),
				'description' => wpautop(
					esc_html__(
						'Determines how lenient judgement should be on suspected bot usage.',
						'stripe'
					)
				),
				'priority'    => 40,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Outputs reCAPTCHA setup content.
 *
 * @since 3.9.6
 */
function setup_description() {
	ob_start();
	?>

	<?php if ( has_keys() ) : ?>
	<div class="notice inline simpay-recaptcha-feedback" style="display: none;"><p></p></div>
	<?php endif; ?>

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'reCAPTCHA is a free anti-spam service from Google which helps to protect your website from spam and abuse while letting real people pass through with ease. To enable reCAPTCHA %1$sregister your site with Google%2$s with reCAPTCHA v3 to retrieve the necessary credentials.', 'stripe' ),
			'<a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
			Utils\get_external_link_markup() . '</a>'
		)
	);
	?>
	</p>

	<br />

	<p>
		<a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer"  class="button button-secondary">
			<?php
			esc_html_e( 'Sign Up for reCAPTCHA', 'stripe' );
			?>
		</a>
	</p>

	<?php
	// No keys are entered.
	if ( ! has_keys() ) {
		return ob_get_clean();
	}

	$url = add_query_arg(
		array(
			'render' => get_key( 'site' ),
		),
		'https://www.google.com/recaptcha/api.js'
	);

	wp_enqueue_script( 'simpay-google-recaptcha-v3', esc_url( $url ), array(), 'v3', true );

	wp_localize_script(
		'simpay-google-recaptcha-v3',
		'simpayGoogleRecaptcha',
		array(
			'siteKey' => get_key( 'site' ),
			'i18n'    => array(
				'invalid' => esc_html__(
					'Unable to generate and validate reCAPTCHA token. Please verify your Site and Secret keys.',
					'stripe'
				),
			),
		)
	);

	return ob_get_clean();
}
