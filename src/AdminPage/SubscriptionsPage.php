<?php
/**
 * Admin: "Subscriptions" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\AdminPage;

use SimplePay\Core\Admin\ListTable\SubscriptionsListTable;
use SimplePay\Core\Subscription\Subscription;
use SimplePay\Core\Utils\AccountScope;

/**
 * SubscriptionsPage class.
 *
 * @since 4.17.4
 */
class SubscriptionsPage extends AbstractAdminPage implements AdminSecondaryPageInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 2;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_capability_requirement() {
		return 'manage_options';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_title() {
		return __( 'Subscriptions', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'Subscriptions', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'simpay-subscriptions';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_parent_slug() {
		return 'edit.php?post_type=simple-pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_block_editor() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
		// The list and detail views reuse the Transactions stylesheet -- the
		// `.simpay-txn-*` classes are generic to both pages.
		wp_enqueue_style(
			'simpay-admin-page-transactions',
			SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-page-transactions.css',
			array(),
			SIMPLE_PAY_VERSION
		);

		// Detail view.
		if ( ! empty( $_GET['subscription_id'] ) ) {
			$this->render_detail_view();
			return;
		}

		// List view.
		$this->render_list_view();
	}

	/**
	 * Renders the list view.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	private function render_list_view() {
		$list_table = new SubscriptionsListTable();
		$list_table->prepare_items();

		// @todo use a ViewLoader
		// `include`, not `include_once`: a template renders wherever it is
		// asked to, and rendering one twice in a process must not silently
		// produce nothing the second time.
		include SIMPLE_PAY_DIR . '/views/admin-page-subscriptions.php';
	}

	/**
	 * Renders the detail view for a single subscription.
	 *
	 * Scoped to the connected Stripe account, like every other read this page
	 * performs (#3533). Without it a subscription belonging to a previously
	 * connected account stayed reachable by ID, through the one query where
	 * the ID was the whole of the lookup.
	 *
	 * The payment mode is deliberately not part of the scope. A payment form
	 * can override the global mode, so both modes' rows coexist and this view
	 * is meant to open either one -- the back link derives the list's mode
	 * from the row itself rather than from the request.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	private function render_detail_view() {
		global $wpdb;

		$subscription_id = absint( $_GET['subscription_id'] );
		$account_scope   = AccountScope::get_where_fragment();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $account_scope is itself a prepared fragment; only the table name is interpolated.
				"SELECT * FROM {$wpdb->prefix}wpsp_subscriptions WHERE id = %d{$account_scope}",
				$subscription_id
			)
		);

		if ( ! $row ) {
			wp_die( esc_html__( 'Subscription not found.', 'stripe' ) );
		}

		$subscription = new Subscription( (array) $row );

		// Powers the detail view's copy-to-clipboard buttons.
		wp_enqueue_script(
			'simpay-admin-transactions',
			SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-admin-transactions.js',
			array(),
			SIMPLE_PAY_VERSION,
			true
		);

		// @todo use a ViewLoader
		include SIMPLE_PAY_DIR . '/views/admin-page-subscription-detail.php';
	}
}
