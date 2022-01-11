<?php
/**
 * Migrations: Single Migration
 *
 * Allows a migration to be run against arbitrary data an arbitrary number of times.
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single_Migration class.
 *
 * @since 4.1.0
 */
class Single_Migration extends Migration {

	/**
	 * Constructs the migration.
	 *
	 * @since 4.1.0
	 *
	 * @param array $args {
	 *   Setting section configuration.
	 *
	 *   @type string $id Migration ID.
	 * }
	 */
	public function __construct( $args ) {
		parent::__construct( $args );

		// A single migration cannot be run automatically as it must supply
		// data to the single item.
		$this->automatic = false;
	}

	/**
	 * Runs the migration for a single item.
	 *
	 * @since 4.1.0
	 *
	 * @param mixed $item Arbitrary item/data to run migration on.
	 */
	public function run( $item ) {
		_doing_it_wrong(
			__METHOD__,
			__( 'Migrations must implement their own <code>run</code> task', 'stripe' ),
			''
		);
	}

}
