<?php
/**
 * Utils: Collections
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
 * Initializes the Collections registry and allows further
 * Collections to be registered.
 *
 * Waits until `plugins_loaded` instead of during plugin instantiation.
 *
 * @since 3.8.0
 */
function register_collections() {
	$collections = Collections::instance();

	/**
	 * Allows further Collections to be registered.
	 *
	 * @since 3.8.0
	 *
	 * @param \SimplePay\Core\Utils\Collections $collections Collections registry.
	 */
	do_action( 'simpay_register_collections', $collections );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\register_collections' );

/**
 * Returns a Collection from the Collections registry.
 *
 * @since 3.8.0
 *
 * @param string $collection_id Collection ID.
 * @return \SimplePay\Core\Utils\Collection|false Collection if available, otherwise false.
 */
function get_collection( $collection_id ) {
	$collections = Collections::instance();

	return $collections->get_item( $collection_id );
}
