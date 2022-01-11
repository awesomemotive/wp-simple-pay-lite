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
	 * @var \SimplePay\Core\License\LicenseInterface
	 */
	private $license;

	/**
	 * {@inheritdoc}
	 */
	public function set_license( LicenseInterface $license ) {
		$this->license = $license;
	}

}
