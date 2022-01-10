<?php
/**
 * Migrations: Admin
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
 * Runs migrations that can safely be done on page load/automatically.
 *
 * @since 4.0.0
 */
function run_automatic_migrations() {
	$migrations = Utils\get_collection( 'migrations' );

	if ( false === $migrations ) {
		return;
	}

	$automatic_migrations = $migrations->by( 'automatic', true );

	if ( empty( $automatic_migrations ) ) {
		return;
	}

	foreach ( $automatic_migrations as $migration ) {
		if ( true === $migration->is_complete() ) {
			continue;
		}

		$migration->run();
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\\run_automatic_migrations' );
