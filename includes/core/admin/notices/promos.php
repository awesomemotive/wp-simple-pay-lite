<?php
/**
 * Notices: Promos
 *
 * Additional promotional notices.
 *
 * @package SimplePay\Core\Admin\Notices\Promos
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace SimplePay\Core\Admin\Notices\Promos;

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
	/** This filter is documented in includes/core/admin/pages.php */
	$show = apply_filters( 'simpay_settings_sidebar_template', SIMPLE_PAY_INC . 'core/promos/general/sidebar.php' );

	if ( ! empty( $show ) ) {
		include( dirname( __FILE__ ) . '/promos/general/promo-under-box-header.php' );
		include( dirname( __FILE__ ) . '/promos/general/generic-tab-promo.php' );
		include( dirname( __FILE__ ) . '/promos/general/promo-under-box-footer.php' );
	}
}
add_action( 'simpay_form_settings_meta_payment_options_panel', __NAMESPACE__ . '\\tab_view' );
add_action( 'simpay_admin_after_general_options', __NAMESPACE__ . '\\tab_view' );
add_action( 'simpay_form_settings_meta_form_display_panel', __NAMESPACE__ . '\\tab_view' );
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
