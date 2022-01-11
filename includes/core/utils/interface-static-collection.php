<?php
/**
 * Static_Collection: interface
 *
 * @package SimplePay\Core\Utils
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the contract for a static (singleton) registry object.
 *
 * @since 3.8.0
 */
interface Static_Collection {

	/**
	 * Retrieves the one true registry instance.
	 *
	 * @since 3.8.0
	 *
	 * @return \SimplePay\Core\Utils\Static_Registry Registry instance.
	 */
	public static function instance();

}
