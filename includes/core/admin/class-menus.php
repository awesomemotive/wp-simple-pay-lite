<?php
/**
 * Admin menu
 *
 * @package SimplePay\Core\Admin
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Admin;

use SimplePay\Core\Admin\Pages\System_Status;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Menus.
 *
 * Handles the plugin admin dashboard menus.
 *
 * @since 3.0.0
 */
class Menus {

	/**
	 * Plugin basename.
	 *
	 * @access private
	 * @var string
	 */
	private static $plugin = '';

	/**
	 * Set properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		self::$plugin = plugin_basename( SIMPLE_PAY_MAIN_FILE );

		// Links and meta content in plugins page.
		add_filter( 'plugin_action_links_' . self::$plugin, array( __CLASS__, 'plugin_action_links' ), 10, 5 );

		// Show if test mode is active in admin bar menu.
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ) );
	}


	/**
	 * Display admin bar test mode active
	 *
	 * @return bool
	 */
	public function admin_bar_menu() {
		global $wp_admin_bar;

		if ( ! simpay_is_test_mode() ) {
			return false;
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'simpay-admin-bar-test-mode',
				'href'   => admin_url( 'admin.php?page=simpay_settings&tab=keys#simpay-settings-keys-mode-test-mode' ),
				'parent' => 'top-secondary',
				'title'  => sprintf(
					/* translators: "Test Mode" badge. */
					__( 'Simple Pay %s', 'stripe' ),
					'<span class="simpay-test-mode-badge">' . __( 'Test Mode', 'stripe' ) . '</span>'
				),
				'meta'   => array( 'class' => 'simpay-admin-bar-test-mode' ),
			)
		);
	}

	/**
	 * Action links in plugins page.
	 *
	 * @since  3.0.0
	 *
	 * @param  array  $action_links
	 * @param  string $file
	 *
	 * @return array
	 */
	public static function plugin_action_links( $action_links, $file ) {

		if ( self::$plugin == $file ) {

			$links             = array();
			$links['settings'] = '<a href="' . admin_url( 'admin.php?page=simpay_settings' ) . '">' . esc_html__( 'Settings', 'stripe' ) . '</a>';
			$links['forms']    = '<a href="' . admin_url( 'admin.php?page=simpay' ) . '">' . esc_html__( 'Payment Forms', 'stripe' ) . '</a>';

			if ( ! defined( 'SIMPLE_PAY_ITEM_NAME' ) ) {
				$upgrade_link = '<a href="' . simpay_ga_url( simpay_get_url( 'upgrade' ), 'plugin-listing-link', false ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Upgrade to Pro', 'stripe' ) . '</a>';

				array_push( $action_links, $upgrade_link );
			}

			return apply_filters( 'simpay_plugin_action_links', array_merge( $links, $action_links ) );
		}

		return $action_links;
	}
}
