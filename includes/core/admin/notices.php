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

			$this->api_keys_error();
			$this->ssl_error();

			// TODO Maybe reuse this for upcoming PHP 5.6 requirement.
			/*
			// Show non-dismissable notice for dropping PHP 5.3 next update.
			if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {

				$notice_message = __( 'WP Simple Pay and Stripe are ending compatibility with PHP 5.3 in the next update. Please update PHP before updating WP Simple Pay.', 'simple-pay' ) . '<br/>' .
				                  __( 'We strongly recommend PHP 7.0 or higher.', 'simple-pay' ) .
				                  ' <a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'Click here for more details and a letter you can send to your host.', 'simple-pay' ) . '</a> ';

				self::print_notice( $notice_message );
			}
			*/

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
	 * Function to display error messages for missing API Keys.
	 */
	public function api_keys_error() {

		if ( ! simpay_check_keys_exist() && ( ( false !== $this->is_admin_screen && ( 'simpay_settings' === $this->is_admin_screen && isset( $_GET['tab'] ) && 'keys' !== $_GET['tab'] && 'license' !== $_GET['tab'] ) || 'simpay' === $this->is_admin_screen ) ) ) {

			/* Translators: 1. "test Stripe API keys" OR "live Stripe API keys" 2. Plugin name */
			$notice_message = sprintf( __( 'Your %1$s Stripe API Keys for %2$s have not been entered.', 'stripe' ), ( simpay_is_test_mode() ? _x( 'test', 'Your test Stripe API keys...', 'stripe' ) : _x( 'live', 'Your live Stripe API keys...', 'stripe' ) ), SIMPLE_PAY_PLUGIN_NAME );
			$notice_message .= ' <a href="' . admin_url( 'admin.php?page=simpay_settings&tab=keys' ) . '">' . esc_html__( 'Enter them here.', 'stripe' ) . '</a>';

			self::print_notice( $notice_message, 'error' );
		}
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
