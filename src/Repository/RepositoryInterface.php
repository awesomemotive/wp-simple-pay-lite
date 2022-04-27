<?php
/**
 * Repository: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\Repository;

use SimplePay\Vendor\BerlinDB\Database\Base as BerlinDbQuery;

/**
 * RepositoryInterface interface.
 *
 * @since 4.4.5
 */
interface RepositoryInterface {

	/**
	 * Retrieves a single item.
	 *
	 * @since 4.4.5
	 *
	 * @param int $id Item ID.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function get( $id );

	/**
	 * Retrieves a single item by a column.
	 *
	 * @since 4.4.5
	 *
	 * @param string     $column_name Colum name.
	 * @param int|string $column_value Column value.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function get_by( $column_name, $column_value );

	/**
	 * Adds a single item.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $data Item data.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function add( $data );

	/**
	 * Updates a single item.
	 *
	 * @since 4.4.5
	 *
	 * @param int          $id Item ID.
	 * @param array<mixed> $data Item data.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function update( $id, $data );

	/**
	 * Deletes a single item.
	 *
	 * @since 4.4.5
	 *
	 * @param int $id Item ID.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function delete( $id );

	/**
	 * Queries for items given a set of criteria.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return array<\SimplePay\Core\Model\ModelInterface> List of models queried.
	 */
	public function query( $args = array() );

	/**
	 * Counts items given a set of criteria.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return int The number of results.
	 */
	public function count( $args = array() );

}
