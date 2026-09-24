<?php
/**
 * Subscriptions: BerlinDB database table
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// phpcs:disable PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.MethodDoubleUnderscore

namespace SimplePay\Core\Subscription\Database;

use SimplePay\Vendor\BerlinDB\Database\Table as BerlinDBTable;

/**
 * Table class.
 *
 * @since 4.17.4
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
	protected $name = 'subscriptions';

	/**
	 * {@inheritdoc}
	 *
	 * @var int
	 */
	protected $version = 202608180002;

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
		'202608180002' => 202608180002,
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
			_object_id varchar(255) DEFAULT NULL,
			customer_id varchar(255) DEFAULT NULL,
			stripe_account_id varchar(255) DEFAULT NULL,
			email varchar(255) DEFAULT NULL,
			livemode tinyint(1) NOT NULL DEFAULT 0,
			status varchar(50) NOT NULL,
			amount bigint(20) NOT NULL DEFAULT 0,
			currency varchar(3) NOT NULL,
			billing_interval varchar(20) DEFAULT NULL,
			interval_count int(11) NOT NULL DEFAULT 1,
			current_period_end datetime DEFAULT NULL,
			cancel_at_period_end tinyint(1) NOT NULL DEFAULT 0,
			canceled_at datetime DEFAULT NULL,
			trial_end datetime DEFAULT NULL,
			application_fee tinyint(1) NOT NULL DEFAULT false,
			date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
			date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
			uuid varchar(100) NOT NULL,

			PRIMARY KEY (id),
			KEY form_id (form_id),
			KEY object_id (_object_id),
			KEY customer_id (customer_id),
			KEY stripe_account_id (stripe_account_id),
			KEY email (email),
			KEY status (status(50)),
			KEY date_created (date_created)
			';
	}

	/**
	 * Upgrade to version 202608180002.
	 *  - Add a `stripe_account_id` column so records can be scoped to the
	 *    connected Stripe account (#3533).
	 *
	 * @since 4.17.4
	 *
	 * @return bool
	 */
	protected function __202608180002() {
		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD COLUMN `stripe_account_id` varchar(255) DEFAULT NULL AFTER `customer_id`"
		);

		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD INDEX stripe_account_id (`stripe_account_id`)"
		);

		return $this->is_success( true );
	}
}
