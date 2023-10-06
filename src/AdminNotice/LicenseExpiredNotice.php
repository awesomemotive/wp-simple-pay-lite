<?php
/**
 * Admin notice: License expired
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\AdminNotice;

/**
 * LicenseExpiredNotice class.
 *
 * @since 4.4.6
 */
class LicenseExpiredNotice extends AbstractAdminNotice {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'expired-license';
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
		return false;
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
		// No license.
		if ( empty( $this->license->get_key() ) ) {
			return false;
		}

		return 'expired' === $this->license->get_status();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_data() {
		$renew_url = add_query_arg(
			array(
				'edd_license_key' => $this->license->get_key(),
				'discount'        => 'SAVE50',
			),
			sprintf( '%s/checkout', untrailingslashit( SIMPLE_PAY_STORE_URL ) ) // @phpstan-ignore-line
		);

		$renew_url = simpay_ga_url( $renew_url, 'admin-notice-expired-license' );

		$learn_more_url = simpay_docs_link(
			'Learn More',
			'what-happens-if-my-license-expires',
			'admin-notice-expired-license',
			true
		);

		/** @var string $expiration */
		$expiration = $this->license->get_expiration();

		/** @var string $format */
		$format = get_option( 'date_format', 'Y-m-d' );

		return array(
			'renew_url'          => $renew_url,
			'learn_more_url'     => $learn_more_url,
			'is_in_grace_period' => $this->license->is_in_grace_period(),
			'grace_period_ends'  => date( // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$format,
				strtotime( $expiration ) + ( DAY_IN_SECONDS * 14 )
			),
		);
	}

}
