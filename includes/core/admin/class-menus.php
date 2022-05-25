<?php
/**
 * Admin menu
 *
 * @package SimplePay\Core\Admin
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
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
		if ( ! simpay_is_admin_screen() ) {
			return $footer_text;
		}

		return sprintf(
			/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
			__( 'Please rate %1$sWP Simple Pay%2$s %3$s★★★★★%4$s on %3$sWordPress.org%4$s to help us spread the word. Thank you from the WP Simple Pay team!', 'stripe' ),
			'<strong>',
			'</strong>',
			'<a href="https://wordpress.org/support/plugin/stripe/reviews/?filter=5#new-post" rel="noopener noreferrer" target="_blank">',
			'</a>'
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
				simpay_pro_upgrade_url( 'admin-menu', 'Upgrade to Pro' ),
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
				'Documentation'
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
				'Support'
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
