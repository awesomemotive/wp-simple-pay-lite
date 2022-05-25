<?php
/**
 * Admin: Education plugin "Coupons" menu item
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * PluginCouponMenuItem class.
 *
 * @since 4.4.0
 */
class PluginCouponMenuItem extends AbstractProductEducation implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( false === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'admin_menu' => array( 'menu_item', 0 ),
		);
	}

	/**
	 * Registers the "Coupons" menu item.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function menu_item() {
		add_submenu_page(
			'edit.php?post_type=simple-pay',
			__( 'Coupons', 'stripe' ),
			__( 'Coupons', 'stripe' ),
			'manage_options',
			'simpay_coupons',
			array( $this, 'upsell' )
		);
	}

	/**
	 * Outputs the "Taxes" feature upsell.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function upsell() {
		// Lightweight, accessible and responsive lightbox.
		wp_enqueue_style(
			'simpay-lity',
			SIMPLE_PAY_INC_URL . 'core/assets/css/vendor/lity/lity.min.css', // @phpstan-ignore-line
			array(),
			'3.0.0'
		);

		wp_enqueue_script(
			'simpay-lity',
			SIMPLE_PAY_INC_URL . 'core/assets/js/vendor/lity.min.js', // @phpstan-ignore-line
			array( 'jquery' ),
			'3.0.0',
			true
		);

		$utm_medium            = 'coupons';
		$utm_content           = 'Offer Coupon Codes to Customers';
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_text          = $this->get_upgrade_button_text();
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-plugin-coupons.php'; // @phpstan-ignore-line
	}

}
