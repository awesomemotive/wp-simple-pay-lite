<?php
/**
 * Migrations: Migration
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
	 * Constructs the migration.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Setting section configuration.
	 *
	 *   @type string $id Migration ID.
	 * }
	 */
	public function __construct( $args ) {
		// ID.
		$this->id = sanitize_text_field( $args['id'] );
	}

}
