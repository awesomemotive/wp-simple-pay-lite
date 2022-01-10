<?php
/**
 * Collections registry
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
 * Collections registry class.
 *
 * @since 3.8.0
 */
class Collections extends Collection implements Static_Collection {

	/**
	 * The one true Collections registry instance.
	 *
	 * @since 3.8.0
	 * @var \SimplePay\Core\Utils\Collections
	 */
	private static $instance;

	/**
	 * Retrieves the one true Collectinos registry instance.
	 *
	 * @since 3.8.0
	 *
	 * @return \SimplePay\Core\Utils\Collections Collections registry instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Collections();
		}

		return self::$instance;
	}

	/**
	 * Register a new collection.
	 *
	 * @since 3.8.0
	 *
	 * @param string                           $collection_id Collection ID.
	 * @param \SimplePay\Core\Utils\Collection $collection Collection.
	 * @return \WP_Error|true True on successful registration, otherwise a \WP_Error object.
	 */
	public function add( $collection_id, $collection ) {
		if ( $this->has_item( $collection_id ) ) {
			return new \WP_Error(
				'collection_exists',
				sprintf(
					/* translators: %s Collection ID that could not be registered. */
					__( 'The %s collection already exists and could not be added.', 'stripe' ),
					$collection_id
				)
			);
		}

		if ( ! is_a( $collection, '\SimplePay\Core\Utils\Collection' ) ) {
			return new \WP_Error(
				'collection_invalid',
				sprintf(
					/* translators: %s Collection ID that could not be registered. */
					__( 'The %s collection must be an instance of \SimplePay\Core\Utils\Collection.', 'stripe' ),
					$collection_id
				)
			);
		}

		return $this->add_item( $collection_id, $collection );
	}

	/**
	 * Removes an item from the registry by ID.
	 *
	 * @since 3.8.0
	 *
	 * @param string $collection_id Collection ID.
	 */
	public function remove_item( $collection_id ) {
		_doing_it_wrong( 'Initialized Collections cannot be removed.', '3.8.0' );
	}

}
