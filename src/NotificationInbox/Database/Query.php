<?php
/**
 * Notification inbox: BerlinDB database query
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox\Database;

use SimplePay\Vendor\BerlinDB\Database\Query as BerlinDBQuery;

/**
 * Query class.
 *
 * @since 4.4.5
 */
class Query extends BerlinDBQuery {

	/**
	 * {@inheritdoc}
	 */
	protected $prefix = 'wpsp';

	/**
	 * {@inheritdoc}
	 */
	protected $table_name = 'notifications';

	/**
	 * {@inheritdoc}
	 */
	protected $table_alias = 'ntf';

	/**
	 * {@inheritdoc}
	 */
	protected $table_schema = '\\SimplePay\\Core\\NotificationInbox\\Database\\Schema';

	/**
	 * {@inheritdoc}
	 */
	protected $item_name = 'notification';

	/**
	 * {@inheritdoc}
	 */
	protected $item_name_plural = 'notifications';

	/**
	 * {@inheritdoc}
	 */
	protected $item_shape = '\stdClass';

	/**
	 * {@inheritdoc}
	 */
	protected $cache_group = 'notifications';

}
