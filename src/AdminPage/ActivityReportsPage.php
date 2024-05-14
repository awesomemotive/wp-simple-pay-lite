<?php
/**
 * Admin: "Activity & Reports" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\AdminPage;

use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * ActivityReportsPage class.
 *
 * @since 4.6.7
 */
class ActivityReportsPage extends AbstractAdminPage implements AdminSecondaryPageInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 0;
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
		return __( 'Activity & Reports', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'Activity & Reports', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'simpay-activity-reports';
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
		$asset = SIMPLE_PAY_INC . 'core/assets/js/dist/simpay-admin-page-activity-reports.asset.php'; // @phpstan-ignore-line

		if ( file_exists( $asset ) ) {
			$asset_data = include_once $asset;

			wp_enqueue_script(
				'simpay-admin-page-activity-reports',
				SIMPLE_PAY_INC_URL . 'core/assets/js/dist/simpay-admin-page-activity-reports.js', // @phpstan-ignore-line
				$asset_data['dependencies'],
				$asset_data['version'],
				true
			);

			$user_id = get_current_user_id();

			$default_range = get_user_meta(
				$user_id,
				'simpay_activity_reports_range',
				true
			);

			if ( ! isset( $_GET['currency'] ) ) {
				$default_currency = get_user_meta(
					$user_id,
					'simpay_activity_reports_currency',
					true
				);
			} else {
				$default_currency = sanitize_text_field( $_GET['currency'] );
			}

			/** @var string $default_currency */

			wp_localize_script(
				'simpay-admin-page-activity-reports',
				'simpayAdminPageActivityReports',
				array(
					'user_id'          => $user_id,
					'license'          => $this->license->to_array(),
					'currencies'       => array_keys( simpay_get_currencies() ),
					'default_currency' => strtolower( $default_currency ),
					'default_range'    => $default_range,
					'links'            => array(
						'all_payments' => sprintf(
							'https://dashboard.stripe.com/%spayments',
							simpay_is_test_mode() ? 'test/' : ''
						),
					),
				)
			);

			wp_set_script_translations(
				'simpay-admin-page-activity-reports',
				'stripe',
				SIMPLE_PAY_DIR . '/languages' // @phpstan-ignore-line
			);

			wp_enqueue_style(
				'simpay-admin-page-activity-reports',
				SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-page-activity-reports.min.css', // @phpstan-ignore-line
				array(
					'wp-components',
				),
				$asset_data['version']
			);
		}

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-page-activity-reports.php'; // @phpstan-ignore-line
	}

}
