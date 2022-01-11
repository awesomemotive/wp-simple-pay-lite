<?php
/**
 * Utils: Collection
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
 * Defines the construct for building an item registry.
 *
 * @since 3.8.0
 * @abstract
 */
abstract class Collection extends \ArrayObject {

	/**
	 * Array of registry items.
	 *
	 * @since 3.8.0
	 * @var array
	 */
	private $items = array();

	/**
	 * Adds an item to the registry.
	 *
	 * If an existing item already exists it will be overwritten.
	 *
	 * @since 3.8.0
	 *
	 * @param mixed $item_id Item ID.
	 * @param mixed $value Item value.
	 * @return true Always true.
	 */
	public function add_item( $item_id, $value ) {
		$this->items[ $item_id ] = $value;

		return true;
	}

	/**
	 * Removes an item from the registry by ID.
	 *
	 * @since 3.8.0
	 *
	 * @param string $item_id Item ID.
	 */
	public function remove_item( $item_id ) {
		unset( $this->items[ $item_id ] );
	}

	/**
	 * Retrieves an item and its associated attributes.
	 *
	 * @since 3.8.0
	 *
	 * @param mixed $item_id Item ID.
	 * @return mixed|false Item attributes if registered, otherwise false.
	 */
	public function get_item( $item_id ) {
		if ( isset( $this->items[ $item_id ] ) ) {
			return $this->items[ $item_id ];
		}

		return false;
	}

	/**
	 * Determines whether an item exists.
	 *
	 * @since 3.8.0
	 *
	 * @param string $item_id Item ID.
	 * @return bool True if the item exists, false on failure.
	 */
	public function has_item( $item_id ) {
		return false !== $this->get_item( $item_id );
	}

	/**
	 * Retrieves registered items.
	 *
	 * @since 3.8.0
	 *
	 * @return array The list of registered items.
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Only intended for use by tests.
	 *
	 * @since 3.8.0
	 */
	public function _reset_items() {
		if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
			_doing_it_wrong( 'This method is only intended for use in phpunit tests', '3.8.0' );
		} else {
			$this->items = array();
		}
	}

	/**
	 * Determines whether an item exists.
	 *
	 * Defined only for compatibility with ArrayAccess, use has_item() directly.
	 *
	 * @since 3.8.0
	 *
	 * @param string $offset Item ID.
	 * @return bool True if the item exists, false on failure.
	 */
	public function offsetExists( $offset ) {
		return $this->has_item( $offset );
	}

	/**
	 * Retrieves an item by its ID.
	 *
	 * Defined only for compatibility with ArrayAccess, use get_item() directly.
	 *
	 * @since 3.8.0
	 *
	 * @param mixed $offset Item ID.
	 * @return mixed|false Item attributes if registered, otherwise false.
	 */
	public function offsetGet( $offset ) {
		return $this->get_item( $offset );
	}

	/**
	 * Adds/overwrites an item in the registry.
	 *
	 * Defined only for compatibility with ArrayAccess, use add_item() directly.
	 *
	 * @since 3.8.0
	 *
	 * @param string $offset Item ID.
	 * @param mixed  $value  Item attributes.
	 */
	public function offsetSet( $offset, $value ) {
		$this->add_item( $offset, $value );
	}

	/**
	 * Removes an item from the registry.
	 *
	 * Defined only for compatibility with ArrayAccess, use remove_item() directly.
	 *
	 * @since 3.8.0
	 *
	 * @param string $offset Item ID.
	 */
	public function offsetUnset( $offset ) {
		$this->remove_item( $offset );
	}

}
