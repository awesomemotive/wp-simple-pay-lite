<?php
/**
 * Transactions: BerlinDB database schema
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Transaction\Database;

use SimplePay\Vendor\BerlinDB\Database\Schema as BerlinDBSchema;

/**
 * Schema class.
 *
 * @since 4.4.6
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

		// object.
		array(
			'name'       => 'object',
			'type'       => 'varchar',
			'length'     => '100',
			'allow_null' => false,
			'validate'   => 'sanitize_text_field',
		),

		// _object_id - prefixed with a _ to bypass a reserved column format that is defaulted
		// to when BerlinDB calls $wpdb::insert( $table, $data, $format ); without a format.
		array(
			'name'       => '_object_id',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
			'validate'   => 'sanitize_text_field',
		),

		// amount_total.
		array(
			'name'       => 'amount_total',
			'type'       => 'bigint',
			'length'     => '20',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// amount_subtotal.
		array(
			'name'       => 'amount_subtotal',
			'type'       => 'bigint',
			'length'     => '20',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// amount_shipping.
		array(
			'name'       => 'amount_shipping',
			'type'       => 'bigint',
			'length'     => '20',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// amount_discount.
		array(
			'name'       => 'amount_discount',
			'type'       => 'bigint',
			'length'     => '20',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// amount_tax.
		array(
			'name'       => 'amount_tax',
			'type'       => 'bigint',
			'length'     => '20',
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

		// payment_method_type.
		array(
			'name'       => 'payment_method_type',
			'type'       => 'varchar',
			'length'     => '50',
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

		// email.
		array(
			'name'       => 'email',
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

		// subscription_id.
		array(
			'name'       => 'subscription_id',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
			'validate'   => 'sanitize_text_field',
		),

		// status.
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '50',
			'allow_null' => false,
			'validate'   => 'sanitize_text_field',
		),

		// application_fee.
		array(
			'name'       => 'application_fee',
			'type'       => 'tinyint',
			'length'     => '1',
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// ip_address.
		array(
			'name'       => 'ip_address',
			'type'       => 'varchar',
			'length'     => '128',
			'allow_null' => false,
			'validate'   => 'sanitize_text_field',
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
