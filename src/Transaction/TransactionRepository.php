<?php
/**
 * Transactions: Transaction repository
 *
 * These records, while they may be used to generate simple reports, are not
 * meant to be used for financial reporting or other purposes as it is possible
 * the data is not fully updated if a webhook event is not received.
 *
 * The primary advantage of this table is that it can be used to store _some_
 * sort of reference to transactions that were created with an application fee.
 *
 * When querying for items with an application fee the `object` column should
 * be evaluated to find a relevant Subscription if the `subscription_id` column
 * is null (this can occur with Stripe Checkout and no webhooks).
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Transaction;

use SimplePay\Core\Repository\BerlinDbRepository;
use SimplePay\Core\Utils;

/**
 * TransactionRepository class.
 *
 * @since 4.4.6
 */
class TransactionRepository extends BerlinDbRepository {

	/**
	 * TransactionRepository.
	 *
	 * @since 4.4.6
	 */
	public function __construct() {
		parent::__construct( Transaction::class, Database\Query::class );
	}

	/**
	 * {@inheritdoc}
	 */
	public function add( $data ) {
		// Prefix object_id to match column name.
		if ( array_key_exists( 'object_id', $data ) ) {
			$data['_object_id'] = $data['object_id'];
			unset( $data['object_id'] );
		}

		// Always log IP address.
		$data['ip_address'] = Utils\get_current_ip_address();

		return parent::add( $data );
	}

	/**
	 * {@inheritdoc}
	 */
	public function update( $id, $data ) {
		// Prefix object_id to match column name.
		if ( array_key_exists( 'object_id', $data ) ) {
			$data['_object_id'] = $data['object_id'];
			unset( $data['object_id'] );
		}

		return parent::update( $id, $data );
	}

	/**
	 * Retrieves a transaction by the Stripe object ID.
	 *
	 * @since 4.4.6
	 *
	 * @param string $object_id Stripe object ID.
	 * @return \SimplePay\Core\Model\ModelInterface|null
	 */
	public function get_by_object_id( $object_id ) {
		return $this->get_by( '_object_id', $object_id );
	}

}
