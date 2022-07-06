<?php
/**
 * Admin notice: License upgrade top of page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 */

namespace SimplePay\Core\AdminNotice;

/**
 * LicenseUpgradeTopOfPageNotice class.
 *
 * @since 4.4.4
 */
class LicenseUpgradeTopOfPageNotice extends AbstractAdminNotice {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'simpay-license-upgrade';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_view() {
		return SIMPLE_PAY_DIR . 'views/admin-notice-license-upgrade-top-of-page.php'; // @phpstan-ignore-line
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
		return DAY_IN_SECONDS *  90;
	}

	/**
	 * {@inheritdoc}
	 */
	public function should_display() {
		$current_screen = get_current_screen();

		// Not on a WP Simple Pay page, show nothing.
		if (
			false === isset( $current_screen->post_type ) ||
			'simple-pay' !== $current_screen->post_type
		) {
			return false;
		}

		/** @var \SimplePay\Core\License\License $license */
		$license = $this->license;

		// Pro without a license, show nothing. They will be promoted elsewhere.
		if ( false === $this->license->is_lite() && empty( $this->license->get_key() ) ) {
			return false;
		}

		// Grandfathered Plus, Professional or higher license, show nothing.
		if (
			(
				true === $license->is_pro( 'plus', '=' ) &&
				true === $license->is_enhanced_subscriptions_enabled()
			) ||
			true === $license->is_pro( 'professional', '>=' )
		) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * Override the parent method class for backwards compatibility.
	 */
	protected function get_attributes() {
		$attributes = parent::get_attributes();

		$attributes['class'] = 'simpay-notice simpay-admin-notice-top-of-page';

		return $attributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_data() {
		$license_level = $this->license->get_level();
		$upgrade_url   = simpay_pro_upgrade_url(
			'notice-bar',
			sprintf( 'Upgrade from %s', ucfirst( $license_level ) )
		);

		switch ( $license_level ) {
			case 'lite':
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				$message = __(
					'You\'re using WP Simple Pay Lite. To unlock more features consider %1$supgrading to Pro%2$s.',
					'stripe'
				);
				break;
			default:
				$message = sprintf(
					/* translators: License level. */
					__(
						'You\'re using WP Simple Pay with a %s license.',
						'stripe'
					),
					ucfirst( $license_level )
				);

				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				$message .= ' ' . __(
					'To unlock more features consider %1$supgrading now%2$s.',
					'stripe'
				);
		}

		$message = wp_kses(
			sprintf(
				$message,
				'<a href="' . esc_url( $upgrade_url ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			array(
				'a' => array(
					'href'   => true,
					'target' => true,
					'rel'    => true,
				),
			)
		);

		return array(
			'message' => $message,
		);
	}

}
