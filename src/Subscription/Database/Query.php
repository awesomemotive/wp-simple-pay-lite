<?php
/**
 * Subscriptions: BerlinDB database query
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription\Database;

use SimplePay\Vendor\BerlinDB\Database\Query as BerlinDBQuery;

/**
 * Query class.
 *
 * @since 4.17.4
 */
class Query extends BerlinDBQuery {

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
	protected $table_name = 'subscriptions';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $table_alias = 'sub';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $table_schema = '\\SimplePay\\Core\\Subscription\\Database\\Schema';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $item_name = 'subscription';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $item_name_plural = 'subscriptions';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $item_shape = '\stdClass';

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $cache_group = 'subscriptions';
}
