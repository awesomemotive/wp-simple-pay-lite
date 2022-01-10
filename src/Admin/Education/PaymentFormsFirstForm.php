<?php
/**
 * Admin: Payment forms "first form guide"
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\Admin\ListTable\PaymentFormsFirstFormListTable;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * PaymentFormsFirstForm class.
 *
 * @since 4.4.0
 */
class PaymentFormsFirstForm implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'views_edit-simple-pay' => 'maybe_show_guide',
		);
	}

	/**
	 * Overrides the list table being loaded if there are no items.
	 *
	 * This is a relatively hacky way of doing this but removes the need
	 * to create a completely custom editing experience for the custom post type.
	 *
	 * `views_edit-simple-pay` is a filter that occurs before the list table is loaded.
	 * We can create a new instance of our custom table instance and if there are
	 * items available simply return the available views (post status filters, etc).
	 *
	 * If there are no items we override the global $wp_list_table with our custom
	 * implementation that replaces the table with onboarding information.
	 *
	 * @since 4.4.0
	 *
	 * @param string[] $views List table views.
	 * @return string[]
	 */
	public function maybe_show_guide( $views ) {
		if ( empty( simpay_get_secret_key() ) ) {
			return $views;
		}

		$list_table = new PaymentFormsFirstFormListTable();

		if ( false === $list_table->has_items() ) {
			global $wp_list_table;

			$wp_list_table = $list_table;

			return array();
		}

		return $views;
	}

}
