<?php
/**
 * Notification inbox: Notification importer interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.x.x
 */

namespace SimplePay\Core\NotificationInbox\NotificationImporter;

/**
 * NotificationImporterInterface interface.
 *
 * @since 4.x.x
 */
interface NotificationImporterInterface {

	/**
	 * Imports notification inbox notifications.
	 *
	 * @since 4.x.x
	 *
	 * @return void
	 */
	public function import();

	/**
	 * Fetches notifications from a source.
	 *
	 * @since 4.x.x
	 *
	 * @return array<array<string, array<array<string>|string>|string>>
	 */
	public function fetch();

	/**
	 * Returns the source of the notification importer.
	 *
	 * @since 4.x.x
	 *
	 * @return string
	 */
	public function get_source();

}
