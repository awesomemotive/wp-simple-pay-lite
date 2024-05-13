<?php
/**
 * Transactions: BerlinDB database query
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Transaction\Database;

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
	protected $table_name = 'transactions';

	/**
	 * {@inheritdoc}
	 */
	protected $table_alias = 'txn';

	/**
	 * {@inheritdoc}
	 */
	protected $table_schema = '\\SimplePay\\Core\\Transaction\\Database\\Schema';

	/**
	 * {@inheritdoc}
	 */
	protected $item_name = 'transaction';

	/**
	 * {@inheritdoc}
	 */
	protected $item_name_plural = 'transactions';

	/**
	 * {@inheritdoc}
	 */
	protected $item_shape = '\stdClass';

	/**
	 * {@inheritdoc}
	 */
	protected $cache_group = 'transactions';

}
