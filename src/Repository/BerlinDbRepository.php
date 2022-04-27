<?php
/**
 * Repository: BerlinDB
 *
 * Normalizes some BerlinDB operations to provide consistency between operations.
 * All singular operations return a shaped item, or null, and a query returns a
 * list of shaped items.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\Repository;

use SimplePay\Vendor\BerlinDB\Database\Base as BerlinDbQuery;
use stdClass;

/**
 * BerlinDbRepository class.
 *
 * @since 4.4.5
 */
class BerlinDbRepository extends BerlinDbQuery implements RepositoryInterface {

	/**
	 * Item shape.
	 *
	 * @since 4.4.5
	 * @var string Fully qualified class name.
	 */
	protected $shape;

	/**
	 * Item query.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Vendor\BerlinDB\Database\Query
	 */
	protected $query;

	/**
	 * BerlinDbRepository.
	 *
	 * @since 4.4.5
	 *
	 * @param string $shape Item shape in the form of a fully qualified class name.
	 * @param string $query BerlinDB Query class in the form of a fully qualified class name.
	 *                      https://github.com/berlindb/core/blob/master/src/Database/Query.php
	 */
	public function __construct( $shape, $query ) {
		$this->shape = $shape;
		$this->query = new $query; // @phpstan-ignore-line
	}

	/**
	 * Shapes a single item to a model given a set of data.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $data Model data.
	 * @return \SimplePay\Core\Model\ModelInterface
	 */
	private function shape( $data ) {
		return new $this->shape( (array) $data ); // @phpstan-ignore-line
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( $id ) {
		$item = $this->query->get_item( $id );

		if ( false === $item ) {
			return null;
		}

		return $this->shape( (array) $item );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_by( $column_name, $column_value ) {
		$item = $this->query->get_item_by( $column_name, $column_value );

		if ( false === $item ) {
			return null;
		}

		return $this->shape( (array) $item );
	}

	/**
	 * {@inheritdoc}
	 */
	public function add( $data ) {
		$item = $this->query->add_item( $data );

		if ( false === $item ) {
			return null;
		}

		/** @var int $item_id */
		$item_id = $item;

		$item = $this->query->get_item( $item_id );

		return $this->shape( (array) $item );
	}

	/**
	 * {@inheritdoc}
	 */
	public function update( $id, $data ) {
		$item = $this->query->update_item( $id, $data );
		/** @var \SimplePay\Vendor\BerlinDB\Database\Base $this */
		$last_result = $this->get_db()->last_result;

		// BerlinDB does not perform an update if the data has not changed but
		// it still returns false. If an update returns false and the latest
		// database result's ID does not equal the item being updated then
		// something truly went wrong.
		if (
			false === $item &&
			! empty( $last_result ) &&
			(string) $last_result[0]->id !== (string) $id
		) {
			return null;
		}

		/** @var \SimplePay\Core\Repository\BerlinDbRepository $this */
		return $this->get( $id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete( $id ) {
		$item = $this->query->get_item( $id );

		if ( false === $item ) {
			return null;
		}

		$delete = $this->query->delete_item( $id );

		if ( false === $delete ) {
			return null;
		}

		return $this->shape( (array) $item );
	}

	/**
	 * {@inheritdoc}
	 */
	public function query( $args = array() ) {
		if ( isset( $args['count'] ) ) {
			unset( $args['count'] );

			_doing_it_wrong(
				__METHOD__,
				__( 'Counting results via a query is not allowed. Use the ::count() method.', 'stripe' ),
				'4.4.5'
			);
		}

		/** @var array<stdClass> $items */
		$items = $this->query->query( $args );

		$items = array_map(
			function( $item ) {
				return $this->get( $item->id );
			},
			$items
		);

		return array_filter(
			$items,
			function( $item ) {
				return $item instanceof $this->shape;
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function count( $args = array() ) {
		$args['count'] = true;

		/** @var int $count */
		$count = $this->query->query( $args );

		return $count;
	}

}
