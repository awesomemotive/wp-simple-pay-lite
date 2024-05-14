<?php
/**
 * Notification inbox: BerlinDB database table
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox\Database;

use SimplePay\Vendor\BerlinDB\Database\Table as BerlinDBTable;

/**
 * Table class.
 *
 * @since 4.4.5
 */
class Table extends BerlinDBTable {

	/**
	 * {@inheritdoc}
	 */
	protected $prefix = 'wpsp';

	/**
	 * {@inheritdoc}
	 */
	protected $name = 'notifications';

	/**
	 * {@inheritdoc}
	 */
	protected $version = 202203024000;

	/**
	 * {@inheritdoc}
	 */
	protected $schema = __NAMESPACE__ . '\\Schema';

	/**
	 * {@inheritdoc}
	 * @var array<string, string>
	 */
	protected $upgrades = array();

	/**
	 * {@inheritdoc}
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			remote_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			source varchar(64) NOT NULL,
			type varchar(64) NOT NULL,
			title text NOT NULL,
			slug text NOT NULL,
			content longtext NOT NULL,
			actions longtext DEFAULT NULL,
			conditions longtext DEFAULT NULL,
			start datetime DEFAULT NULL,
			end datetime DEFAULT NULL,
			dismissed tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			is_dismissible tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
			date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
			date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
			PRIMARY KEY (id),
			KEY dismissed_start_end (dismissed, start, end)";
	}

}
