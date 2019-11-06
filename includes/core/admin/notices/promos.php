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
	$show = apply_filters( 'simpay_settings_sidebar_template', SIMPLE_PAY_INC . 'core/promos/views/sidebar.php' );

	if ( ! empty( $show ) ) {
		include( dirname( __FILE__ ) . '/promos/views/promo-under-box-header.php' );
		include( dirname( __FILE__ ) . '/promos/views/generic-tab-promo.php' );
		include( dirname( __FILE__ ) . '/promos/views/promo-under-box-footer.php' );
	}
}
add_action( 'simpay_form_settings_meta_payment_options_panel', __NAMESPACE__ . '\\tab_view' );
add_action( 'simpay_admin_after_general_options', __NAMESPACE__ . '\\tab_view' );
add_action( 'simpay_form_settings_meta_form_display_panel', __NAMESPACE__ . '\\tab_view' );
add_action( 'simpay_admin_after_stripe_checkout', __NAMESPACE__ . '\\tab_view' );
