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

			do_action( 'simpay_admin_notices', $this->is_admin_screen );
		}
	}

	/**
	 *
	 * Function to display error messages if SSL is not enabled
	 *
	 */
	public function ssl_error() {

		if ( ! is_ssl() && ( ! $this->check_if_dismissed( 'ssl' ) ) ) {

			// TODO: Add docs link here?
			$notice_message = apply_filters( 'simpay_ssl_admin_notice_message', __( 'SSL (HTTPS) is not enabled. You will not be able to process live Stripe transactions until SSL is enabled.', 'stripe' ) );

			self::print_notice( $notice_message, 'error', 'ssl' );
		}
	}

	/**
	 *
	 * Function to display error messages for missing API Keys
	 *
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
}
