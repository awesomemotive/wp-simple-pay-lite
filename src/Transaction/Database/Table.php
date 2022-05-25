<?php
/**
 * Transactions: BerlinDB database table
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Transaction\Database;

use SimplePay\Vendor\BerlinDB\Database\Table as BerlinDBTable;

/**
 * Table class.
 *
 * @since 4.4.6
 */
class Table extends BerlinDBTable {

	/**
	 * {@inheritdoc}
	 */
	protected $prefix = 'wpsp';

	/**
	 * {@inheritdoc}
	 */
	protected $name = 'transactions';

	/**
	 * {@inheritdoc}
	 */
	protected $version = 202200516000;

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
			form_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			object varchar(255) NOT NULL,
			_object_id varchar(255) DEFAULT NULL,
			livemode tinyint(1) NOT NULL DEFAULT 0,
			amount_total bigint(20) NOT NULL,
			amount_subtotal bigint(20) NOT NULL,
			amount_shipping bigint(20) NOT NULL,
			amount_discount bigint(20) NOT NULL,
			amount_tax bigint(20) NOT NULL,
			currency varchar(3) NOT NULL,
			email varchar(255) DEFAULT NULL,
			customer_id varchar(255) DEFAULT NULL,
			subscription_id varchar(255) DEFAULT NULL,
			status varchar(255) NOT NULL,
			application_fee tinyint(1) NOT NULL DEFAULT false,
			ip_address varchar(128) NOT NULL,
			date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
			date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
			uuid varchar(100) NOT NULL,

			PRIMARY KEY (id),
			KEY form_id (form_id),
			KEY object_id (_object_id),
			KEY date_created (date_created),
			KEY customer_id (customer_id),
			KEY email (email),
			KEY subscription_id (subscription_id),
			KEY object_status (object,status)
			";
	}

}
