<?php
/**
 * Setup Wizard: Launcher
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Admin\SetupWizard;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Settings;

/**
 * SetupWizardLaunch class.
 *
 * @since 4.4.2
 */
class SetupWizardLaunch implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_init'               => array(
				array( 'maybe_launch', 10 ),
				array( 'maybe_set_launched', 20 ),
			),
			'simpay_register_settings' => 'add_settings_launch',
			'__unstable_simpay_system_report_actions'
				=> 'add_system_report_launch',
		);
	}

	/**
	 * Redirects to the Setup Wizard if activation has happened recently.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function maybe_launch() {
		// Doing AJAX or cron, do not redirect.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Incorrect permission, do not redirect.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Plugin has been previously installed, do not redirect.
		if ( ! empty( simpay_get_secret_key() ) ) {
			return;
		}

		// Setup wizard has already been visited once, do not redirect.
		if ( get_option( 'simpay_setup_wizard_launched' ) ) {
			return;
		}

		// Plugin has not been recently activated, do not redirect.
		if ( ! get_transient( 'simpay_activation_redirect' ) ) {
			return;
		}

		delete_transient( 'simpay_activation_redirect' );

		// Multisite, do nothing.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		wp_safe_redirect( $this->get_wizard_url() );
		exit;
	}

	/**
	 * Flags that the Setup Wizard has been launched/visited already.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function maybe_set_launched() {
		if ( ! isset( $_GET['page'] ) || 'simpay-setup-wizard' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		update_option( 'simpay_setup_wizard_launched', true );
	}

	/**
	 * Adds a "Launch Setup Wizard" settings button to launch the setup wizard.
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection<\SimplePay\Core\Settings\Setting> $settings Settings collection.
	 * @return void
	 */
	public function add_settings_launch( $settings ) {
		$setup_wizard = new Settings\Setting(
			array(
				'id'         => 'setup-wizard',
				'section'    => 'general',
				'subsection' => 'advanced',
				'label'      => esc_html__( 'Setup Wizard', 'simple-pay' ),
				'output'     => function() {
					printf(
						'<a href="%s" class="button button-secondary">%s</a>',
						esc_url( $this->get_wizard_url() ),
						esc_html__( 'Launch Setup Wizard', 'simple-pay' )
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				},
				'priority'   => 60,
			)
		);

		$settings->add( $setup_wizard );
	}

	/**
	 * Adds a button to launch the Setup Wizard in the System Report.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function add_system_report_launch() {
		?>

		<a href="<?php echo esc_url( $this->get_wizard_url() ); ?>" class="button button-secondary button-large simpay-button-large" style="margin-left: 10px;">
			<?php
			esc_html_e(
				'Launch Setup Wizard',
				'simple-pay'
			);
			?>
		</a>

		<?php
	}

	/**
	 * Returns the URL to the Setup Wizard.
	 *
	 * @todo Should AdminPageInterface include a method to retrieve its own URL,
	 * and then the page passed as a dependency?
	 *
	 * @since 4.4.2
	 *
	 * @return string
	 */
	private function get_wizard_url() {
		return add_query_arg(
			array(
				'page'      => 'simpay-setup-wizard',
				'post_type' => 'simple-pay',
			),
			admin_url( 'edit.php' )
		);
	}

}
