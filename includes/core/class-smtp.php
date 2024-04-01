<?php
/**
 * Admin pages: SMTP
 *
 * @package SimplePay\Core\Admin\Pages
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Core\Admin\Pages;

use SimplePay\Core\Settings\Setting_Collection;
use SimplePay\Core\Settings\Setting_Input;
use SimplePay\Core\Settings\Subsection;
use SimplePay\Core\Settings\Subsection_Collection;
use SimplePay\Core\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

/**
 * SMTP Sub-page.
 *
 * Add interactive admin subpage that allows installing and activating WP Mail SMTP plugin.
 *
 * @since 4.3.0
 */
class SMTP {

	/**
	 * Admin menu page slug.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	const SLUG = 'simpay_smtp';

	/**
	 * Configuration.
	 *
	 * @since 4.3.0
	 *
	 * @var array
	 */
	private $config = array(
		'lite_plugin'       => 'wp-mail-smtp/wp_mail_smtp.php',
		'lite_wporg_url'    => 'https://wordpress.org/plugins/wp-mail-smtp/',
		'lite_download_url' => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
		'pro_plugin'        => 'wp-mail-smtp-pro/wp_mail_smtp.php',
		'smtp_settings_url' => 'admin.php?page=wp-mail-smtp',
		'smtp_wizard_url'   => 'admin.php?page=wp-mail-smtp-setup-wizard',
	);

	/**
	 * Runtime data used for generating page HTML.
	 *
	 * @since 4.3.0
	 *
	 * @var array
	 */
	private $output_data = array();

	/**
	 * Constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Get the instance of a class and store it in itself.
	 *
	 * @since 4.3.0
	 */
	public static function get_instance() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Hooks.
	 *
	 * @since 4.3.0
	 */
	private function hooks() {
		add_action(
			'simpay_register_settings_subsections',
			array( $this, 'register_settings_subsection' )
		);

		add_action(
			'simpay_register_settings',
			array( $this, 'disable_from_settings' ),
			20
		);

		add_action(
			'simpay_admin_page_settings_emails_end',
			array( $this, 'output' )
		);

		if ( wp_doing_ajax() ) {
			add_action(
				'wp_ajax_simpay_smtp_page_check_plugin_status',
				array( $this, 'ajax_check_plugin_status' )
			);
		}

		// Only load if we are actually on the SMTP page.
		if ( false === $this->is_delivery_subtab() ) {
			return;
		}

		add_action(
			'admin_init',
			array( $this, 'redirect_to_smtp_settings' )
		);

		add_action(
			'admin_enqueue_scripts',
			array( $this, 'enqueue_assets' )
		);
	}

	/**
	 * Determines if we are on the "Delivery" setting subtab.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	private function is_delivery_subtab() {
		if (
			! isset( $_GET['page'] ) ||
			'simpay_settings' !== sanitize_key( wp_unslash( $_GET['page'] ) ) // phpcs:ignore WordPress.CSRF.NonceVerification
		) {
			return false;
		}

		if (
			! isset( $_GET['subsection'] ) ||
			'delivery' !== sanitize_key( wp_unslash( $_GET['subsection'] ) ) // phpcs:ignore WordPress.CSRF.NonceVerification
		) {
			return false;
		}

		return true;
	}

	/**
	 * Registers the "Delivery" subtab in the "Email" settings.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Core\Settings\Subsection_Collection $subsections Subsections.
	 * @return void
	 */
	public function register_settings_subsection( Subsection_Collection $subsections ) {
		$args = array(
			'id'       => 'delivery',
			'section'  => 'emails',
			'label'    => '<span class="dashicons dashicons-email"></span>' .
				esc_html_x(
					'Delivery',
					'settings subsection label',
					'stripe'
				),
			'priority' => 11,
		);

		if ( true === function_exists( 'wp_mail_smtp' ) ) {
			$args['url'] = add_query_arg(
				array(
					'page' => 'wp-mail-smtp',
				),
				admin_url( 'admin.php' )
			);
		}

		$subsections->add( new Subsection( $args ) );
	}

	/**
	 * Disables "From Name" and "From Email" settings when "Force From Email" setting
	 * is enabled in WP Mail SMTP.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings.
	 * @return void
	 */
	public function disable_from_settings( Setting_Collection $settings ) {
		if ( false === function_exists( 'wp_mail_smtp' ) ) {
			return;
		}

		$mail_options = \WPMailSMTP\Options::init()->get_group( 'mail' ); // @phpstan-ignore-line

		// Adjust "From Name".
		if (
			isset( $mail_options['from_name_force'] ) &&
			true === $mail_options['from_name_force']
		) {
			$setting_url = add_query_arg(
				array(
					'page' => 'wp-mail-smtp#wp-mail-smtp-setting-row-from_name',
				),
				admin_url( 'admin.php' )
			);

			$from_name = array_merge(
				(array) $settings->get_item( 'email_from_name' ),
				array(
					'disabled'    => true,
					'description' => wpautop(
						sprintf(
							/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
							__(
								'This setting is disabled because you have the %1$s"Force From Name" setting%2$s enabled from the WP Mail SMTP plugin.',
								'stripe'
							),
							'<a href="' . esc_url( $setting_url ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
							Utils\get_external_link_markup() . '</a>'
						)
					),
				)
			);

			$settings->add( new Setting_Input( $from_name ) );
		}

		// Adjust "From Address".
		if (
			isset( $mail_options['from_email_force'] ) &&
			true === $mail_options['from_email_force']
		) {
			$setting_url = add_query_arg(
				array(
					'page' => 'wp-mail-smtp#wp-mail-smtp-setting-row-from_email',
				),
				admin_url( 'admin.php' )
			);

			$from_address = array_merge(
				(array) $settings->get_item( 'email_from_address' ),
				array(
					'disabled'    => true,
					'description' => wpautop(
						sprintf(
							/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
							__(
								'This setting is disabled because you have the %1$s"Force From Email" setting%2$s enabled from the WP Mail SMTP plugin.',
								'stripe'
							),
							'<a href="' . esc_url( $setting_url ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
							Utils\get_external_link_markup() . '</a>'
						)
					),
				)
			);

			$settings->add( new Setting_Input( $from_address ) );
		}
	}

	/**
	 * Enqueue JS and CSS files.
	 *
	 * @since 4.3.0
	 */
	public function enqueue_assets() {

		// Lightweight, accessible and responsive lightbox.
		wp_enqueue_style(
			'simpay-lity',
			SIMPLE_PAY_INC_URL . 'core/assets/css/vendor/lity/lity.min.css',
			null,
			'3.0.0'
		);

		wp_enqueue_script(
			'simpay-lity',
			SIMPLE_PAY_INC_URL . 'core/assets/js/vendor/lity.min.js',
			array( 'jquery' ),
			'3.0.0',
			true
		);

		// SMTP page style and script.
		wp_enqueue_style(
			'simpay-smtp',
			SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-page-smtp.min.css',
			array(),
			SIMPLE_PAY_VERSION
		);

		wp_enqueue_script(
			'simpay-smtp',
			SIMPLE_PAY_INC_URL . 'core/assets/js/dist/simpay-admin-page-smtp.js',
			array( 'jquery' ),
			SIMPLE_PAY_VERSION,
			true
		);

		wp_localize_script(
			'simpay-smtp',
			'simpay_smtp',
			$this->get_js_strings()
		);
	}

	/**
	 * JS Strings.
	 *
	 * @since 4.3.0
	 *
	 * @return array Array of strings.
	 */
	private function get_js_strings() {

		$error_could_not_install = sprintf(
			wp_kses( /* translators: %s - Lite plugin download URL. */
				__( 'Could not install the plugin automatically. Please <a href="%s">download</a> it and install it manually.', 'stripe' ),
				array(
					'a' => array(
						'href' => true,
					),
				)
			),
			esc_url( $this->config['lite_download_url'] )
		);

		$error_could_not_activate = sprintf(
			wp_kses( /* translators: %s - Lite plugin download URL. */
				__( 'Could not activate the plugin. Please activate it on the <a href="%s">Plugins page</a>.', 'stripe' ),
				array(
					'a' => array(
						'href' => true,
					),
				)
			),
			esc_url( admin_url( 'plugins.php' ) )
		);

		return array(
			'nonce'                    => wp_create_nonce( 'simpay-admin' ),
			'ajax_url'                 => admin_url( 'admin-ajax.php' ),
			'installing'               => esc_html__( 'Installing...', 'stripe' ),
			'activating'               => esc_html__( 'Activating...', 'stripe' ),
			'activated'                => esc_html__( 'WP Mail SMTP Installed & Activated', 'stripe' ),
			'install_now'              => esc_html__( 'Install Now', 'stripe' ),
			'activate_now'             => esc_html__( 'Activate Now', 'stripe' ),
			'download_now'             => esc_html__( 'Download Now', 'stripe' ),
			'plugins_page'             => esc_html__( 'Go to Plugins page', 'stripe' ),
			'error_could_not_install'  => $error_could_not_install,
			'error_could_not_activate' => $error_could_not_activate,
			'manual_install_url'       => $this->config['lite_download_url'],
			'manual_activate_url'      => admin_url( 'plugins.php' ),
			'smtp_settings'            => esc_html__( 'Go to SMTP settings', 'stripe' ),
			'smtp_wizard'              => esc_html__( 'Open Setup Wizard', 'stripe' ),
			'smtp_settings_url'        => esc_url( $this->config['smtp_settings_url'] ),
			'smtp_wizard_url'          => esc_url( $this->config['smtp_wizard_url'] ),
		);
	}

	/**
	 * Generate and output page HTML.
	 *
	 * @since 4.3.0
	 */
	public function output() {
		if ( false === $this->is_delivery_subtab() ) {
			return;
		}

		// Remove submit button.
		add_filter(
			'simpay_admin_page_settings_emails_submit',
			'__return_false'
		);

		echo '<div id="simpay-plugin-page-smtp" class="wrap simpay-plugin-page">';

		$this->output_section_heading();
		$this->output_section_screenshot();
		$this->output_section_step_install();
		$this->output_section_step_setup();

		echo '</div>';
	}

	/**
	 * Generate and output heading section HTML.
	 *
	 * @since 4.3.0
	 */
	private function output_section_heading() {

		// Heading section.
		printf(
			'<section class="top">
				<img class="img-top" src="%1$s" alt="%2$s"/>
				<h1>%3$s</h1>
				<p>%4$s</p>
			</section>',
			esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/smtp/simple-pay-smtp.svg' ),
			esc_attr__( 'WP Mail SMTP ♥ WP Simple Pay', 'stripe' ),
			esc_html__( 'Making Email Deliverability Easy for WordPress', 'stripe' ),
			esc_html__( 'WP Mail SMTP fixes deliverability problems with your WordPress emails so you can reliably send payment receipts, form notifications and more!', 'stripe' )
		);
	}

	/**
	 * Generate and output screenshot section HTML.
	 *
	 * @since 4.3.0
	 */
	private function output_section_screenshot() {

		// Screenshot section.
		printf(
			'<section class="screenshot">
				<div class="cont">
					<img src="%1$s" alt="%2$s"/>
					<a href="%3$s" class="hover" data-lity></a>
				</div>
				<ul>
					<li>%4$s</li>
					<li>%5$s</li>
					<li>%6$s</li>
					<li>%7$s</li>
				</ul>
			</section>',
			esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/smtp/screenshot-tnail.png' ),
			esc_attr__( 'WP Mail SMTP screenshot', 'stripe' ),
			esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/smtp/screenshot-full.png' ),
			esc_html__( 'Improves email deliverability in WordPress.', 'stripe' ),
			esc_html__( 'Used by 2+ million websites.', 'stripe' ),
			esc_html__( 'Free mailers: SMTP.com, Brevo, Google Workspace/ Gmail, Mailgun, Postmark, SendGrid.', 'stripe' ),
			esc_html__( 'Pro mailers: Amazon SES, Microsoft 365/ Outlook.com, Zoho Mail.', 'stripe' )
		);
	}

	/**
	 * Generate and output step 'Install' section HTML.
	 *
	 * @since 4.3.0
	 */
	private function output_section_step_install() {

		$step = $this->get_data_step_install();

		if ( empty( $step ) ) {
			return;
		}

		$button_format       = '<button type="button" class="button %3$s" data-plugin="%1$s" data-action="%4$s">%2$s</button>';
		$button_allowed_html = array(
			'button' => array(
				'class'       => true,
				'data-plugin' => true,
				'data-action' => true,
			),
		);

		if (
			! $this->output_data['plugin_installed'] &&
			! $this->output_data['pro_plugin_installed'] &&
			! current_user_can( 'install_plugins' )
		) {
			$button_format       = '<a class="link" href="%1$s" target="_blank" rel="nofollow noopener" class="simpay-external-link">%2$s ' . Utils\get_external_link_markup() . '</a>';
			$button_allowed_html = array(
				'a'    => array(
					'class'  => true,
					'href'   => true,
					'target' => true,
					'rel'    => true,
				),
				'span' => array(
					'class'       => true,
					'aria-hidden' => true,
				),
			);
		}

		$button = sprintf(
			$button_format,
			esc_attr( $step['plugin'] ),
			esc_html( $step['button_text'] ),
			esc_attr( $step['button_class'] ),
			esc_attr( $step['button_action'] )
		);

		printf(
			'<section class="step step-install">
				<aside class="num">
					<img src="%1$s" alt="%2$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%3$s</h2>
					<p>%4$s</p>
					%5$s
				</div>
			</section>',
			esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/smtp/' . $step['icon'] ),
			esc_attr__( 'Step 1', 'stripe' ),
			esc_html( $step['heading'] ),
			esc_html( $step['description'] ),
			wp_kses( $button, $button_allowed_html )
		);
	}

	/**
	 * Generate and output step 'Setup' section HTML.
	 *
	 * @since 4.3.0
	 */
	private function output_section_step_setup() {

		$step = $this->get_data_step_setup();

		if ( empty( $step ) ) {
			return;
		}

		printf(
			'<section class="step step-setup %1$s">
				<aside class="num">
					<img src="%2$s" alt="%3$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%4$s</h2>
					<p>%5$s</p>
					<button type="button" class="button %6$s" data-url="%7$s">%8$s</button>
				</div>
			</section>',
			esc_attr( $step['section_class'] ),
			esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/smtp/' . $step['icon'] ),
			esc_attr__( 'Step 2', 'stripe' ),
			esc_html__( 'Set Up WP Mail SMTP', 'stripe' ),
			esc_html__( 'Select and configure your mailer.', 'stripe' ),
			esc_attr( $step['button_class'] ),
			esc_url( admin_url( $this->config['smtp_wizard_url'] ) ),
			esc_html( $step['button_text'] )
		);
	}

	/**
	 * Step 'Install' data.
	 *
	 * @since 4.3.0
	 *
	 * @return array Step data.
	 */
	private function get_data_step_install() {

		$step = array();

		$step['heading'] = esc_html__(
			'Install and Activate WP Mail SMTP',
			'stripe'
		);

		$step['description'] = esc_html__(
			'Install WP Mail SMTP from the WordPress.org plugin repository.',
			'stripe'
		);

		$this->output_data['all_plugins']          = get_plugins();
		$this->output_data['plugin_installed']     = array_key_exists(
			$this->config['lite_plugin'],
			$this->output_data['all_plugins']
		);
		$this->output_data['pro_plugin_installed'] = array_key_exists(
			$this->config['pro_plugin'],
			$this->output_data['all_plugins']
		);
		$this->output_data['plugin_activated']     = false;
		$this->output_data['plugin_setup']         = false;

		if (
			! $this->output_data['plugin_installed'] &&
			! $this->output_data['pro_plugin_installed']
		) {
			$step['icon']          = 'step-1.svg';
			$step['button_text']   = esc_html__( 'Install WP Mail SMTP', 'stripe' );
			$step['button_class']  = 'button-primary';
			$step['button_action'] = 'install';
			$step['plugin']        = $this->config['lite_download_url'];

			if ( ! current_user_can( 'install_plugins' ) ) {
				$step['heading']     = esc_html__( 'WP Mail SMTP', 'stripe' );
				$step['description'] = '';
				$step['button_text'] = esc_html__( 'WP Mail SMTP on WordPress.org', 'stripe' );
				$step['plugin']      = $this->config['lite_wporg_url'];
			}
		} else {
			$this->output_data['plugin_activated'] = $this->is_smtp_activated();
			$this->output_data['plugin_setup']     = $this->is_smtp_configured();

			$step['icon'] = $this->output_data['plugin_activated']
				? 'step-complete.svg'
				: 'step-1.svg';

			$step['button_text'] = $this->output_data['plugin_activated']
				? esc_html__( 'WP Mail SMTP Installed & Activated', 'stripe' )
				: esc_html__( 'Activate WP Mail SMTP', 'stripe' );

			$step['button_class'] = $this->output_data['plugin_activated']
				? 'grey disabled'
				: 'button-primary';

			$step['button_action'] = $this->output_data['plugin_activated']
				? ''
				: 'activate';

			$step['plugin'] = $this->output_data['pro_plugin_installed']
				? $this->config['pro_plugin']
				: $this->config['lite_plugin'];
		}

		return $step;
	}

	/**
	 * Step 'Setup' data.
	 *
	 * @since 4.3.0
	 *
	 * @return array Step data.
	 */
	private function get_data_step_setup() {

		$step = array(
			'icon' => 'step-2.svg',
		);

		if ( $this->output_data['plugin_activated'] ) {
			$step['section_class'] = '';
			$step['button_class']  = 'button-primary';
			$step['button_text']   = esc_html__( 'Open Setup Wizard', 'stripe' );
		} else {
			$step['section_class'] = 'grey';
			$step['button_class']  = 'grey disabled';
			$step['button_text']   = esc_html__( 'Start Setup', 'stripe' );
		}

		if ( $this->output_data['plugin_setup'] ) {
			$step['icon']        = 'step-complete.svg';
			$step['button_text'] = esc_html__( 'Go to SMTP settings', 'stripe' );
		}

		return $step;
	}

	/**
	 * Ajax endpoint. Check plugin setup status.
	 * Used to properly init step 'Setup' section after completing step 'Install'.
	 *
	 * @since 4.3.0
	 */
	public function ajax_check_plugin_status() {

		// Security check.
		if ( ! check_ajax_referer( 'simpay-admin', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'error' => esc_html__( 'You do not have permission.', 'stripe' ),
				)
			);
		}

		if ( ! $this->is_smtp_activated() ) {
			wp_send_json_error(
				array(
					'error' => esc_html__( 'Plugin unavailable.', 'stripe' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'setup_status'  => (int) $this->is_smtp_configured(),
				'license_level' => \wp_mail_smtp()->get_license_type(),
			)
		);
	}

	/**
	 * Get $phpmailer instance.
	 *
	 * @since 4.3.0
	 *
	 * @return \PHPMailer|\PHPMailer\PHPMailer\PHPMailer Instance of PHPMailer.
	 */
	private function get_phpmailer() {

		if ( version_compare( get_bloginfo( 'version' ), '5.5-alpha', '<' ) ) {
			$phpmailer = $this->get_phpmailer_v5();
		} else {
			$phpmailer = $this->get_phpmailer_v6();
		}

		return $phpmailer;
	}

	/**
	 * Get $phpmailer v5 instance.
	 *
	 * @since 4.3.0
	 *
	 * @return \PHPMailer Instance of PHPMailer.
	 */
	private function get_phpmailer_v5() {

		global $phpmailer;

		if ( ! ( $phpmailer instanceof \PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			$phpmailer = new \PHPMailer( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $phpmailer;
	}

	/**
	 * Get $phpmailer v6 instance.
	 *
	 * @since 4.3.0
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer Instance of PHPMailer.
	 */
	private function get_phpmailer_v6() {

		global $phpmailer;

		if ( ! ( $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $phpmailer;
	}

	/**
	 * Whether WP Mail SMTP plugin configured or not.
	 *
	 * @since 4.3.0
	 *
	 * @return bool True if some mailer is selected and configured properly.
	 */
	private function is_smtp_configured() {

		if ( ! $this->is_smtp_activated() ) {
			return false;
		}

		$phpmailer = $this->get_phpmailer();
		$mailer    = \WPMailSMTP\Options::init()->get( 'mail', 'mailer' );

		return (
			! empty( $mailer ) &&
			'mail' !== $mailer &&
			\wp_mail_smtp()->get_providers()->get_mailer( $mailer, $phpmailer )->is_mailer_complete()
		);
	}

	/**
	 * Whether WP Mail SMTP plugin active or not.
	 *
	 * @since 4.3.0
	 *
	 * @return bool True if SMTP plugin is active.
	 */
	private function is_smtp_activated() {

		return (
			function_exists( 'wp_mail_smtp' ) &&
			(
				is_plugin_active( $this->config['lite_plugin'] ) ||
				is_plugin_active( $this->config['pro_plugin'] )
			)
		);
	}

	/**
	 * Redirect to SMTP settings page.
	 *
	 * @since 4.3.0
	 */
	public function redirect_to_smtp_settings() {

		// Redirect to SMTP plugin if it is activated.
		if ( $this->is_smtp_configured() ) {
			wp_safe_redirect( admin_url( $this->config['smtp_settings_url'] ) );
			exit;
		}
	}
}

// Init instance.
SMTP::get_instance();
