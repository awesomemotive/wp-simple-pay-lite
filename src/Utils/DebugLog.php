<?php
/**
 * Utils: Debug log
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Utils;

/**
 * DebugLog class.
 *
 * Writes a prefixed line to the PHP error log for failures that happen inside
 * a cron run, where there is no request to return an error to and no screen to
 * show a notice on. Silent `catch`/`continue` in a background job is the case
 * that cannot be diagnosed after the fact: the symptom is a record that never
 * updates, with nothing anywhere saying why.
 *
 * Gated on `WP_DEBUG` so a production site's log is not filled by a background
 * job retrying a permanently broken record. Durable state that survives the run
 * belongs in an option next to the job's cursor, not here.
 *
 * @since 4.17.4
 */
class DebugLog {

	/**
	 * Writes a message to the error log when debugging is enabled.
	 *
	 * @since 4.17.4
	 *
	 * @param string $message Message to log.
	 * @return void
	 */
	public static function log( $message ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		error_log( 'WP Simple Pay: ' . $message );
	}
}
