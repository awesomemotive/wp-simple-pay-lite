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
	protected $version = 202608180001;

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
		'202404230001' => 202404230001,
		'202608180001' => 202608180001,
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
			amount_refunded bigint(20) NOT NULL DEFAULT 0,
			amount_tax bigint(20) NOT NULL,
			currency varchar(3) NOT NULL,
			payment_method_type varchar(50) DEFAULT NULL,
			email varchar(255) DEFAULT NULL,
			customer_id varchar(255) DEFAULT NULL,
			subscription_id varchar(255) DEFAULT NULL,
			stripe_account_id varchar(255) DEFAULT NULL,
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
			KEY stripe_account_id (stripe_account_id),
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

	/**
	 * Upgrade to version 202404230001.
	 *  - Add a new `amount_refunded` column.
	 *
	 * @since 4.10.0
	 *
	 * @return bool
	 */
	protected function __202404230001() {
		// Add the `amount_refunded` column after `amount_discount`.
		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD COLUMN `amount_refunded` bigint(20) NOT NULL DEFAULT 0 AFTER `amount_discount`"
		);

		return $this->is_success( true );
	}

	/**
	 * Upgrade to version 202608180001.
	 *  - Add a `stripe_account_id` column so records can be scoped to the
	 *    connected Stripe account (#3533).
	 *  - Backfill existing rows with the currently connected account, since all
	 *    pre-existing records belong to it.
	 *
	 * @since 4.17.4
	 *
	 * @return bool
	 */
	protected function __202608180001() {
		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD COLUMN `stripe_account_id` varchar(255) DEFAULT NULL AFTER `subscription_id`"
		);

		$this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD INDEX stripe_account_id (`stripe_account_id`)"
		);

		// Backfill: every existing row predates multi-account support, so it
		// belongs to whichever account is connected now. The account ID is a
		// Stripe `acct_...` identifier from a trusted option; esc_sql() guards
		// the interpolation, consistent with the ALTER statements above.
		$account_id = get_option( 'simpay_stripe_connect_account_id', '' );

		if ( is_string( $account_id ) && '' !== trim( $account_id ) ) {
			$account_id = esc_sql( trim( $account_id ) );

			$this->get_db()->query(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				"UPDATE {$this->table_name} SET stripe_account_id = '{$account_id}' WHERE stripe_account_id IS NULL OR stripe_account_id = ''"
			);
		}

		return $this->is_success( true );
	}
}
