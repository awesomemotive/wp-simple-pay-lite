<?php
/**
 * License: License aware trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\License;

/**
 * LicenseAwareInterface trait.
 *
 * @since 4.4.1
 */
trait LicenseAwareTrait {

	/**
	 * License.
	 *
	 * @since 4.4.1
	 * @since 4.4.4 Set visibility to protected.
	 * @var \SimplePay\Core\License\License
	 */
	protected $license;

	/**
	 * {@inheritdoc}
	 */
	public function set_license( License $license ) {
		$this->license = $license;
	}

}
