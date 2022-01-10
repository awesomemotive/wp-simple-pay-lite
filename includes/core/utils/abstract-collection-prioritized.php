<?php
/**
 * Utils: Prioritized collection
 *
 * @package SimplePay\Core\Utils
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the construct for building a prioritized item registry.
 *
 * Items in the registry must have a class property or array key `priority`.
 *
 * @since 4.0.0
 * @abstract
 */
abstract class Collection_Prioritized extends Collection {

	/**
	 * Retrieves registered items, prioritized.
	 *
	 * @since 4.0.0
	 *
	 * @return array The list of registered items, prioritized.
	 */
	public function get_items() {
		$items = parent::get_items();

		uasort(
			$items,
			function( $a, $b ) {
				$a = (array) $a;
				$b = (array) $b;

				if ( ! isset( $a['priority'] ) ) {
					$a['priority'] = 10;
				}

				if ( ! isset( $b['priority'] ) ) {
					$b['priority'] = 10;
				}

				if ( floatval( $a['priority'] ) === floatval( $b['priority'] ) ) {
					return 0;
				}

				return ( floatval( $a['priority'] ) < floatval( $b['priority'] ) )
					? -1
					: 1;
			}
		);

		return $items;
	}

}
