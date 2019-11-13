<?php
/**
 * Usage tracking.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Admin\Usage_Tracking;

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
	$optin = simpay_get_global_setting( 'usage_tracking_opt_in' );

	return ( $optin && 'yes' === $optin );
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
	// Never show notice for 2.3.2.
	// @link https://github.com/wpsimplepay/wp-simple-pay-lite/issues/114
	return;

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
	$settings = get_option( 'simpay_settings_general' );

	$settings['general_misc']['usage_tracking_opt_in']         = 'yes';
	$settings['general_misc']['usage_tracking_opt_in_consent'] = time();

	update_option( 'simpay_settings_general', $settings );

	// Force hide notice.
	Notice_Manager::dismiss_notice( 'usage_tracking_optin' );

	// Send initial checkin.
	// Email address is sent for further processing on server.
	$email    = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : get_bloginfo( 'admin_email' );
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

<p style="margin-top: 0.75em;"><strong><?php esc_html_e( 'Save 10% on WP Simple Pay Pro', 'stripe' ); ?></strong></p>

<p>
<?php esc_html_e( 'Do you want to help us make WP Simple Pay even better? Enabling usage analytics helps us better determine new features and improvements to make. In turn, you get the most out of each and every update to WP Simple Pay.', 'stripe' ); ?>
</p>

<p>
<?php
echo wp_kses(
	sprintf(
		/* translators: %1$s Opening <strong> tag, do not translate. %2$s Closing </strong> tag, do not translate. %1$s Opening <a> tag, do not translate. %4$s Closing <a> tag, do not translate. */
		esc_html__( 'Subscribe now and we will immediately send a %1$s10&#37; off%2$s discount code to your inbox. This code can be used to upgrade to any WP Simple Pay Pro license. No sensitive data is ever collected or stored. You may unsubscribe at anytime.', 'stripe' ),
		'<strong>',
		'</strong>'
	),
	array(
		'strong' => true,
	)
);
?></p>

<form
	id="simpay-usage-tracking-nag"
	style="margin-bottom: 1em;"
	target="_blank"
	method="GET"
	action="https://wpsimplepay.com/subscribe-ua/"
	enctype="application/x-www-form-urlencoded"
>
	<p>
		<label for="simpay-usage-tracking-email"><?php esc_html_e( 'First Name', 'stripe' ); ?></label><br />
		<input type="text" name="edd_jilt_fname" id="simpay-usage-tracking-first-name" class="regular-text" value="" />
	</p>
	<p>
		<label for="simpay-usage-tracking-email"><?php esc_html_e( 'Email Address', 'stripe' ); ?></label><br />
		<input type="text" name="edd_jilt_email" id="simpay-usage-tracking-email" class="regular-text" value="<?php echo bloginfo( 'admin_email' ); ?>" />
	</p>
	<p>
		<button type="submit" name="submit" class="button button-primary"><?php esc_html_e( 'Yes, enable and send me the coupon!', 'stripe' ); ?></button>
		<input type="hidden" name="utm_source" value="inside-plugin" />
		<input type="hidden" name="utm_medium" value="form" />
		<input type="hidden" name="utm_campaign" value="<?php echo esc_attr( apply_filters( 'simpay_utm_campaign', 'lite-plugin' ) ); ?>" />
		<input type="hidden" name="utm_content" value="usage-tracking-admin-notice" />
		<?php wp_nonce_field( 'simpay-usage-tracking-optin-nag', 'simpay-usage-tracking-optin-nag' ); ?>
		<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button-link" style="margin-left: 5px;"><?php esc_html_e( 'No thanks', 'stripe' ); ?></a>
	</p>
</form>

<?php
	return ob_get_clean();
}

/**
 * Adds usage tracking opt-in setting.
 *
 * @since 3.6.0
 *
 * @param array $fields Setting fields.
 * @return array
 */
function add_admin_setting( $fields ) {
	$group   = 'general';
	$id      = 'settings';
	$section = 'general_misc';

	$fields[ $section ]['usage_tracking_opt_in'] = array(
		'title'       => esc_html__( 'Usage Tracking', 'stripe' ),
		'text'        => esc_html__( 'Allow', 'stripe' ),
		'type'        => 'checkbox',
		'name'        => 'simpay_' . $id . '_' . $group . '[' . $section . '][usage_tracking_opt_in]',
		'id'          => 'simpay-' . $id . '-' . $group . '-' . $section . '-usage-tracking-opt-in',
		'value'       => simpay_get_global_setting( 'usage_tracking_opt_in' ),
		'default'     => 'no',
		'description' => esc_html__( 'Your site will be considered as we evaluate new features and determine the best improvements to make. No sensitive data is tracked.', 'stripe' ),
	);

	return $fields;
}
add_filter( 'simpay_add_settings_general_fields', __NAMESPACE__ . '\\add_admin_setting' );
