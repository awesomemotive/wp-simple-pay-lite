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

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// phpcs:disable PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.MethodDoubleUnderscore

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
	 *
	 * @var string
	 */
	protected $prefix = 'wpsp';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $name = 'transactions';

	/**
	 * {@inheritdoc}
	 *
	 * @var int
	 */
	protected $version = 202301090001;

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $schema = __NAMESPACE__ . '\\Schema';

	/**
	 * {@inheritdoc}
	 *
	 * @var array<string, int>
	 */
	protected $upgrades = array( // @phpstan-ignore-line
		'202206170001' => 202206170001,
		'202301090001' => 202301090001,
	);

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = '
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			form_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			object varchar(100) NOT NULL,
			_object_id varchar(255) DEFAULT NULL,
			livemode tinyint(1) NOT NULL DEFAULT 0,
			amount_total bigint(20) NOT NULL,
			amount_subtotal bigint(20) NOT NULL,
			amount_shipping bigint(20) NOT NULL,
			amount_discount bigint(20) NOT NULL,
			amount_tax bigint(20) NOT NULL,
			currency varchar(3) NOT NULL,
			payment_method_type varchar(50) DEFAULT NULL,
			email varchar(255) DEFAULT NULL,
			customer_id varchar(255) DEFAULT NULL,
			subscription_id varchar(255) DEFAULT NULL,
			status varchar(50) NOT NULL,
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
			KEY object_status (object(100),status(50))
			';
	}

	/**
	 * Upgrade to version 202206170001.
	 *  - Change the length of column `object` to `varchar(100)`.
	 *  - Change the length of column `status` to `varchar(50)`.
	 *  - Update the `object_status` index to reflect the new length of `object` and `status`.
	 *
	 * @since 4.4.7
	 *
	 * @return bool
	 */
	protected function __202206170001() {
		// Set column `object` length.
		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} MODIFY COLUMN `object` varchar(100) NOT NULL"
		);

		// Set column `status` length.
		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} MODIFY COLUMN `status` varchar(50) NOT NULL"
		);

		// Update the `object_status` index.
		$this->get_db()->query(
			"DROP INDEX object_status ON {$this->table_name}"
		);

		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD INDEX object_status (`object`(100), `status`(50))"
		);

		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 202206170001.
	 *  - Add a new `payment_method_type` column.
	 *
	 * @since 4.6.7
	 *
	 * @return bool
	 */
	protected function __202301090001() {
		// Add the `payment_method_type` column after `currency`.
		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD COLUMN `payment_method_type` varchar(50) DEFAULT NULL AFTER `currency`"
		);

		return $this->is_success( true );
	}

}
