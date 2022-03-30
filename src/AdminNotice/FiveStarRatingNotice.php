<?php
/**
 * Admin notice: Five star rating
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\AdminNotice;

/**
 * FiveStarRatingNotice class.
 *
 * @since 4.4.1
 */
class FiveStarRatingNotice extends AbstractAdminNotice {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'five-star-review';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return 'info';
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
		// Current user cannot manage options, show nothing.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// @todo Add on installation.
		$installed = get_option( 'simpay_installed', '' );

		if ( empty( $installed ) ) {
			$installed = time();

			update_option( 'simpay_installed', $installed );
		}

		/** @var string $installed */

		if ( time() - (int) $installed < ( DAY_IN_SECONDS * 14 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_data() {
		return array(
			'feedback_url' => simpay_ga_url(
				'https://wpsimplepay.com/plugin-feedback/',
				'admin-notice',
				'Give Feedback'
			),
		);
	}

}
