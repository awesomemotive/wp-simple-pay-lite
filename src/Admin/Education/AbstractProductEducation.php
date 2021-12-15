<?php
/**
 * Admin: Product education abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\License\LicenseInterface;

/**
 * AbstractProductEducation abstract.
 *
 * @since 4.4.0
 */
abstract class AbstractProductEducation implements ProductEducationInterface {

	/**
	 * License.
	 *
	 * @since 4.4.0
	 * @var \SimplePay\Core\License\LicenseInterface
	 */
	protected $license;

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
	public function get_upgrade_button_url( $utm_medium ) {
		return simpay_pro_upgrade_url( $utm_medium );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_upgrade_button_text() {
		if ( true === $this->license->is_lite() ) {
			$text = __( 'Upgrade to WP Simple Pay Pro', 'simple-pay' );
		} else {
			$text = __( 'Upgrade Now', 'simple-pay' );
		}

		return $text;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_upgrade_button_subtext() {
		return __( 'Special Upgrade Offer - Save 50%', 'simple-pay' );
	}

}
