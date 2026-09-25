<?php
/**
 * Subscriptions: Subscription
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription;

use SimplePay\Core\Model\AbstractModel;

/**
 * Subscription class.
 *
 * @since 4.17.4
 */
class Subscription extends AbstractModel {

	/**
	 * Subscription record ID.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	public $id;

	/**
	 * Payment Form ID.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	public $form_id;

	/**
	 * Stripe Subscription ID.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	public $object_id;

	/**
	 * Stripe Customer ID.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	public $customer_id;

	/**
	 * Connected Stripe account ID.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	public $stripe_account_id;

	/**
	 * Customer email address.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	public $email;

	/**
	 * Livemode.
	 *
	 * @since 4.17.4
	 * @var null|bool
	 */
	public $livemode;

	/**
	 * Subscription status.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	public $status;

	/**
	 * Recurring amount charged each billing period.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	public $amount;

	/**
	 * Currency.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	public $currency;

	/**
	 * Billing interval (day|week|month|year).
	 *
	 * @since 4.17.4
	 * @var string|null
	 */
	public $billing_interval;

	/**
	 * Number of intervals between billings.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	public $interval_count;

	/**
	 * Next renewal date timestamp.
	 *
	 * @since 4.17.4
	 * @var int|null
	 */
	public $current_period_end;

	/**
	 * Whether the subscription is set to cancel at the end of the period.
	 *
	 * @since 4.17.4
	 * @var null|bool
	 */
	public $cancel_at_period_end;

	/**
	 * Cancellation date timestamp.
	 *
	 * @since 4.17.4
	 * @var int|null
	 */
	public $canceled_at;

	/**
	 * Trial end date timestamp.
	 *
	 * @since 4.17.4
	 * @var int|null
	 */
	public $trial_end;

	/**
	 * Whether the subscription was created with an application fee.
	 *
	 * @since 4.17.4
	 * @var null|bool
	 */
	public $application_fee;

	/**
	 * Creation date timestamp.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	public $date_created;

	/**
	 * Modification date timestamp.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	public $date_modified;

	/**
	 * Subscription UUID.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	public $uuid;

	/**
	 * Subscription.
	 *
	 * @since 4.17.4
	 *
	 * @param array<mixed> $data Data to create a model from.
	 */
	public function __construct( $data ) {
		parent::__construct( $data );

		// Cast values.

		if ( ! empty( $this->id ) ) {
			$this->id = (int) $this->id;
		}

		if ( ! empty( $this->form_id ) ) {
			$this->form_id = (int) $this->form_id;
		}

		if ( ! empty( $this->_object_id ) ) {
			$this->object_id = $this->_object_id;
			unset( $this->_object_id );
		}

		if ( isset( $this->livemode ) ) {
			$this->livemode = (bool) $this->livemode;
		}

		if ( ! empty( $this->amount ) ) {
			$this->amount = (int) $this->amount;
		}

		if ( ! empty( $this->interval_count ) ) {
			$this->interval_count = (int) $this->interval_count;
		}

		if ( isset( $this->cancel_at_period_end ) ) {
			$this->cancel_at_period_end = (bool) $this->cancel_at_period_end;
		}

		if ( isset( $this->application_fee ) ) {
			$this->application_fee = (bool) $this->application_fee;
		}

		// Convert datetime columns to timestamps for display.
		foreach ( array( 'current_period_end', 'canceled_at', 'trial_end', 'date_created', 'date_modified' ) as $field ) {
			/** @var string|int|null $value */
			$value = isset( $this->{$field} ) ? $this->{$field} : null;

			if ( ! empty( $value ) && is_string( $value ) && false !== strtotime( $value ) ) {
				$this->{$field} = strtotime( $value );
			}
		}
	}
}
