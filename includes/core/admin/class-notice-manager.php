<?php
/**
 * Admin notices
 *
 * @package SimplePay\Core\Admin
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Core\Admin;

use Sandhills\Utils\Persistent_Dismissible;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notice_Manager class
 *
 * @since 3.5.0
 */
class Notice_Manager {

	/**
	 * List of core notices.
	 *
	 * @var array
	 */
	private static $core_notices = array();

	/**
	 * List of additional notices added by other functionality.
	 *
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Notice_Manager constructor.
	 *
	 * @since 3.5.0
	 */
	public function __construct() {
		self::$core_notices = array(
			'rest_api_error'     => array(
				'dismissible' => false,
				'type'        => 'error',
				'callback'    => 'SimplePay\Core\Admin\Notices\no_rest_api',
			),
			'ssl_error'          => array(
				'dismissible' => false,
				'type'        => 'error',
				'callback'    => 'SimplePay\Core\Admin\Notices\no_ssl',
			),
			'php_version_56'     => array(
				'dismissible' => false,
				'type'        => 'error',
				'callback'    => 'SimplePay\Core\Admin\Notices\php_version_56',
			),
			'stripe_connect'     => array(
				'dismissible' => true,
				'type'        => 'info',
				'callback'    => 'SimplePay\Core\Admin\Notices\stripe_connect',
			),
		);

		add_action( 'admin_notices', array( __CLASS__, 'show_notices' ) );
		add_action( 'wp_ajax_simpay_dismiss_admin_notice', array( __CLASS__, 'ajax_dismiss_notice' ) );

		add_action( 'admin_init', array( __CLASS__, 'link_dismiss_notice' ), 0 );
	}

	/**
	 * Get all notices.
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	public static function get_notices() {
		return array_merge( self::$core_notices, self::$notices );
	}

	/**
	 * Add a notice to display.
	 *
	 * @since 3.5.0
	 *
	 * @param int   $notice_id Notice ID.
	 * @param array $notice_args Notice arguments.
	 */
	public static function add_notice( $notice_id, $notice_args ) {
		self::$notices[ $notice_id ] = $notice_args;

		return array_unique( self::$notices, SORT_REGULAR );
	}

	/**
	 * Remove a notice from being output.
	 *
	 * @since 3.5.0
	 *
	 * @param int $notice_id Notice ID.
	 */
	public static function remove_notice( $notice_id ) {
		self::$notices = array_diff( self::$notices, array( $notice_id ) );
	}

	/**
	 * Dismiss a notice.
	 *
	 * @since 3.5.0
	 * @since 3.6.0 Directly dismisses notice, instead of an AJAX response.
	 * @since 4.2.0 Support temporary dismissals.
	 *
	 * @param string $notice_id Notice ID.
	 * @param string $lifespan Dismissal lifespan.
	 */
	public static function dismiss_notice( $notice_id, $lifespan = '' ) {
		if ( empty( $lifespan ) ) {
			update_option( 'simpay_dismiss_' . $notice_id, true );
		} else {
			Persistent_Dismissible::set(
				array(
					'id'   => $notice_id,
					'life' => $lifespan,
				)
			);
		}
	}

	/**
	 * Undismisses a notice.
	 *
	 * @since 3.6.0
	 *
	 * @param string $notice_id Notice ID.
	 */
	public static function undismiss_notice( $notice_id ) {
		delete_option( 'simpay_dismiss_' . $notice_id );
	}

	/**
	 * Determine if a notice has been permanently dismissed.
	 *
	 * @since 3.5.0
	 * @since 4.2.0 Support temporary dismissals.
	 *
	 * @param string $notice_id Notice ID.
	 */
	public static function is_notice_dismissed( $notice_id ) {
		$permanent = (bool) get_option( 'simpay_dismiss_' . $notice_id, false );

		if ( true === $permanent ) {
			return true;
		}

		$temporary = (bool) Persistent_Dismissible::get(
			array(
				'id' => $notice_id,
			)
		);

		if ( true === $temporary ) {
			return true;
		}

		return false;
	}

	/**
	 * Output registered notices.
	 *
	 * @since 3.5.0
	 */
	public static function show_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notices = self::get_notices();

		foreach ( $notices as $notice_id => $notice_args ) {
			if ( self::is_notice_dismissed( $notice_id ) ) {
				continue;
			}

			if ( ! is_callable( $notice_args['callback'] ) ) {
				continue;
			}

			$notice = call_user_func( $notice_args['callback'] );

			if ( false !== $notice ) {
				self::print_notice( $notice, $notice_id, $notice_args );
			}
		}
	}

	/**
	 * Dismisses a notice via AJAX.
	 *
	 * @since 3.6.0
	 * @since 4.2.0 Support temporary dismissals.
	 */
	public static function ajax_dismiss_notice() {
		$notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( $_POST['notice_id'] ) : false;
		$nonce     = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : false;

		if ( ! current_user_can( 'manage_options' ) ) {
			return wp_send_json_error();
		}

		if ( ! wp_verify_nonce( $nonce, 'simpay-dismiss-notice-' . $notice_id ) ) {
			return wp_send_json_error();
		}

		$lifespan  = isset( $_POST['lifespan'] )
			? sanitize_text_field( $_POST['lifespan'] )
			: '';

		self::dismiss_notice( $notice_id, $lifespan );

		wp_send_json_success();
	}

	/**
	 * Dismisses a notice via a URL.
	 *
	 * @since 3.6.0
	 */
	public static function link_dismiss_notice() {
		$notice_id = isset( $_GET['simpay_dismiss_notice_id'] )
			? sanitize_text_field( $_GET['simpay_dismiss_notice_id'] )
			: false;

		$nonce = isset( $_GET['simpay_dismiss_notice_nonce'] )
			? sanitize_text_field( $_GET['simpay_dismiss_notice_nonce'] )
			: false;


		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! ( $notice_id && $nonce ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, 'simpay-dismiss-notice-' . $notice_id ) ) {
			return;
		}

		$lifespan = isset( $_GET['simpay_dismiss_notice_lifespan'] )
			? sanitize_text_field( $_GET['simpay_dismiss_notice_lifespan'] )
			: '';

		self::dismiss_notice( $notice_id, $lifespan );
	}

	/**
	 * Print a notice to the screen
	 *
	 * @param string $notice HTML to output in notice.
	 * @param int    $notice_id Notice ID.
	 * @param array  $notice_args Notice arguments.
	 */
	public static function print_notice( $notice, $notice_id, $notice_args ) {
		ob_start();

		$classes = array(
			'simpay-notice',
			'notice',
			'notice-' . $notice_args['type'],
		);

		if ( $notice_args['dismissible'] ) {
			switch ( $notice_id ) {
				// Check lately because the `simpay_can_site_manage_stripe_keys` filter
				// has not run upon notice registration.
				case 'stripe_connect':
					if ( simpay_can_site_manage_stripe_keys() ) {
						$classes[] = 'is-dismissible';
					}

					break;
				default:
					$classes[] = 'is-dismissible';
			}
		}
		?>

<div
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"

		<?php if ( $notice_args['dismissible'] ) : ?>
	data-id="<?php echo esc_attr( $notice_id ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-' . $notice_id ) ); ?>"
	<?php endif; ?>
>
		<?php echo $notice; ?>
</div>

		<?php
		echo ob_get_clean();
	}
}
