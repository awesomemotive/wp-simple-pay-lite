<?php
/**
 * Migrations: Collection
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Utils\Migrations;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration_Collection class
 *
 * @since 4.0.0
 */
class Migration_Collection extends Utils\Collection {

	/**
	 * Adds a Migration to the collection.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Utils\Migrations\Migration $migration Migration.
	 * @return \WP_Error|true True on successful addition, otherwise a \WP_Error object.
	 */
	public function add( $migration ) {
		if ( ! $migration instanceof \SimplePay\Core\Utils\Migrations\Migration ) {
			return new \WP_Error(
				'invalid_migration',
				sprintf(
					/* translators: %s Collection ID that could not be registered. */
					__( 'The %s migration is invalid.', 'stripe' ),
					get_class( $migration )
				)
			);
		}

		// Validate ID.
		if ( empty( $migration->id ) ) {
			return new \WP_Error(
				'invalid_migration_id',
				__( 'Parameter <code>id</code> is required when registering a migration.', 'stripe' )
			);
		}

		return $this->add_item( $migration->id, $migration );
	}

	/**
	 * Filters registered migrations given a criteria.
	 *
	 * @since 4.0.0
	 *
	 * @param string $by Attribute to filter by.
	 * @param string $value Attribute value to compare.
	 * @return array
	 */
	public function by( $by, $value ) {
		$migrations = $this->get_items();

		return array_filter(
			$migrations,
			function( $migration ) use ( $by, $value ) {
				return $value === $migration->$by;
			}
		);
	}

}
