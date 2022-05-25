<?php
/**
 * Transactions: Transaction
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Transaction;

use SimplePay\Core\Model\AbstractModel;

/**
 * Transaction class.
 *
 * @since 4.4.6
 */
class Transaction extends AbstractModel {

	/**
	 * Notification ID.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $id;

	/**
	 * Payment Form ID.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $form_id;

	/**
	 * Object type.
	 *
	 * @since 4.4.6
	 * @var string
	 */
	public $object;

	/**
	 * Object ID.
	 *
	 * @since 4.4.6
	 * @var string
	 */
	public $object_id;

	/**
	 * Livemode.
	 *
	 * @since 4.4.6
	 * @var null|bool
	 */
	public $livemode;

	/**
	 * Transaction amount total.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $amount_total;

	/**
	 * Transaction amount subtotal.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $amount_subtotal;

	/**
	 * Transaction amount shipping.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $amount_shipping;

	/**
	 * Transaction amount discount.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $amount_discount;

	/**
	 * Transaction amount tax.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $amount_tax;

	/**
	 * Transaction currency.
	 *
	 * @since 4.4.6
	 * @var string
	 */
	public $currency;

	/**
	 * Transaction email address.
	 *
	 * @since 4.4.6
	 * @var string
	 */
	public $email;

	/**
	 * Transaction Customer ID.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $customer_id;

	/**
	 * Transaction Subscription ID.
	 *
	 * @since 4.4.6
	 * @var int|null
	 */
	public $subscription_id;

	/**
	 * Transaction status.
	 *
	 * @since 4.4.6
	 * @var string
	 */
	public $status;

	/**
	 * Transaction application_fee.
	 *
	 * @since 4.4.6
	 * @var null|bool
	 */
	public $application_fee;

	/**
	 * Transaction IP address.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $ip_address;

	/**
	 * Transaction creation date timestamp.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $date_created;

	/**
	 * Transaction modification date timestamp.
	 *
	 * @since 4.4.6
	 * @var int
	 */
	public $date_modified;

	/**
	 * Transaction UUID.
	 *
	 * @since 4.4.6
	 * @var string
	 */
	public $uuid;

	/**
	 * Transaction.
	 *
	 * @since 4.4.6
	 *
	 * @param array<mixed> $data Data to create an model from.
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

		if ( ! empty( $this->amount_total ) ) {
			$this->amount_total = (int) $this->amount_total;
		}

		if ( ! empty( $this->amount_subtotal ) ) {
			$this->amount_subtotal = (int) $this->amount_subtotal;
		}

		if ( ! empty( $this->amount_shipping ) ) {
			$this->amount_shipping = (int) $this->amount_shipping;
		}

		if ( ! empty( $this->amount_discount ) ) {
			$this->amount_discount = (int) $this->amount_discount;
		}

		if ( ! empty( $this->amount_tax ) ) {
			$this->amount_tax = (int) $this->amount_tax;
		}

		if ( isset( $this->application_fee ) ) {
			$this->application_fee = (bool) $this->application_fee;
		}

		/** @var string $date_created */
		$date_created = $this->date_created;

		if ( ! empty( $date_created ) && false !== strtotime( $date_created ) ) {
			$this->date_created = strtotime( $date_created );
		}

		/** @var string $date_modified */
		$date_modified = $this->date_modified;

		if ( ! empty( $date_modified ) && false !== strtotime( $date_modified ) ) {
			$this->date_modified = strtotime( $date_modified );
		}
	}

}
