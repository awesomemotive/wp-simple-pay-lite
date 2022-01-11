<?php
/**
 * Migrations: Functions
 *
 * @package SimplePay\Core\Utils
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
 * Retrieves a migration routine.
 *
 * @since 4.1.0
 *
 * @param string $migration_id Migration ID.
 * @return \SimplePay\Core\Utils\Migrations\Migration|false Migration or false if not found.
 */
function get( $migration_id ) {
	$migrations = Utils\get_collection( 'migrations' );

	if ( false === $migrations ) {
		return false;
	}

	$migration = $migrations->get_item( $migration_id );

	if ( false === $migration ) {
		return false;
	}

	return $migration;
}

/**
 * Determines if a migration is complete.
 *
 * @since 4.0.0
 *
 * @param string $migration_id Migration ID.
 * @return bool
 */
function is_complete( $migration_id ) {
	$migrations = Utils\get_collection( 'migrations' );

	if ( false === $migrations ) {
		return false;
	}

	$migration = $migrations->get_item( $migration_id );

	if ( false === $migration ) {
		return false;
	}

	// Single migration is never complete.
	if ( ! $migration instanceof \SimplePay\Core\Utils\Migrations\Bulk_Migration ) {
		return false;
	}

	return $migration->is_complete();
}

/**
 * Runs a bulk migration.
 *
 * @since 4.0.0
 * @since 4.1.0 Call the `run()` method on the migration directly to facilitate migration types.
 *
 * @param string $migration_id Migration ID.
 */
function run( $migration_id ) {
	$migration = get( $migration_id );

	if ( false === $migration ) {
		return;
	}

	// Do not allow use with single migrations.
	if ( ! $migration instanceof \SimplePay\Core\Utils\Migrations\Bulk_Migration ) {
		return;
	}

	$migration->run();

	_doing_it_wrong(
		__FUNCTION__,
		__( 'Retrieve a migration object and use the `run()` method directly.', 'stripe' ),
		'4.1.0'
	);
}
