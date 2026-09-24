<?php
/**
 * Subscriptions: BerlinDB database schema
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription\Database;

use SimplePay\Vendor\BerlinDB\Database\Schema as BerlinDBSchema;

/**
 * Schema class.
 *
 * @since 4.17.4
 */
class Schema extends BerlinDBSchema {

	/**
	 * {@inheritdoc}
	 *
	 * @var array<int, array<string, bool|int|string|null>>
	 */
	public $columns = array(

		// id.
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
			'validate' => 'intval',
		),

		// form_id.
		array(
			'name'       => 'form_id',
			'type'       => 'bigint',
			'length'     => '20',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// _object_id - the Stripe Subscription ID. Prefixed with a _ to bypass a
		// reserved column format that is defaulted to when BerlinDB calls
		// $wpdb::insert( $table, $data, $format ); without a format.
		array(
			'name'       => '_object_id',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
			'validate'   => 'sanitize_text_field',
		),

		// customer_id.
		array(
			'name'       => 'customer_id',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
			'validate'   => 'sanitize_text_field',
		),

		// stripe_account_id - the connected Stripe account this record belongs
		// to, so switching accounts does not surface another account's data.
		array(
			'name'       => 'stripe_account_id',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
			'validate'   => 'sanitize_text_field',
		),

		// email.
		array(
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
			'validate'   => 'sanitize_text_field',
		),

		// livemode.
		array(
			'name'       => 'livemode',
			'type'       => 'tinyint',
			'length'     => '1',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// status.
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '50',
			'allow_null' => false,
			'validate'   => 'sanitize_text_field',
		),

		// amount - the recurring amount charged each billing period.
		array(
			'name'       => 'amount',
			'type'       => 'bigint',
			'length'     => '20',
			'default'    => 0,
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// currency.
		array(
			'name'       => 'currency',
			'type'       => 'varchar',
			'length'     => '3',
			'allow_null' => false,
			'validate'   => 'sanitize_text_field',
		),

		// billing_interval - day|week|month|year. Named to avoid the MySQL
		// reserved word `interval`.
		array(
			'name'       => 'billing_interval',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => null,
			'allow_null' => true,
			'validate'   => 'sanitize_text_field',
		),

		// interval_count.
		array(
			'name'       => 'interval_count',
			'type'       => 'int',
			'length'     => '11',
			'default'    => 1,
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// current_period_end - next renewal date.
		array(
			'name'       => 'current_period_end',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// cancel_at_period_end.
		array(
			'name'       => 'cancel_at_period_end',
			'type'       => 'tinyint',
			'length'     => '1',
			'default'    => 0,
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// canceled_at.
		array(
			'name'       => 'canceled_at',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
		),

		// trial_end.
		array(
			'name'       => 'trial_end',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
		),

		// application_fee.
		array(
			'name'       => 'application_fee',
			'type'       => 'tinyint',
			'length'     => '1',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// date_created.
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_modified.
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// uuid.
		array(
			'name' => 'uuid',
			'uuid' => true,
		),

	);
}
