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
				'label'    => esc_html_x(
					'🛡️ Anti-Spam',
					'settings subsection label',
					'stripe'
				),
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
	// CAPTCHA toggle.
	$settings->add(
		new Setting(
			array(
				'id'         => 'captcha',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x(
					'CAPTCHA',
					'captcha setup setting label',
					'stripe'
				),
				'output'     => __NAMESPACE__ . '\\choose_captcha_type',
				'priority'   => 10,
			)
		)
	);

	// Google Setup.
	$settings->add(
		new Setting(
			array(
				'id'         => 'recaptcha_setup',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x(
					'Google reCAPTCHA v3',
					'recaptcha setup setting label',
					'stripe'
				),
				'output'     => __NAMESPACE__ . '\\recaptcha_setup_description',
				'priority'   => 20,
			)
		)
	);

	// Google Site Key.
	$settings->add(
		new Setting_Input(
			array(
				'id'         => 'recaptcha_site_key',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x(
					'Site Key',
					'captcha setting label',
					'stripe'
				),
				'value'      => simpay_get_setting( 'recaptcha_site_key', '' ),
				'classes'    => array(
					'regular-text',
				),
				'priority'   => 21,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Google Secret Key.
	$settings->add(
		new Setting_Input(
			array(
				'id'         => 'recaptcha_secret_key',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x(
					'Secret Key',
					'captcha setting label',
					'stripe'
				),
				'value'      => simpay_get_setting( 'recaptcha_secret_key', '' ),
				'classes'    => array(
					'regular-text',
				),
				'priority'   => 22,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Google Score Threshold.
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
					'aggressive'
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
				'priority'    => 23,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);

	// hCaptcha Setup.
	$settings->add(
		new Setting(
			array(
				'id'         => 'hcaptcha_setup',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x(
					'hCaptcha',
					'captcha setup setting label',
					'stripe'
				),
				'output'     => __NAMESPACE__ . '\\hcaptcha_setup_description',
				'priority'   => 30,
			)
		)
	);

	// hCaptcha Site Key.
	$settings->add(
		new Setting_Input(
			array(
				'id'         => 'hcaptcha_site_key',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x(
					'Site Key',
					'captcha setting label',
					'stripe'
				),
				'value'      => simpay_get_setting( 'hcaptcha_site_key', '' ),
				'classes'    => array(
					'regular-text',
				),
				'priority'   => 31,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// hCaptcha Secret Key.
	$settings->add(
		new Setting_Input(
			array(
				'id'         => 'hcaptcha_secret_key',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => esc_html_x(
					'Secret Key',
					'captcha setting label',
					'stripe'
				),
				'value'      => simpay_get_setting( 'hcaptcha_secret_key', '' ),
				'classes'    => array(
					'regular-text',
				),
				'priority'   => 32,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Cloudflare Turnstile Setup.
	if ( simpay_is_upe() ) {
		$settings->add(
			new Setting(
				array(
					'id'         => 'cloudflare_turnstile_setup',
					'section'    => 'general',
					'subsection' => 'recaptcha',
					'label'      => esc_html_x(
						'Cloudflare Turnstile',
						'captcha setup setting label',
						'stripe'
					),
					'output'     => __NAMESPACE__ . '\\cloudflare_turnstile_setup_description',
					'priority'   => 30,
				)
			)
		);

		// Cloudflare Turnstile Site Key.
		$settings->add(
			new Setting_Input(
				array(
					'id'         => 'cloudflare_turnstile_site_key',
					'section'    => 'general',
					'subsection' => 'recaptcha',
					'label'      => esc_html_x(
						'Site Key',
						'captcha setting label',
						'stripe'
					),
					'value'      => simpay_get_setting( 'cloudflare_turnstile_site_key', '' ),
					'classes'    => array(
						'regular-text',
					),
					'priority'   => 31,
					'schema'     => array(
						'type' => 'string',
					),
				)
			)
		);

		// Cloudflare Turnstile Secret Key.
		$settings->add(
			new Setting_Input(
				array(
					'id'         => 'cloudflare_turnstile_secret_key',
					'section'    => 'general',
					'subsection' => 'recaptcha',
					'label'      => esc_html_x(
						'Secret Key',
						'captcha setting label',
						'stripe'
					),
					'value'      => simpay_get_setting( 'cloudflare_turnstile_secret_key', '' ),
					'classes'    => array(
						'regular-text',
					),
					'priority'   => 32,
					'schema'     => array(
						'type' => 'string',
					),
				)
			)
		);
	}

	// None warning.
	$settings->add(
		new Setting(
			array(
				'id'         => 'no_captcha_warning',
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'label'      => '&nbsp;',
				'output'     => __NAMESPACE__ . '\\no_captcha_setup_description',
				'priority'   => 20,
			)
		)
	);
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Outputs CAPTCHA type toggle.
 *
 * @since 4.6.6
 *
 * @return string HTML markup.
 */
function choose_captcha_type() {
	ob_start();

	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );
	?>


	<p>
		<?php
		esc_html_e(
			'A CAPTCHA is an anti-spam technique which helps to protect your website from spam and abuse while letting real people pass through with ease. WP Simple Pay supports two popular services.',
			'stripe'
		);

		echo '&nbsp;';

		echo wp_kses_post(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Learn more about CAPTCHA and how to choose the best option for your website in our %1$sdocumentation%2$s', 'stripe' ),
				sprintf(
					'<a href="%s" target="_blank" class="simpay-external-link">',
					simpay_docs_link(
						'',
						'recaptcha',
						'plugin-settings-captcha',
						true
					)
				),
				Utils\get_external_link_markup() . '</a>'
			)
		);
		?>

	</p>

	<fieldset
		class="simpay-settings-visual-toggles simpay-settings-captcha-type"
		<?php if ( empty( $type ) ) : ?>
			style="margin-bottom: 30px;"
		<?php endif; ?>
	>
		<legend class="screen-reader-text">
			<?php esc_html_e( 'CAPTCHA Type', 'stripe' ); ?>
		</legend>

		<input
			type="radio"
			value="hcaptcha"
			name="simpay_settings[captcha_type]"
			id="simpay-settings-captcha-type-hcaptcha"
			class="simpay-settings-captcha-type--is-recommended"
			<?php checked( $type, 'hcaptcha' ); ?>
		/>
		<label
			for="simpay-settings-captcha-type-hcaptcha"
			class="simpay-settings-visual-toggles__toggle"
		>
			<span class="simpay-settings-visual-toggles__toggle-recommended">
				<?php esc_html_e( 'Recommended', 'stripe' ); ?>
			</span>

			<img
				src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/captcha-hcaptcha.svg' ); ?>"
				alt="<?php esc_attr_e( 'hCaptcha', 'stripe' ); ?>"
				class="simpay-settings-visual-toggles__toggle-icon"
			/>

			<span class="simpay-settings-visual-toggles__toggle-label">
				<?php echo esc_html_e( 'hCaptcha', 'stripe' ); ?>
				<small>
					<?php echo esc_html_e( 'Challenge', 'stripe' ); ?>
				</small>
			</span>
		</label>

		<input
			type="radio"
			value="recaptcha-v3"
			name="simpay_settings[captcha_type]"
			id="simpay-settings-captcha-type-recaptcha"
			class="simpay-settings-captcha-type--is-recommended"
			<?php checked( $type, 'recaptcha-v3' ); ?>
		/>
		<label
			for="simpay-settings-captcha-type-recaptcha"
			class="simpay-settings-visual-toggles__toggle"
		>
			<span class="simpay-settings-visual-toggles__toggle-recommended">
				<?php esc_html_e( 'Recommended', 'stripe' ); ?>
			</span>

			<img
				src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/captcha-recaptcha.svg' ); ?>"
				alt="<?php esc_attr_e( 'reCAPTCHA', 'stripe' ); ?>"
				class="simpay-settings-visual-toggles__toggle-icon"
			/>

			<span class="simpay-settings-visual-toggles__toggle-label">
				<?php echo esc_html_e( 'Google reCAPTCHA', 'stripe' ); ?>
				<small>
					<?php echo esc_html_e( 'Invisible', 'stripe' ); ?>
				</small>
			</span>
		</label>

		<?php if ( simpay_is_upe() ) : ?>
		<input
			type="radio"
			value="cloudflare-turnstile"
			name="simpay_settings[captcha_type]"
			id="simpay-settings-captcha-type-cloudflare-turnstile"
			class="simpay-settings-captcha-type--is-recommended"
			<?php checked( $type, 'cloudflare-turnstile' ); ?>
		/>

		<label
			for="simpay-settings-captcha-type-cloudflare-turnstile"
			class="simpay-settings-visual-toggles__toggle"
		>
			<span class="simpay-settings-visual-toggles__toggle-recommended">
				<?php esc_html_e( 'Recommended', 'stripe' ); ?>
			</span>

			<img
				src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/captcha-cloudflare-turnstile.svg' ); ?>"
				alt="<?php esc_attr_e( 'Cloudflare Turnstile', 'stripe' ); ?>"
				class="simpay-settings-visual-toggles__toggle-icon"
			/>

			<span class="simpay-settings-visual-toggles__toggle-label">
				<?php echo esc_html_e( 'Cloudflare Turnstile', 'stripe' ); ?>
				<small>
					<?php echo esc_html_e( 'Adaptive', 'stripe' ); ?>
				</small>
			</span>
		</label>
		<?php endif; ?>

		<input
			type="radio"
			value="none"
			name="simpay_settings[captcha_type]"
			id="simpay-settings-captcha-type-none"
			class="simpay-settings-captcha-type--is-not-recommended"
			<?php checked( $type, 'none' ); ?>
		/>
		<label
			for="simpay-settings-captcha-type-none"
			class="simpay-settings-visual-toggles__toggle"
		>
			<span class="simpay-settings-visual-toggles__toggle-not-recommended">
				<?php esc_html_e( 'Not Recommended', 'stripe' ); ?>
			</span>

			<img
				src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/captcha-none.svg' ); ?>"
				alt="<?php esc_attr_e( 'No CAPTCHA', 'stripe' ); ?>"
				class="simpay-settings-visual-toggles__toggle-icon"
			/>

			<span class="simpay-settings-visual-toggles__toggle-label">
				<?php echo esc_html_e( 'None', 'stripe' ); ?>
			</span>
		</label>
	</fieldset>

	<?php
	return ob_get_clean();
}

/**
 * Outputs hCaptcha setup content.
 *
 * @since 4.6.6
 *
 * @return string HTML markup.
 */
function hcaptcha_setup_description() {
	ob_start();

	$type     = simpay_get_setting( 'captcha_type', '' );
	$site_key = simpay_get_setting( 'hcaptcha_site_key', '' );
	?>

	<?php if ( ! empty( $site_key ) && 'hcaptcha' === $type ) : ?>
		<div class="notice inline">
			<p>
				<strong>
					<?php esc_html_e( 'Preview', 'stripe' ); ?>
				</strong>
			</p>
			<div class="h-captcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>" style="margin-bottom: 6px;"></div>
		</div>
	<?php endif; ?>

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'hCaptcha is an anti-bot solution that protects user privacy. It is the most popular Google reCAPTCHA alternative. To enable hCaptcha %1$ssign up for hCaptcha (free)%2$s to retrieve the necessary credentials.', 'stripe' ),
			'<a href="https://dashboard.hcaptcha.com/signup" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
			Utils\get_external_link_markup() . '</a>'
		)
	);
	?>
	</p>

	<br />

	<p>
		<a href="https://dashboard.hcaptcha.com/signup" target="_blank" rel="noopener noreferrer"  class="button button-secondary">
			<?php
			esc_html_e( 'Sign Up for hCaptcha (Free)', 'stripe' );
			?>
		</a>
	</p>

	<?php
	// No keys are entered.
	if ( empty( $site_key ) ) {
		return ob_get_clean();
	}

	wp_enqueue_script( 'simpay-hcaptcha', 'https://js.hcaptcha.com/1/api.js' );

	return ob_get_clean();
}

/**
 * Outputs reCAPTCHA setup content.
 *
 * @since 4.6.6
 *
 * @return string HTML markup.
 */
function recaptcha_setup_description() {
	$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
	$default            = ! empty( $existing_recaptcha )
		? 'recaptcha-v3'
		: '';
	$type               = simpay_get_setting( 'captcha_type', $default );

	ob_start();
	?>

	<?php if ( has_keys() && 'recaptcha-v3' === $type ) : ?>
	<div class="notice inline simpay-recaptcha-feedback">
		<p>
			<strong>
				<?php esc_html_e( 'Badge Preview', 'stripe' ); ?>
			</strong>
		</p>
		<img
			src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/captcha-recaptcha-badge.png' ); ?>"
			alt="<?php esc_attr_e( 'reCAPTCHA badge', 'stripe' ); ?>"
			style="border-radius: 3px; box-shadow: 0 0 4px 1px rgba(0, 0, 0, 0.08); width: 256px; border: 1px solid #ddd; margin-bottom: 5px;"
		/>
	</div>
	<?php endif; ?>

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'Google reCAPTCHA is a free anti-spam service from Google which helps to protect your website from spam and abuse while letting real people pass through with ease. To enable reCAPTCHA %1$sregister your site with Google (free)%2$s with reCAPTCHA v3 to retrieve the necessary credentials.', 'stripe' ),
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
			esc_html_e( 'Sign Up for reCAPTCHA v3 (Free)', 'stripe' );
			?>
		</a>
	</p>

	<?php
	// No keys are entered.
	if ( ! has_keys() ) {
		return ob_get_clean();
	}

	if ( 'recaptcha-v3' !== $type ) {
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

/**
 * Outputs Cloudflare Turnstile setup content.
 *
 * @since 4.7.1
 *
 * @return string HTML markup.
 */
function cloudflare_turnstile_setup_description() {
	ob_start();

	$type     = simpay_get_setting( 'captcha_type', '' );
	$site_key = simpay_get_setting( 'cloudflare_turnstile_site_key', '' );
	?>

	<?php if ( ! empty( $site_key ) && 'cloudflare-turnstile' === $type ) : ?>
		<div class="notice inline">
			<img
				src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/captcha-cloudflare-turnstile-badge.png' ); ?>"
				alt="<?php esc_attr_e( 'Cloudflare Turnstile badge', 'stripe' ); ?>"
				style="width: 256px; margin: 10px 0 5px;"
			/>

			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening <strong> tag, do not translate. %2$s Closing </strong> tag, do not translate. */
						__(
							'%1$sNote:%2$s Cloudflare Turnstile is not compatible with overlay payment forms. Please use a different CAPTCHA solution if you are displaying your forms in an overlay.',
							'stripe'
						),
						'<strong>',
						'</strong>'
					),
					array(
						'strong' => array(),
					)
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'Cloudflare Turnstile delivers frustration-free, CAPTCHA-free web experiences to website visitors. To enable Cloudflare Turnstile %1$ssign up at Cloudflare (free)%2$s to retrieve the necessary credentials.', 'stripe' ),
			'<a href="https://www.cloudflare.com/products/turnstile/" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
			Utils\get_external_link_markup() . '</a>'
		)
	);
	?>
	</p>

	<br />

	<p>
		<a href="https://www.cloudflare.com/products/turnstile/" target="_blank" rel="noopener noreferrer"  class="button button-secondary">
			<?php
			esc_html_e( 'Sign Up for Cloudflare Turnstile (Free)', 'stripe' );
			?>
		</a>
	</p>

	<?php
	return ob_get_clean();
}

/**
 * Outputs a warning when disabling captcha.
 *
 * @since 4.6.6
 *
 * @return string HTML markup.
 */
function no_captcha_setup_description() {
	ob_start();
	?>

	<div class="notice inline notice-error">
		<p>
			<strong style="display: block; margin-bottom: 5px;">
				<?php esc_html_e( '🚨 Warning', 'stripe' ); ?>
			</strong>

			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__(
						'Disabling CAPTCHA will make your site more vulnerable to spam and fraudulent payments which can <strong>cost you money and jeopardize your Stripe account</strong>. We highly recommend using a provided CAPTCHA solution. Learn more about CAPTCHA and how to choose the best option for your website in our %1$sdocumentation%2$s',
						'stripe'
					),
					sprintf(
						'<a href="%s" class="simpay-external-link">',
						simpay_docs_link(
							'',
							'recaptcha',
							'plugin-settings-captcha',
							true
						)
					),
					Utils\get_external_link_markup() . '</a>'
				)
			);
			?>
		</p>
	</div>

	<?php
	return ob_get_clean();
}
