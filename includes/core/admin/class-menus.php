<?php
/**
 * Admin menu
 *
 * @package SimplePay\Core\Admin
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Admin;

use SimplePay\Core\Settings;
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

		add_filter( 'admin_footer_text', array( $this, 'add_footer_text' ) );
	}

	/**
	 * Outputs "please rate" text.
	 *
	 * @since 3.0.0
	 *
	 * @param string $footer_text Footer text.
	 * @return string
	 */
	public function add_footer_text( $footer_text ) {

		if ( simpay_is_admin_screen() ) {
			$footer_text = sprintf(
				/* Translators: 1. The plugin name */
				__( 'If you like <strong>%1$s</strong> please leave us a %2$s rating. A huge thanks in advance!', 'stripe' ),
				SIMPLE_PAY_PLUGIN_NAME,
				'<a href="https://wordpress.org/support/plugin/stripe/reviews?rate=5#new-post" rel="noopener noreferrer" target="_blank" class="simpay-rating-link" data-rated="' .
				esc_attr__( 'Thanks :)', 'stripe' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}


	/**
	 * Display admin bar test mode active.
	 *
	 * @return bool
	 */
	public function admin_bar_menu() {
		global $wp_admin_bar;

		if ( ! simpay_is_test_mode() ) {
			return false;
		}

		$stripe_test_mode_url = Settings\get_url( array(
			'section'    => 'stripe',
			'subsection' => 'account',
			'setting'    => 'test_mode',
		) );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'simpay-admin-bar-test-mode',
				'href'   => $stripe_test_mode_url,
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
	 * @param array  $action_links Action links.
	 * @param string $file Plugin file.
	 * @return array
	 */
	public static function plugin_action_links( $action_links, $file ) {
		if ( self::$plugin !== $file ) {
			return $action_links;
		}

		$links = array();

		// Upgrade to Pro.
		if ( ! class_exists( 'SimplePay\Pro\SimplePayPro', false ) ) {
			$links[] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="simpay-upgrade-link">%s</a>',
				simpay_ga_url( 'https://wpsimplepay.com/lite-vs-pro/', 'admin-menu' ),
				esc_html__( 'Upgrade to Pro', 'stripe' )
			);
		}

		// Settings.
		$settings_url = Settings\get_url( array(
			'section' => 'stripe',
		) );

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $settings_url ),
			esc_html__( 'Settings', 'stripe' )
		);

		if ( class_exists( 'SimplePay\Pro\SimplePayPro', false ) ) {

			// Documentation.
			$documentation_url = simpay_ga_url(
				'https://docs.wpsimplepay.com/',
				'plugin-listing-link',
				false
			);

			$links[] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $documentation_url ),
				esc_html__( 'Documentation', 'stripe' )
			);

			// Support.
			$support_url = simpay_ga_url(
				'https://wpsimplepay.com/support',
				'plugin-listing-link',
				false
			);

			$links[] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $support_url ),
				esc_html__( 'Support', 'stripe' )
			);
		}

		return array_merge( $links, $action_links );
	}
}
