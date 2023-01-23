<?php
/**
 * Admin: Admin page subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin;

use SimplePay\Core\AdminPage\AdminPageInterface;
use SimplePay\Core\AdminPage\AdminPrimaryPageInterface;
use SimplePay\Core\AdminPage\AdminSecondaryPageInterface;
use SimplePay\Core\EventManagement\SubscriberInterface;
use WP_Screen;

/**
 * AdminPageSubscriber class.
 *
 * @since 4.4.0
 */
class AdminPageSubscriber implements SubscriberInterface {

	/**
	 * Admin pages.
	 *
	 * @since 4.4.0
	 * @var \SimplePay\Core\AdminPage\AdminPageInterface[] $pages Admin pages.
	 */
	private $pages;

	/**
	 * AdminPageSubscriber.
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\AdminPage\AdminPageInterface[] $pages Admin pages.
	 */
	public function __construct( $pages ) {
		$this->pages = array();

		foreach ( $pages as $page ) {
			$this->add_admin_page( $page );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_menu' => array( 'add_menu_pages', 20 ),
			'admin_head' => 'set_block_editor_body_class',
			'admin_init' => 'maybe_redirect_pages',
		);
	}

	/**
	 * Adds an admin page to be registered.
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\AdminPage\AdminPageInterface $page Admin page.
	 * @return void
	 */
	private function add_admin_page( AdminPageInterface $page ) {
		$this->pages[] = $page;
	}

	/**
	 * Adds registered admin pages to the menu.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function add_menu_pages() {
		foreach ( $this->pages as $page ) {
			if ( $page instanceof AdminPrimaryPageInterface ) {
				add_menu_page(
					$page->get_page_title(),
					$page->get_menu_title(),
					$page->get_capability_requirement(),
					$page->get_page_slug(),
					array( $page, 'render' ),
					$page->get_icon(),
					$page->get_position()
				);
			} elseif ( $page instanceof AdminSecondaryPageInterface ) {
				add_submenu_page(
					$page->get_parent_slug(),
					$page->get_page_title(),
					$page->get_menu_title(),
					$page->get_capability_requirement(),
					$page->get_page_slug(),
					array( $page, 'render' ),
					$page->get_position()
				);
			}
		}
	}

	/**
	 * Sets the current screen's body class based on block editor configuration.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function set_block_editor_body_class() {
		$current_screen = get_current_screen();

		if ( ! $current_screen instanceof WP_Screen ) {
			return;
		}

		foreach ( $this->pages as $page ) {
			if ( $current_screen->base !== $page->get_screen_base_name() ) {
				continue;
			}

			$current_screen->is_block_editor( $page->is_block_editor() );
		}
	}

	/**
	 * Redirect some admin pages to the correct URL base.
	 *
	 * When an Admin Page is created with a priority that puts it above the
	 * "Payment Forms" post type list type page WordPress uses a different
	 * URL structure. i.e
	 *
	 * admin.php?page=simpay-activity-reports
	 *   instead of:
	 * edit.php?post_type=simple-pay&page=simpay-activity-reports
	 *
	 * I'm not sure why.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function maybe_redirect_pages() {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		if ( isset( $_GET['post_type'] ) ) {
			return;
		}

		$page = sanitize_text_field( $_GET['page'] );

		// Possible page slugs that need to be fixed.
		$candidates = array(
			'simpay-activity-reports',
		);

		if ( ! in_array( $page, $candidates, true ) ) {
			return;
		}

		$redirect = add_query_arg(
			array(
				'post_type' => 'simple-pay',
				'page'      => $page
			),
			admin_url( 'edit.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

}
