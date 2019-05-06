<?php

namespace SimplePay\Core\Admin;

use SimplePay\Core\Admin\Tables\Form_List_Table;
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
	 * The main menu screen hook.
	 *
	 * @access public
	 * @var string
	 */
	public static $main_menu = '';

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

		add_action( 'admin_menu', array( $this, 'add_main_menu_page' ), 0 );

		add_action( 'admin_menu', array( __CLASS__, 'add_menu_items' ) );

		self::$plugin = plugin_basename( SIMPLE_PAY_MAIN_FILE );

		// Links and meta content in plugins page.
		add_filter( 'plugin_action_links_' . self::$plugin, array( __CLASS__, 'plugin_action_links' ), 10, 5 );

		// Save user per_page setting
		add_filter( 'set-screen-option', array( $this, 'save_user_per_page_setting' ), 10, 3 );

		// Fix highlighting on add new submenu item
		add_filter( 'parent_file', array( $this, 'fix_submenu_highlight' ) );

		// Show if test mode is active in admin bar menu
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

		$wp_admin_bar->add_menu( array(
			'id'     => 'simpay-admin-bar-test-mode',
			'href'   => admin_url( 'admin.php?page=simpay_settings&tab=keys' ),
			'parent' => 'top-secondary',
			'title'  => __( 'Simple Pay Test Mode Active', 'stripe' ),
			'meta'   => array( 'class' => 'simpay-admin-bar-test-mode' ),
		) );
	}

	/**
	 * This function changes the menu highlight for our Add New submenu item. We check for the page query arg and make
	 * sure it's ours before modifying it.
	 *
	 * @param $parent_file
	 *
	 * @return mixed
	 */
	public function fix_submenu_highlight( $parent_file ) {

		global $submenu_file;

		if ( isset( $_GET['page'] ) && 'simpay' === $_GET['page'] && isset( $_GET['action'] ) ) {
			$submenu_file = $_GET['page'] . '&action=' . $_GET['action'];
		}

		return $parent_file;
	}

	/**
	 * Add main menu item Simple Pay
	 */
	public function add_main_menu_page() {

		$title = apply_filters( 'simpay_menu_title', __( 'Simple Pay Lite', 'stripe' ) );

		$hook = add_menu_page( $title, $title, 'manage_options', 'simpay', array(
			'SimplePay\Core\Admin\Pages\Main',
			'html',
		), simpay_get_svg_icon_url(), 26 );

		add_action( "load-$hook", array( $this, 'add_options' ) );
	}

	/**
	 * Add screen options to form list table section
	 *
	 * @return string
	 */
	public function add_options() {

		if ( isset( $_REQUEST['action'] ) ) {

			// We only want to return empty if we are on an edit or add new page
			switch ( $_REQUEST['action'] ) {
				case 'edit':
				case 'create':
					return '';
				default:
					break;
			}
		}

		global $form_list_table;

		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Forms', 'stripe' ),
			'default' => 20,
			'option'  => 'forms_per_page',
		);

		add_screen_option( $option, $args );

		// Instantiating this here let's WordPress automatically handle the columns screen options settings
		$form_list_table = new Form_List_Table();
	}

	/**
	 * Save the per_page option entered by the user
	 * Unused params need to be there because this is a call to a WP filter
	 */
	public function save_user_per_page_setting( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Add menu items.
	 *
	 * @since 3.0.0
	 */
	public static function add_menu_items() {

		// All Payment Forms menu link
		add_submenu_page( 'simpay', 'Simple Pay', __( 'Payment Forms', 'stripe' ), 'manage_options', 'simpay', function () {
		} );

		// Add New menu link
		add_submenu_page( 'simpay', 'Simple Pay', __( 'Add New', 'stripe' ), 'manage_options', 'simpay&action=create', function () {
		} );

		/**
		 * Filter the name used for the "Settings" submenu item.
		 *
		 * @since 3.5.0
		 *
		 * @param string
		 */
		$settings_menu_name = apply_filters(
			'simpay_settings_menu_name',
			__( 'Settings', 'stripe' )
		);

		// Settings menu link
		add_submenu_page(
			'simpay',
			sprintf( __( '%s Settings', 'stripe' ), SIMPLE_PAY_PLUGIN_NAME ),
			$settings_menu_name,
			'manage_options',
			'simpay_settings',
			function() {
				$page = new Pages( 'settings' );
				$page->html();
			}
		);

		// System Report (aka System Status) page
		add_submenu_page( 'simpay', __( 'System Report', 'stripe' ), __( 'System Report', 'stripe' ), 'manage_options', 'simpay_system_status', function () {
			$page = new System_Status();
			$page->html();
		} );

		$page_hook = add_submenu_page( 'simpay', __( 'Upgrade to Pro', 'stripe' ), __( 'Upgrade to Pro', 'stripe' ), 'manage_options', 'simpay_upgrade', function() {
			wp_redirect( simpay_ga_url( simpay_get_url( 'upgrade' ), 'plugin-submenu-link',true ), 301 );
			exit;
		} );

		add_action( 'load-' . $page_hook , function() {
			ob_start();
		} );

		do_action( 'simpay_admin_add_menu_items' );
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
