<?php
/**
 * Admin: Payment forms Stripe Connect education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\Admin\ListTable\PaymentFormsStripeConnectListTable;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * PaymentFormsStripeConnect class.
 *
 * @since 4.4.0
 */
class PaymentFormsStripeConnect implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_init'            => 'maybe_redirect_back',
			'views_edit-simple-pay' => 'maybe_setup_stripe',
		);
	}

	/**
	 * Redirects users back to the main Payment Forms list page if there is no
	 * API secret key.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function maybe_redirect_back() {
		global $pagenow;

		if (
			'post-new.php' === $pagenow &&
			isset( $_GET['post_type'] ) &&
			'simple-pay' === sanitize_key( $_GET['post_type'] ) &&
			empty( simpay_get_secret_key() )
		) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => 'simple-pay',
					),
					admin_url( 'edit.php' )
				)
			);
		}
	}

	/**
	 * Overrides the list table being loaded if there is no Stripe connection.
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
	public function maybe_setup_stripe( $views ) {
		if ( ! empty( simpay_get_secret_key() ) ) {
			return $views;
		}

		$list_table = new PaymentFormsStripeConnectListTable();

		global $wp_list_table;

		$wp_list_table = $list_table;

		return array();
	}

}
