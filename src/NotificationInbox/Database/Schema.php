<?php
/**
 * Notification inbox: BerlinDB database schema
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox\Database;

use SimplePay\Vendor\BerlinDB\Database\Schema as BerlinDBSchema;

/**
 * Schema class.
 *
 * @since 4.4.5
 */
class Schema extends BerlinDBSchema {

	/**
	 * {@inheritdoc}
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
			'validate'   => 'intval',
		),

		// remote_id.
		array(
			'name'       => 'remote_id',
			'type'       => 'bigint',
			'length'     => '20',
			'default'    => null,
			'allow_null' => false,
			'validate'   => 'intval',
		),

		// type.
		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '64',
			'unsigned'   => true,
			'searchable' => false,
			'sortable'   => false,
			'validate'   => 'sanitize_text_field',
		),

		// source.
		array(
			'name'       => 'source',
			'type'       => 'varchar',
			'length'     => '64',
			'unsigned'   => true,
			'searchable' => false,
			'sortable'   => false,
			'validate'   => 'sanitize_text_field',
		),

		// title.
		array(
			'name'     => 'title',
			'type'     => 'varchar',
			'length'   => '255',
			'sortable' => true,
			'validate' => 'sanitize_text_field',
		),

		// slug.
		array(
			'name'      => 'slug',
			'type'      => 'varchar',
			'length'    => '255',
			'sortable'  => true,
			'validate'  => 'sanitize_title',
			'cache_key' => 'slug',
		),

		// content.
		array(
			'name'       => 'content',
			'type'       => 'longtext',
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => false,
			'validate'   => 'wp_kses_post',
		),

		// actions.
		array(
			'name'       => 'actions',
			'type'       => 'longtext',
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => false,
		),

		// conditions.
		array(
			'name'       => 'conditions',
			'type'       => 'longtext',
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => false,
		),

		// start.
		array(
			'name'       => 'start',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => false,
		),

		// end.
		array(
			'name'       => 'end',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => false,
		),

		// dismissed.
		array(
			'name'       => 'dismissed',
			'type'       => 'tinyint',
			'length'     => 1,
			'default'    => 0,
			'sortable'   => false,
			'allow_null' => false,
			'transition' => true,
			'cache_key'  => 'dismissed',
		),

		// is_dismissible.
		array(
			'name'       => 'is_dismissible',
			'type'       => 'tinyint',
			'length'     => 1,
			'default'    => 1,
			'sortable'   => false,
			'allow_null' => false,
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

	);
}
