<?php
/**
 * Admin notice: Stripe API error
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\AdminNotice;

use SimplePay\Core\Settings;

/**
 * StripeApiErrorNotice class.
 *
 * @since 4.4.6
 */
class StripeApiErrorNotice extends AbstractAdminNotice {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'stripe-api-error';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return 'error';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_dismissible() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_dismissal_length() {
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function should_display() {
		return isset( $_GET['simpay-stripe-api-error'] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_data() {
		$error = isset( $_GET['simpay-stripe-api-error'] )
			? $_GET['simpay-stripe-api-error'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: '';

		return array(
			'error' => $error,
		);
	}

}
