<?php
/**
 * Migrations: Migration
 *
 * @package SimplePay\Core\Utils\Migrations
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
 * Migration class.
 *
 * @since 4.0.0
 */
class Migration {

	/**
	 * Migration ID.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $id;

	/**
	 * Migration runs automatically.
	 *
	 * @since 4.0.0
	 * @var bool
	 */
	public $automatic;

	/**
	 * Constructs the migration.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Setting section configuration.
	 *
	 *   @type string $id Migration ID.
	 *   @type bool   $automatic If the migration can run automatically. Default false.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'id'        => '',
			'automatic' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		// ID.
		$this->id = sanitize_text_field( $args['id'] );

		// Automatic.
		$this->automatic = (bool) $args['automatic'];
	}

	/**
	 * Runs the migration.
	 *
	 * @since 4.0.0
	 */
	public function run() {
		_doing_it_wrong(
			__METHOD__,
			__( 'Migrations must implement their own <code>run</code> task', 'stripe' ),
			''
		);
	}

	/**
	 * Determines if the migration has been previously completed.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_complete() {
		$completed_migrations = get_option( 'simpay_completed_migrations', array() );

		return isset( $completed_migrations[ $this->id ] );
	}

	/**
	 * Marks an upgrade as completed.
	 *
	 * @since 4.0.0
	 */
	public function complete() {
		$completed_upgrades = get_option( 'simpay_completed_migrations', array() );

		update_option(
			'simpay_completed_migrations',
			array_merge(
				array(
					$this->id => true,
				),
				$completed_upgrades
			)
		);
	}

}
