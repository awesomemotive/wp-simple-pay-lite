<?php
/**
 * Subscriptions: Observer
 *
 * Keeps the local subscriptions table in sync with Stripe going forward. Rows
 * are added when a subscription is created from a payment form and updated as
 * subscription lifecycle webhooks arrive. Backfilling existing/historical
 * subscriptions from the Stripe API is tracked separately (#3533).
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\StripeConnect\ApplicationFee;

/**
 * SubscriptionObserver class.
 *
 * @since 4.17.4
 */
class SubscriptionObserver implements SubscriberInterface {

	/**
	 * Subscription repository.
	 *
	 * @since 4.17.4
	 * @var \SimplePay\Core\Subscription\SubscriptionRepository
	 */
	private $subscriptions;

	/**
	 * Application fee.
	 *
	 * @since 4.17.4
	 * @var \SimplePay\Core\StripeConnect\ApplicationFee
	 */
	private $application_fee;

	/**
	 * SubscriptionObserver.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Core\Subscription\SubscriptionRepository $subscriptions Subscription repository.
	 * @param \SimplePay\Core\StripeConnect\ApplicationFee        $application_fee Application fee.
	 */
	public function __construct(
		SubscriptionRepository $subscriptions,
		ApplicationFee $application_fee
	) {
		$this->subscriptions   = $subscriptions;
		$this->application_fee = $application_fee;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			// Created from a payment form (initial record).
			'simpay_after_subscription_from_payment_form_request' => array(
				'add_on_subscription',
				10,
				2,
			),
			// First invoice paid -- confirms/activates the subscription.
			'simpay_webhook_subscription_created' => array(
				'sync_on_webhook',
				10,
				2,
			),
			// Fired for BOTH customer.subscription.updated and .deleted, so it
			// covers renewals, plan changes, and cancellations.
			'simpay_webhook_subscription_cancel'  => array(
				'sync_on_webhook',
				10,
				2,
			),
		);
	}

	/**
	 * Adds a subscription record when one is created from a payment form.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @param \SimplePay\Core\Abstracts\Form        $form Payment form.
	 * @return void
	 */
	public function add_on_subscription( $subscription, $form ) {
		$this->upsert( $subscription, (int) $form->id );
	}

	/**
	 * Syncs a subscription record from a lifecycle webhook event.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Event        $event Stripe webhook event. Unused.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @return void
	 */
	public function sync_on_webhook( $event, $subscription ) {
		if ( null === $subscription ) {
			return;
		}

		$this->upsert( $subscription );
	}

	/**
	 * Imports a Stripe Subscription into the local table.
	 *
	 * Shared entry point for backfilling existing subscriptions from the Stripe
	 * API (#3533); reuses the same mapping/upsert as the webhook path so there
	 * is a single source of truth for how a Stripe Subscription maps to a row.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @return void
	 */
	public function import_from_stripe( $subscription ) {
		$this->upsert( $subscription );
	}

	/**
	 * Creates or updates the local record for a Stripe Subscription.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @param int|null                              $form_id Payment form ID, when known from the request.
	 * @return void
	 */
	private function upsert( $subscription, $form_id = null ) {
		if ( ! isset( $subscription->id ) ) {
			return;
		}

		$data     = $this->map_subscription( $subscription, $form_id );
		$existing = $this->subscriptions->get_by_object_id( $subscription->id );

		if ( $existing instanceof Subscription ) {
			// The originating form is only known at creation time; don't let a
			// later webbook overwrite it with 0.
			unset( $data['form_id'], $data['object_id'] );

			$this->subscriptions->update( $existing->id, $data );

			return;
		}

		$this->subscriptions->add( $data );
	}

	/**
	 * Maps a Stripe Subscription to the local column data.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @param int|null                              $form_id Payment form ID, when known from the request.
	 * @return array<string, mixed>
	 */
	private function map_subscription( $subscription, $form_id = null ) {
		// Resolve the customer, which may be an ID or an expanded object.
		$customer    = isset( $subscription->customer ) ? $subscription->customer : null;
		$customer_id = null;
		$email       = null;

		if ( is_object( $customer ) ) {
			$customer_id = isset( $customer->id ) ? $customer->id : null;
			$email       = isset( $customer->email ) ? $customer->email : null;
		} elseif ( is_string( $customer ) ) {
			$customer_id = $customer;
		}

		// Resolve the form ID from metadata when it was not passed in.
		if ( null === $form_id ) {
			$form_id = isset( $subscription->metadata->simpay_form_id )
				? (int) $subscription->metadata->simpay_form_id
				: 0;
		}

		$price = $this->get_primary_price( $subscription );

		return array(
			'form_id'              => (int) $form_id,
			'object_id'            => (string) $subscription->id,
			'customer_id'          => $customer_id,
			'email'                => $email,
			'livemode'             => isset( $subscription->livemode ) ? (bool) $subscription->livemode : false,
			'status'               => isset( $subscription->status ) ? (string) $subscription->status : '',
			'amount'               => $this->get_amount( $subscription ),
			'currency'             => $this->get_currency( $subscription, $price ),
			'billing_interval'     => ( $price && isset( $price->recurring->interval ) )
				? (string) $price->recurring->interval
				: null,
			'interval_count'       => ( $price && isset( $price->recurring->interval_count ) )
				? (int) $price->recurring->interval_count
				: 1,
			'current_period_end'   => $this->to_datetime(
				isset( $subscription->current_period_end ) ? $subscription->current_period_end : null
			),
			'cancel_at_period_end' => isset( $subscription->cancel_at_period_end )
				? (bool) $subscription->cancel_at_period_end
				: false,
			'canceled_at'          => $this->to_datetime(
				isset( $subscription->canceled_at ) ? $subscription->canceled_at : null
			),
			'trial_end'            => $this->to_datetime(
				isset( $subscription->trial_end ) ? $subscription->trial_end : null
			),
			'application_fee'      => $this->application_fee->has_application_fee(),
		);
	}

	/**
	 * Returns the first subscription item's Price object, if available.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @return \SimplePay\Vendor\Stripe\Price|null
	 */
	private function get_primary_price( $subscription ) {
		if (
			! isset( $subscription->items->data ) ||
			! is_array( $subscription->items->data ) ||
			empty( $subscription->items->data )
		) {
			return null;
		}

		$item = $subscription->items->data[0];

		return isset( $item->price ) ? $item->price : null;
	}

	/**
	 * Returns the total recurring amount across all subscription items.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @return int
	 */
	private function get_amount( $subscription ) {
		if (
			! isset( $subscription->items->data ) ||
			! is_array( $subscription->items->data )
		) {
			return 0;
		}

		$amount = 0;

		foreach ( $subscription->items->data as $item ) {
			if ( ! isset( $item->price->unit_amount ) ) {
				continue;
			}

			$quantity = isset( $item->quantity ) ? (int) $item->quantity : 1;
			$amount  += (int) $item->price->unit_amount * $quantity;
		}

		return $amount;
	}

	/**
	 * Returns the subscription currency, falling back to the price currency.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
	 * @param \SimplePay\Vendor\Stripe\Price|null   $price Primary price.
	 * @return string
	 */
	private function get_currency( $subscription, $price ) {
		if ( isset( $subscription->currency ) && ! empty( $subscription->currency ) ) {
			return (string) $subscription->currency;
		}

		if ( $price && isset( $price->currency ) ) {
			return (string) $price->currency;
		}

		return '';
	}

	/**
	 * Converts a Stripe Unix timestamp to a GMT datetime string.
	 *
	 * @since 4.17.4
	 *
	 * @param int|null $timestamp Unix timestamp.
	 * @return string|null
	 */
	private function to_datetime( $timestamp ) {
		if ( empty( $timestamp ) ) {
			return null;
		}

		return gmdate( 'Y-m-d H:i:s', (int) $timestamp );
	}
}
