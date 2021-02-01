<?php
/**
 * Migrations: Functions
 *
 * @package SimplePay\Core\Utils
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Utils\Migrations;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	return $migration->is_complete();
}

/**
 * Runs a migration.
 *
 * @since 4.0.0
 *
 * @param string $migration_id Migration ID.
 */
function run( $migration_id ) {
	$migrations = Utils\get_collection( 'migrations' );

	if ( false === $migrations ) {
		return;
	}

	$migration = $migrations->get_item( $migration_id );

	if ( false === $migration ) {
		return;
	}

	$migration->run();
}
