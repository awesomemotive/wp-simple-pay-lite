<?php
/**
 * Site Health: Menu item management
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.7
 */

namespace SimplePay\Core\Admin\SiteHealth;

use SimplePay\Core\EventManagement\SubscriberInterface;
use WP_Screen;

/**
 * SiteHealthMenuItem class.
 *
 * @since 4.4.7
 */
class SiteHealthMenuItem implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_init'                  => 'redirect_menu_item',
			'parent_file'                 => 'set_active_menu_item',
			'submenu_file'                => 'set_active_submenu_item',
			'site_health_navigation_tabs' => 'maybe_rename_tab',
		);
	}

	/**
	 * Redirects the menu item (based on the parent custom post type) to the
	 * actual "Site Health" page, limited to WP Simple Pay information.
	 *
	 * @since 4.4.7
	 *
	 * @return void
	 */
	public function redirect_menu_item() {
		if ( ! isset(
			$_GET['post_type'],
			$_GET['page']
		) ) {
			return;
		}

		$post_type = sanitize_text_field( $_GET['post_type'] );
		$page      = sanitize_text_field( $_GET['page'] );

		if ( 'simple-pay' !== $post_type || 'system-report' !== $page ) {
			return;
		}

		$redirect_url = add_query_arg(
			array(
				'tab'    => 'debug',
				'simpay' => true,
			),
			admin_url( 'site-health.php' )
		);

		wp_safe_redirect( esc_url_raw( $redirect_url ) );
		exit;
	}

	/**
	 * Sets the active admin menu item when in Site Health, but only view
	 * WP Simple Pay debug informaton.
	 *
	 * @since 4.4.7
	 *
	 * @param string $file Parent file.
	 * @return string
	 */
	public function set_active_menu_item( $file ) {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return $file;
		}

		if ( 'site-health' === $screen->base && isset( $_GET['simpay'] ) ) {
			$file = 'edit.php?post_type=simple-pay';
		}

		return $file;
	}

	/**
	 * Sets the active admin submenu item when in Site Health, but only view
	 * WP Simple Pay debug informaton.
	 *
	 * @since 4.4.7
	 *
	 * @param string $file Submenu file.
	 * @return string
	 */
	public function set_active_submenu_item( $file ) {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return $file;
		}

		if ( 'site-health' === $screen->base && isset( $_GET['simpay'] ) ) {
			$file = 'system-report';
		}

		return $file;
	}

	/**
	 * Renames the "Info" tab label to "WP Simple Pay" if we are only viewing
	 * WP Simple Pay debug information.
	 *
	 * @since 4.4.7
	 *
	 * @param array<string, string> $tabs Site health navigation tabs.
	 * @return array<string, string>
	 */
	public function maybe_rename_tab( $tabs ) {
		if ( ! isset( $_GET['simpay'] ) ) {
			return $tabs;
		}

		$tabs['debug'] = __( 'WP Simple Pay', 'stripe' );

		return $tabs;
	}

}
