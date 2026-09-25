<?php
/**
 * Subscriptions: Subscription repository
 *
 * Like the Transaction records, these are a convenience log of subscriptions
 * created through the plugin. They are kept current going forward via
 * subscription lifecycle webhooks and are not intended for financial
 * reconciliation -- data may be incomplete if a webhook event is not received.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription;

use SimplePay\Core\Repository\BerlinDbRepository;

/**
 * SubscriptionRepository class.
 *
 * @since 4.17.4
 */
class SubscriptionRepository extends BerlinDbRepository {

	/**
	 * SubscriptionRepository.
	 *
	 * @since 4.17.4
	 */
	public function __construct() {
		parent::__construct( Subscription::class, Database\Query::class );
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

		// Scope the record to the connected Stripe account so switching
		// accounts does not surface another account's data (#3533). Respect an
		// explicit value when one is supplied (e.g. tests, reconciliation).
		if ( ! array_key_exists( 'stripe_account_id', $data ) ) {
			$data['stripe_account_id'] = (string) simpay_get_account_id();
		}

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
	 * Retrieves a subscription by the Stripe Subscription ID.
	 *
	 * @since 4.17.4
	 *
	 * @param string $object_id Stripe Subscription ID.
	 * @return \SimplePay\Core\Model\ModelInterface|null
	 */
	public function get_by_object_id( $object_id ) {
		return $this->get_by( '_object_id', $object_id );
	}
}
