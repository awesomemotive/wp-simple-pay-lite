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

use SimplePay\Core\Database\TableUpgradeTrait;
use SimplePay\Vendor\BerlinDB\Database\Table as BerlinDBTable;

/**
 * Table class.
 *
 * @since 4.4.6
 */
class Table extends BerlinDBTable {

	use TableUpgradeTrait;

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
	protected $version = 202610160001;

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
		'202610160001' => 202610160001,
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
		$object = $this->get_db()->query(
			"ALTER TABLE {$this->table_name} MODIFY COLUMN `object` varchar(100) NOT NULL"
		);

		// Set column `status` length.
		$status = $this->get_db()->query(
			"ALTER TABLE {$this->table_name} MODIFY COLUMN `status` varchar(50) NOT NULL"
		);

		if ( false === $object || false === $status ) {
			return false;
		}

		// Update the `object_status` index.
		if ( $this->index_exists( 'object_status' ) ) {
			$dropped = $this->get_db()->query(
				"DROP INDEX object_status ON {$this->table_name}"
			);

			if ( false === $dropped ) {
				return false;
			}
		}

		return $this->add_index_if_missing( 'object_status', '(`object`(100), `status`(50))' );
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
		return $this->add_column_if_missing(
			'payment_method_type',
			'varchar(50) DEFAULT NULL AFTER `currency`'
		);
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
		return $this->add_column_if_missing(
			'amount_refunded',
			'bigint(20) NOT NULL DEFAULT 0 AFTER `amount_discount`'
		);
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
		if ( ! $this->add_stripe_account_id_column() ) {
			return false;
		}

		return $this->backfill_stripe_account_id();
	}

	/**
	 * Upgrade to version 202610160001.
	 *  - Repair sites where the 202608180001 upgrade was recorded as done even
	 *    though its ALTER failed, leaving no `stripe_account_id` column and
	 *    breaking every transaction insert (#3582).
	 *
	 * @since 4.17.6
	 *
	 * @return bool
	 */
	protected function __202610160001() {
		if ( $this->column_exists( 'stripe_account_id' ) ) {
			// Healthy table: only make sure the index is there. Rows are left
			// alone, since they may legitimately belong to other accounts.
			return $this->add_index_if_missing( 'stripe_account_id', '(`stripe_account_id`)' );
		}

		// The column never existed, so every row predates it.
		return $this->__202608180001();
	}

	/**
	 * Adds the `stripe_account_id` column and index, if missing.
	 *
	 * @since 4.17.6
	 *
	 * @return bool True if both exist afterwards.
	 */
	private function add_stripe_account_id_column() {
		return (
			$this->add_column_if_missing(
				'stripe_account_id',
				'varchar(255) DEFAULT NULL AFTER `subscription_id`'
			) &&
			$this->add_index_if_missing( 'stripe_account_id', '(`stripe_account_id`)' )
		);
	}

	/**
	 * Assigns rows without a `stripe_account_id` to the connected account.
	 *
	 * Every such row predates multi-account support, so it belongs to
	 * whichever account is connected now.
	 *
	 * @since 4.17.6
	 *
	 * @return bool False if the update failed.
	 */
	private function backfill_stripe_account_id() {
		$account_id = get_option( 'simpay_stripe_connect_account_id', '' );

		if ( ! is_string( $account_id ) || '' === trim( $account_id ) ) {
			return true;
		}

		// The account ID is a Stripe `acct_...` identifier from a trusted
		// option; esc_sql() guards the interpolation, consistent with the
		// ALTER statements above.
		$account_id = esc_sql( trim( $account_id ) );

		$result = $this->get_db()->query(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			"UPDATE {$this->table_name} SET stripe_account_id = '{$account_id}' WHERE stripe_account_id IS NULL OR stripe_account_id = ''"
		);

		return false !== $result;
	}
}
