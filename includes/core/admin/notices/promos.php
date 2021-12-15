<?php
/**
 * Notices: Promos
 *
 * Additional promotional notices.
 *
 * @package SimplePay\Core\Admin\Notices\Promos
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

namespace SimplePay\Core\Admin\Notices\Promos;

use SimplePay\Core\Admin\Notice_Manager;

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

	$upgrade_url = simpay_pro_upgrade_url( 'notice-bar' );

	require dirname( __FILE__ ) . '/promos/general/license-upgrade.php';
}
add_action( 'admin_notices', __NAMESPACE__ . '\\license_upgrade_promo' );
