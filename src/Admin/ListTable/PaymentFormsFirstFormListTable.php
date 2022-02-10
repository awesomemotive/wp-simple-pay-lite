<?php
/**
 * List Table: Payment forms first form
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\ListTable;

use WP_Posts_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
}

/**
 * PaymentFormsFirstFormListTable class.
 *
 * @since 4.4.0
 */
class PaymentFormsFirstFormListTable extends WP_Posts_List_Table {

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function display() {
		if ( $this->has_items() || isset( $_GET['s'] ) ) {
			parent::display();
		} else {
			// @todo use a ViewLoader
			include_once SIMPLE_PAY_DIR . '/views/admin-payment-forms-first-form.php'; // @phpstan-ignore-line
		}
	}

}
