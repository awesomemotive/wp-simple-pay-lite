<?php
/**
 * Routines: Settings integer keys.
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.7
 */

namespace SimplePay\Core\Utils\Migrations\Routines;

use SimplePay\Core\Settings;
use SimplePay\Core\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings_Integer_Keys class
 *
 * Removes integer-keyed entries from the `simpay_settings` option. Earlier
 * versions duplicated these entries on each settings update, which could grow
 * the (autoloaded) option to many megabytes.
 *
 * @since 4.17.7
 */
class Settings_Integer_Keys extends Migrations\Bulk_Migration {

	/**
	 * Runs the migration.
	 *
	 * @since 4.17.7
	 */
	public function run() {
		$settings = get_option( 'simpay_settings', array() );

		if ( is_array( $settings ) ) {
			$cleaned = Settings\remove_integer_keys( $settings );

			if ( count( $cleaned ) !== count( $settings ) ) {
				update_option( 'simpay_settings', $cleaned );
			}
		}

		return $this->complete();
	}
}
