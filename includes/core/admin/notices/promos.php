<?php
/**
 * Notices: Promos
 *
 * Additional promotional notices.
 *
 * @package SimplePay\Core\Admin\Notices\Promos
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace SimplePay\Core\Admin\Notices\Promos;

use SimplePay\Core\Admin\Notice_Manager;

/**
 * Includes the views required for a "Generic" upgrade promo.
 *
 * Used mainly in the "Edit Form" tab views.
 *
 * @todo Implement against a better Notice registry.
 *
 * @since 3.6.5
 */
function tab_view() {
	if ( class_exists( '\SimplePay\Pro\Lite_Helper', false ) ) {
		return;
	}

	?>
	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<td style="border-bottom: 0;">
				<?php
				include( dirname( __FILE__ ) . '/promos/general/promo-under-box-header.php' );
				include( dirname( __FILE__ ) . '/promos/general/generic-tab-promo.php' );
				include( dirname( __FILE__ ) . '/promos/general/promo-under-box-footer.php' );
				?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
add_action( 'simpay_form_settings_meta_payment_options_panel', __NAMESPACE__ . '\\tab_view' );
add_action( 'simpay_admin_after_form_display_options_rows', __NAMESPACE__ . '\\tab_view', 99 );
add_action( 'simpay_admin_after_stripe_checkout', __NAMESPACE__ . '\\tab_view' );

/**
 * Changes the Settings sidebar promo for Black Friday/Cyber Monday.
 *
 * @since 3.6.5
 *
 * @param string $template Template path.
 * @return string
 */
function bfcm_sidebar_template( $template ) {
	$bfcm_is_promo_active = bfcm_is_promo_active();

	if ( false === $bfcm_is_promo_active ) {
		return $template;
	}

	wp_enqueue_style(
		'simpay-promo-bfcm',
		SIMPLE_PAY_URL . '/includes/core/admin/notices/promos/bfcm/style.css',
		array(),
		SIMPLE_PAY_VERSION
	);

	$template = SIMPLE_PAY_INC . 'core/admin/notices/promos/bfcm/sidebar.php';

	return $template;
}
add_filter( 'simpay_settings_sidebar_template', __NAMESPACE__ . '\\bfcm_sidebar_template' );

/**
 * Find out if a Black Friday/Cyber Monday promotion is active.
 *
 * @since 3.6.5
 *
 * @return bool
 */
function bfcm_is_promo_active() {
	$start = strtotime( '2019-11-29 06:00:00' );
	$end   = strtotime( '2019-12-07 05:59:59' );
	$now   = time();

	if ( ( $now > $start ) && ( $now < $end ) ) {
		return true;
	}

	return false;
}

/**
 * Outputs a top-of-page promotion to upgrade to Pro, or upgrade a license.
 *
 * @since 4.2.0
 */
function license_upgrade_promo() {
	$current_screen = get_current_screen();

	// Not on a WP Simple Pay page, show nothing.
	if (
		false === isset( $current_screen->post_type ) ||
		'simple-pay' !== $current_screen->post_type
	) {
		return;
	}

	// Dismissed, show nothing.
	$dismissed = Notice_Manager::is_notice_dismissed( 'simpay-license-upgrade' );

	if ( true === $dismissed ) {
		return;
	}

	$license_data = get_option( 'simpay_license_data', false );
	$is_lite      = false === class_exists( '\SimplePay\Pro\SimplePayPro', false );

	// Pro. No license data. Show nothing.
	if (
		false === $is_lite &&
		(
			false === $license_data ||
			( isset( $license_data->error ) && 'missing' === $license_data->error )
		)
	) {
		return;
	}

	$license_id = isset( $license_data->price_id )
		? $license_data->price_id
		: 0;

	// Pro. Professional or higher. Show nothing.
	if ( false === $is_lite && $license_id > 1 ) {
		return;
	}

	$license_types = array(
		0 => _x( 'Lite', 'license type', 'stripe' ),
		1 => _x( 'Personal', 'license type', 'stripe' ),
		2 => _x( 'Plus', 'license type', 'stripe' ),
		3 => _x( 'Professional', 'license type', 'stripe' ),
		4 => _x( 'Ultimate', 'license type', 'stripe' ),
	);

	$license_type = false === $is_lite
		? $license_types[ $license_id ]
		: false;

	switch ( $current_screen->base ){
		case 'post':
			$source = sprintf(
				'%s-payment-form',
				'add' === $current_screen->action ? 'add' : 'edit'
			);
			break;
		case 'edit':
			$source = 'list-payment-forms';
			break;
		case 'simple-pay_page_simpay_settings':
			$source = 'settings';
			break;
		case 'simple-pay_page_simpay_system_status':
			$source = 'system-status';
			break;
		default:
			$source = $current_screen->base;
	}

	$utm_args    = array(
		'utm_source'   => $source,
		'utm_medium'   => sprintf(
			'upgrade-from-%s',
			true === $is_lite ? 'lite' : 'pro-' . $license_id
		),
		'utm_campaign' => 'admin',
		'utm_content'  => 'link-1',
	);

	if ( true === $is_lite ) {
		$upgrade_base_url = 'https://wpsimplepay.com/lite-vs-pro/';
	} else {
		$upgrade_base_url = 'https://wpsimplepay.com/my-account/licenses/';
	}

	$upgrade_url = add_query_arg( $utm_args, $upgrade_base_url );

	require dirname( __FILE__ ) . '/promos/general/license-upgrade.php';
}
add_action( 'admin_notices', __NAMESPACE__ . '\\license_upgrade_promo' );
