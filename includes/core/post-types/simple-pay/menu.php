<?php
/**
 * Simple Pay: Menu
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Menu
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Menu;

use SimplePay\Core\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds extra menu items under the `simple-pay` post type.
 *
 * @since 3.8.0
 */
function add_items() {
	$settings_menu_name = __( 'Settings', 'stripe' );

	/**
	 * Filters the name used for the "Settings" submenu item.
	 *
	 * @since 3.5.0
	 *
	 * @param string
	 */
	$settings_menu_name = apply_filters( 'simpay_settings_menu_name', $settings_menu_name );

	// Settings.
	add_submenu_page(
		'edit.php?post_type=simple-pay',
		sprintf(
			/* translators: %s Plugin name. */
			__( '%s Settings', 'stripe' ),
			SIMPLE_PAY_PLUGIN_NAME
		),
		$settings_menu_name,
		'manage_options',
		'simpay_settings',
		'SimplePay\\Core\\Settings\\page'
	);

	// System Report.
	add_submenu_page(
		'edit.php?post_type=simple-pay',
		__( 'System Report', 'stripe' ),
		__( 'System Report', 'stripe' ),
		'manage_options',
		'simpay_system_status',
		function () {
			$page = new Pages\System_Status();
			$page->html();
		}
	);

	// Upgrade.
	global $submenu;

	$submenu['edit.php?post_type=simple-pay'][99] = array(
		__( 'Upgrade to Pro', 'stripe' ),
		'manage_options',
		simpay_ga_url( simpay_get_url( 'upgrade' ), 'plugin-submenu-link', true ),
	);

	/**
	 * Allows further menu items to be added.
	 *
	 * @todo Deprecate. Use core actions.
	 *
	 * @since 3.0.0
	 */
	do_action( 'simpay_admin_add_menu_items' );
}
add_action( 'admin_menu', __NAMESPACE__ . '\\add_items' );
