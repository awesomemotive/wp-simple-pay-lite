<?php
/**
 * Dashboard widget: Product education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\DashboardWidget;

use SimplePay\Core\License\LicenseInterface;

/**
 * ProductEducationDashboardWidget class.
 *
 * @since 4.4.0
 */
class ProductEducationDashboardWidget extends AbstractDashboardWidget {

	/**
	 * License.
	 *
	 * @since 4.4.0
	 * @var \SimplePay\Core\License\LicenseInterface
	 */
	private $license;

	/**
	 * ProductEducationDashboardWidget.
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\License\LicenseInterface $license Plugin license.
	 */
	public function __construct( LicenseInterface $license ) {
		$this->license = $license;
	}

	/**
	 * {@inheritdoc}
	 */
	public function can_register() {
		return (
			true === $this->should_display_stripe_connect() ||
			true === $this->should_display_first_form() ||
			true === $this->should_display_lite_upgrade()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'simpay-product-education';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name() {
		return 'WP Simple Pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_args() {
		return array();
	}

	/**
	 * Determines if the widget should display the Stripe Connect view.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function should_display_stripe_connect() {
		return empty( simpay_get_secret_key() );
	}

	/**
	 * Determines if the widget should display the first form view.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function should_display_first_form() {
		$forms = array_map( 'intval', (array) wp_count_posts( 'simple-pay' ) );

		return 0 === $forms['publish'];
	}

	/**
	 * Determines if the widget should display the Lite upgrade view.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function should_display_lite_upgrade() {
		return $this->license->is_lite();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @todo use a ViewLoader
	 */
	public function render() {
		// No Stripe connection.
		if ( true === $this->should_display_stripe_connect() ) {
			include_once SIMPLE_PAY_DIR . 'views/admin-dashboard-widget-stripe-connect.php'; // @phpstan-ignore-line

			return;
		}

		// No payment forms.
		if ( true === $this->should_display_first_form() ) {
			include_once SIMPLE_PAY_DIR . 'views/admin-dashboard-widget-first-form.php'; // @phpstan-ignore-line

			return;
		}

		// Lite upgrade.
		if ( true === $this->should_display_lite_upgrade() ) {
			include_once SIMPLE_PAY_DIR . 'views/admin-dashboard-widget-lite-upgrade.php'; // @phpstan-ignore-line

			return;
		}
	}

}
