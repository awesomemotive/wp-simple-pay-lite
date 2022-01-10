<?php
/**
 * Migrations: Register
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
 * Registers available migrations.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
 */
function register( $registry ) {
	// Add Migrations registry to Collections registry.
	$migrations = new Migration_Collection();
	$registry->add( 'migrations', $migrations );

	// Options API/flattening.
	$migrations->add(
		new Routines\Options_Flattening(
			array(
				'id'        => 'options-flattening',
				'automatic' => true,
			)
		)
	);

	// Payment Form amounts to Prices API.
	$migrations->add(
		new Routines\Prices_API(
			array(
				'id' => 'prices-api',
			)
		)
	);

	/**
	 * Allows further migrations to be registered.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Utils\Migrations\Migration_Collection $migrations Migration collection.
	 */
	do_action( 'simpay_register_migrations', $migrations );
}
add_action( 'simpay_register_collections', __NAMESPACE__ . '\\register' );
