<?php

namespace SimplePay\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notices {

	public $is_admin_screen;

	/**
	 * Notices constructor.
	 */
	public function __construct() {

		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'admin_notices', array( $this, 'show_upgrade_php_version_notice' ) );

		add_action( 'in_plugin_update_message-' . plugin_basename( SIMPLE_PAY_MAIN_FILE ), array(
			$this,
			'in_plugin_update_message',
		), 10, 2 );
	}

	/**
	 * Show the admin notices
	 */
	public function show_notices() {

		if ( current_user_can( 'manage_options' ) ) {
			$this->is_admin_screen = simpay_is_admin_screen();

			$action = isset( $_GET['simpay_action'] ) ? $_GET['simpay_action'] : false;
			$notice = isset( $_GET['simpay_notice'] ) ? $_GET['simpay_notice'] : false;

			if ( 'dismiss_notice' === $action ) {
				$this->dismiss_notice( $notice );
			}

			$this->stripe_connect_notice();
			$this->ssl_error();

			do_action( 'simpay_admin_notices', $this->is_admin_screen );
		}
	}

	/**
	 * Function to display error messages if SSL is not enabled.
	 */
	public function ssl_error() {

		if ( ! is_ssl() && ( ! $this->check_if_dismissed( 'ssl' ) ) ) {

			// TODO: Add docs link here?
			$notice_message = apply_filters( 'simpay_ssl_admin_notice_message', __( 'SSL (HTTPS) is not enabled. You will not be able to process live Stripe transactions until SSL is enabled.', 'stripe' ) );

			self::print_notice( $notice_message, 'error', 'ssl' );
		}
	}

	/**
	 * Function to display an alert to installs that have not authorized through Stripe Connect
	 */
	public function stripe_connect_notice() {
		if( 'simpay_settings' === $this->is_admin_screen && isset( $_GET['tab'] ) && 'keys' === $_GET['tab'] ) {
			return;
		}

		if( $this->check_if_dismissed( 'stripe-connect' ) ) {
			return;
		}

		if ( ! simpay_get_account_id() ) {

			$notice_message = sprintf(
				__( 'WP Simple Pay now supports Stripe Connect for easier setup and improved security. <a href="%s">Click here</a> to connect your Stripe account.', 'stripe' ),
				admin_url( 'admin.php?page=simpay_settings&tab=keys' )
			);

			self::print_notice( $notice_message, 'info', 'stripe-connect' );
		}
	}

	/**
	 * Output the PHP requirement notice.
	 *
	 * This warns users that the plugin will not be able to function in their
	 * environment after a future update.
	 *
	 * @since 3.4.0
	 */
	public function show_upgrade_php_version_notice() {
		$future_required_version = 5.6;

		if ( ! version_compare( PHP_VERSION, $future_required_version, '<' ) ) {
			return;
		}

		$notice_message = '<p><strong>' . __( 'WP Simple Pay is increasing its PHP version requirement.', 'stripe' ) . '</strong></p>';
		$notice_message .= '<p>' . sprintf( __( 'WP Simple Pay will be increasing its PHP requirement to version <code>%1$s</code> or higher in an upcoming release. It looks like you\'re using version <code>%2$s</code>, which means you will need to upgrade your version of PHP to allow the plugin to continue to function. Newer versions of PHP are both faster and more secure. The version you\'re using <a href="%3$s" target="_blank">no longer receives security updates</a>, which is another great reason to update.', 'stripe' ), $future_required_version, PHP_VERSION, 'http://php.net/eol.php' ) . '</p>';

		$notice_message .= '<p><strong>' . __( 'Which version should I upgrade to?', 'stripe' ) . '</strong></p>';
		$notice_message .= '<p>' . sprintf( __( 'In order to be compatible with future versions of WP Simple Pay, you should update your PHP version to <code>%1$s</code>, <code>7.0</code>, <code>7.1</code>, or <code>7.2</code>. On a normal WordPress site, switching to PHP <code>%1$s</code> should never cause issues. We would however actually recommend you switch to PHP <code>7.1</code> or higher to receive the full speed and security benefits provided to more modern and fully supported versions of PHP. However, some plugins may not be fully compatible with PHP <code>7.x</code>, so more testing may be required.', 'stripe' ), $future_required_version ) . '</p>';

		$notice_message .= '<p><strong>' . __( 'Need help upgrading? Ask your web host!', 'stripe' ) . '</strong></p>';
		$notice_message .= '<p>' . sprintf( __( 'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see <a href="%s" target="_blank">our hosting recommendations</a>.', 'stripe' ), 'https://wpsimplepay.com/recommended-wordpress-hosting/' ) . '</p>';

		self::print_notice( $notice_message );
	}

	/**
	 * Print a notice to the screen
	 *
	 * @param        $notice_message
	 * @param string $notice_type
	 * @param string $dismiss_id
	 */
	public static function print_notice( $notice_message, $notice_type = 'error', $dismiss_id = '' ) {

		ob_start();
		?>

		<div class="notice notice-<?php echo esc_attr( $notice_type ); ?>">
			<p>
				<?php echo $notice_message; ?>
			</p>
			<?php
			if ( ! empty( $dismiss_id ) ) {
				?>
				<p>
					<a href="<?php echo add_query_arg( array(
						'simpay_action' => 'dismiss_notice',
						'simpay_notice' => sanitize_key( $dismiss_id ),
					) ); ?>"><?php _e( 'Dismiss Notice', 'stripe' ); ?></a>
				</p>
			<?php } ?>
		</div>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Dismiss a notice
	 *
	 * @param $notice
	 */
	public function dismiss_notice( $notice ) {
		update_option( 'simpay_dismiss_' . $notice, true );
	}

	/**
	 * Check if a notice has already been dismissed
	 *
	 * @param $notice
	 *
	 * @return bool
	 */
	public function check_if_dismissed( $notice ) {
		$notice = get_option( 'simpay_dismiss_' . $notice );

		return $notice;
	}

	/**
	 * Show inline plugin update message from remote readme.txt `== Upgrade Notice ==` section.
	 * Code adapted from W3 Total Cache & WooCommerce.
	 * TODO Eventually tack on additional notices using EDDSL action?
	 *
	 * @param array  $args     Unused parameter.
	 * @param object $response Plugin update response.
	 */
	public function in_plugin_update_message( $args, $response ) {

		$new_version = $response->new_version;
		$upgrade_notice = $this->get_upgrade_notice( $new_version );

		echo apply_filters( 'simpay_in_plugin_update_message', $upgrade_notice ? '</p>' . wp_kses_post( $upgrade_notice ) . '<p class="dummmy" style="display: none;">' : '' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the upgrade notice from hosted readme.txt file.
	 *
	 * @param  string $version Plugin's new version.
	 *
	 * @return string
	 */
	protected function get_upgrade_notice( $version ) {

		$transient_name = 'simpay_upgrade_notice_' . $version;
		$upgrade_notice = get_transient( $transient_name );
		$response       = '';

		if ( false === $upgrade_notice ) {

			if ( class_exists( 'SimplePay\Pro\SimplePayPro' ) )
			{
				// Pro readme.txt from wpsimplepay.com
				$response = wp_safe_remote_get( 'https://wpsimplepay.com/readmes/pro3/readme.txt' );
			} else {
				// Lite readme.txt from wordpress.org
				$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/stripe/trunk/readme.txt' );
			}

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'], $version );

				set_transient( $transient_name, $upgrade_notice, HOUR_IN_SECONDS * 12 );

				// Expire transient quickly for testing.
				//set_transient( $transient_name, $upgrade_notice, 1 );
			}
		}

		return $upgrade_notice;
	}

	/**
	 * Parse update notice from readme.txt file.
	 *
	 * @param  string $content readme.txt file content.
	 * @param  string $new_version Plugin's new version.
	 * @return string
	 */
	private function parse_update_notice( $content, $new_version ) {

		$notice_regexp          = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';
		$upgrade_notice         = '';
		$upgrade_notice_version = '';

		if ( version_compare( SIMPLE_PAY_VERSION, $new_version, '>' ) ) {
			return '';
		}

		$matches = null;

		if ( preg_match( $notice_regexp, $content, $matches ) ) {
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			$upgrade_notice_version = trim( $matches[1] );

			if ( version_compare( SIMPLE_PAY_VERSION, $upgrade_notice_version, '<' ) ) {
				$upgrade_notice .= '<div class="simpay-plugin-upgrade-notice">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
				}

				$upgrade_notice .= '</div>';
			}
		}

		return wp_kses_post( $upgrade_notice );
	}
}
