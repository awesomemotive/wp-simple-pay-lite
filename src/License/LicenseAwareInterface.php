<?php
/**
 * License: License aware interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\License;

/**
 * LicenseAwareInterface interface.
 *
 * @since 4.4.1
 */
interface LicenseAwareInterface {

	/**
	 * Sets the license.
	 *
	 * @since 4.1.1
	 *
	 * @param \SimplePay\Core\License\License $license License.
	 * @return void
	 */
	public function set_license( License $license );

}
