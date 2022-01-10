<?php
/**
 * Usage tracking: functions
 *
 * @package SimplePay\Core\Admin\Usage_Tracking
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core\Admin\Usage_Tracking;

use SimplePay\Core\Settings\Setting_Checkbox;
use SimplePay\Core\Admin\Notice_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL to send checkin data to.
 *
 * @since 3.6.0
 *
 * @return string.
 */
function checkin_url() {
	$checkin_url = add_query_arg( 'edd_action', 'usage_tracking', 'https://wpsimplepay.com' );

	/**
	 * Filters the URL to send checkin data to.
	 *
	 * @since 3.6.0
	 *
	 * @param string $checkin_url URL to send checkin data to.
	 */
	$checkin_url = apply_filters( 'simpay_usage_tracking_checkin_url', $checkin_url );

	return $checkin_url;
}

/**
 * Determines if tracking has been opted-in to.
 *
 * @since 3.6.0
 *
 * @return bool
 */
function is_opted_in() {
	/* This filter is documented in includes/core/admin/usage-tracking/settings.php */
	$default = apply_filters( 'simpay_usage_tracking_enabled_default', false );

	$optin = simpay_get_setting(
		'usage_tracking_opt_in',
		false === $default ? 'no' : 'yes'
	);

	return 'yes' === $optin;
}

/**
 * Determines if Usage Tracking is enabled for the current site.
 *
 * @since 3.6.0
 *
 * @return bool
 */
function is_enabled() {
	// Never track our own site.
	if ( false !== strpos( home_url(), 'wpsimplepay.com' ) ) {
		return false;
	}

	$is_enabled = true;

	// Don't check in if not opted-in.
	if ( ! is_opted_in() ) {
		$is_enabled = false;
	}

	// Don't check in if it's been less than a week since the previous checkin.
	$last_checkin = get_option( 'simpay_usage_tracking_last_checkin', false );

	if ( false !== $last_checkin && $last_checkin > strtotime( '-1 week', time() ) ) {
		$is_enabled = false;
	}

	/**
	 * Filters if the current site should send usage tracking data.
	 *
	 * @since 3.6.0
	 *
	 * @param bool $is_enabled If the tracking should be enabled or not.
	 */
	$is_enabled = apply_filters( 'simpay_usage_tracking_enabled', $is_enabled );

	return (bool) $is_enabled;
}

/**
 * Sends a checkin once a week.
 *
 * @since 3.6.0
 */
function maybe_send_checkin() {
	if ( ! is_enabled() ) {
		return;
	}

	$tracking = new Tracker;
	$tracking->checkin();
}
add_action( 'simpay_weekly_scheduled_events', __NAMESPACE__ . '\\maybe_send_checkin' );

/**
 * Shows a notice to opt-in to usage tracking a day after install.
 *
 * @since 3.6.0
 */
function show_optin_notice() {
	if ( true !== (bool) get_option( 'simpay_usage_tracking_show_optin_notice' ) ) {
		return;
	}

	Notice_Manager::add_notice(
		'usage_tracking_optin',
		array(
			'dismissible' => false,
			'type'        => 'info',
			'callback'    => __NAMESPACE__ . '\\get_optin_notice',
		)
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\show_optin_notice' );

/**
 * Flags a need to show the opt-in notice a day after install.
 *
 * @since 3.6.0
 */
function needs_optin_notice() {
	if ( is_opted_in() ) {
		return;
	}

	add_option( 'simpay_usage_tracking_show_optin_notice', true );
}
add_action( 'simpay_day_after_install_scheduled_events', __NAMESPACE__ . '\\needs_optin_notice' );

/**
 * Save usage tracking preferences from admin notice form.
 *
 * @since 3.6.0
 *
 * @return array
 */
function save_optin_notice_form() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : false;

	if ( ! wp_verify_nonce( $nonce, 'simpay-usage-tracking-optin-nag' ) ) {
		return wp_send_json_error();
	}

	// Update setting.
	simpay_update_setting( 'usage_tracking_opt_in', 'yes' );
	simpay_update_setting( 'usage_tracking_opt_in_consent', time() );

	// Force hide notice.
	Notice_Manager::dismiss_notice( 'usage_tracking_optin' );

	// Send initial checkin.
	$email    = get_bloginfo( 'admin_email' );
	$tracking = new Tracker;

	$tracking->email = $email;
	$tracking->checkin();

	wp_send_json_success();
}
add_action( 'wp_ajax_simpay-usage-tracking-optin-nag', __NAMESPACE__ . '\\save_optin_notice_form' );

/**
 * Generates optin notice strings.
 *
 * @since 3.6.0
 *
 * @return string
 */
function get_optin_notice() {
	ob_start();

	$notice_id   = 'usage_tracking_optin';
	$dismiss_url = add_query_arg(
		array(
			'simpay_dismiss_notice_id'    => $notice_id,
			'simpay_dismiss_notice_nonce' => wp_create_nonce( 'simpay-dismiss-notice-' . $notice_id ),
		),
		( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
	);
	?>

	<div id="simpay-usage-tracking-optin">
		<p>
			<?php
			esc_html_e(
				'Do you want to help us make WP Simple Pay even better? Enabling usage analytics helps us better determine new features and improvements to make. In turn, you get the most out of each and every update to WP Simple Pay.',
				'stripe'
			);
			?>
		</p>

		<p>
			<?php
			wp_nonce_field(
				'simpay-usage-tracking-optin-nag',
				'simpay-usage-tracking-optin-nag'
			);
			?>

			<button
				id="simpay-usage-tracking-opt-in"
				class="button button-primary"
				type="submit"
				name="submit"
			>
				<?php esc_html_e( 'Enable usage analytics', 'stripe' ); ?>
			</button>

			<a
				href="<?php echo esc_url( $dismiss_url ); ?>"
				class="button-link"
				style="margin-left: 5px;"
			>
				<?php esc_html_e( 'No thanks', 'stripe' ); ?>
			</a>
		</p>
	</div>

	<?php
	return ob_get_clean();
}
