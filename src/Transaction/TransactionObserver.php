<?php
/**
 * Transactions: Observer
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Transaction;

use Exception;
use SimplePay\Core\API;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\StripeConnect\ApplicationFee;
use stdClass;

/**
 * TransactionObserver class.
 *
 * @since 4.4.6
 */
class TransactionObserver implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Transaction repository.
	 *
	 * @since 4.4.6
	 * @var \SimplePay\Core\Transaction\TransactionRepository
	 */
	private $transactions;

	/**
	 * Application fee helper.
	 *
	 * @since 4.4.6
	 * @var \SimplePay\Core\StripeConnect\ApplicationFee
	 */
	private $application_fee;

	/**
	 * TransactionObserver.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Core\Transaction\TransactionRepository $transactions Transaction repository.
	 * @param \SimplePay\Core\StripeConnect\ApplicationFee      $application_fee Appilcation fee helper.
	 */
	public function __construct(
		TransactionRepository $transactions,
		ApplicationFee $application_fee
	) {
		$this->transactions    = $transactions;
		$this->application_fee = $application_fee;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		$observe = array(
			// Initial one time payment.
			'simpay_after_paymentintent_from_payment_form_request' =>
				array(
					array( 'add_on_payment_intent', 10, 4 ),
					// @todo This might not be the best place for this,
					// but it makes enough sense for now.
					array( 'maybe_decrement_stock', 10, 4 ),
				),
			// Initial recurring payment.
			'simpay_after_subscription_from_payment_form_request' =>
				array(
					array( 'add_on_subscription', 10, 2 ),
					// @todo This might not be the best place for this,
					// but it makes enough sense for now.
					array( 'maybe_decrement_stock', 10, 4 ),
				),
			// Initial invoice payment.
			'simpay_after_invoice_from_payment_form_request' =>
				array(
					array( 'add_on_multiple_line_invoice', 10, 2 ),
					array( 'maybe_decrement_stock', 10, 4 ),
				),
			'simpay_webhook_invoice_paid'               => array(
				array( 'update_on_multiple_line_invoice', 10, 2 ),
			),
			// Initial Checkout Session.
			'simpay_after_checkout_session_from_payment_form_request' =>
				array( 'add_on_checkout_session', 10, 2 ),
			// Update one time payment.
			'simpay_webhook_payment_intent_succeeded'   =>
				array( 'update_on_payment_intent', 10, 2 ),
			// Update Checkout Session.
			'simpay_webhook_checkout_session_completed' =>
				array( 'update_on_checkout_session', 10, 4 ),
			'simpay_webhook_invoice_payment_succeeded'  =>
				array(
					// Initial recurring subsequent invoice.
					array( 'add_on_invoice', 10, 3 ),
					// Update recurring subsequent invoice.
					array( 'update_on_invoice', 10, 3 ),
				),

			// Update on charge failed.
			// @todo This might not be the best place for this, but it makes
			// enough sense for now.
			'simpay_webhook_charge_failed'              =>
				array(
					array( 'update_on_failed', 10, 2 ),
					array( 'maybe_increment_stock', 10, 2 ),
				),
			'simpay_webhook_charge_refunded'            => array( 'add_refund_log', 10, 3 ),
		);

		// Update Checkout Session in Lite when viewing confirmation.
		if ( true === $this->license->is_lite() ) {
			$observe['_simpay_payment_confirmation'] =
				array( 'update_on_checkout_session_lite' );
		}

		return $observe;
	}

	/**
	 * Logs a transaction when a refund is created.
	 *
	 * @since 4.10.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Event  $event Stripe Event object.
	 * @param \SimplePay\Vendor\Stripe\Charge $charge Stripe Charge object.
	 * @param \SimplePay\Core\Abstracts\Form  $form Payment Form.
	 * @return void
	 */
	public function add_refund_log( $event, $charge, $form ) {
		// Check if the payment made form WP Simple Pay.
		if ( ! isset( $charge->payment_intent->metadata->simpay_form_id ) ) {
			return;
		}

		$transaction = $this->transactions->get_by_object_id(
			$charge->payment_intent->id
		);

		// Update the transaction status if it exists.
		if ( $transaction instanceof Transaction ) {
			$this->transactions->update(
				$transaction->id,
				array(
					'status'          => 'refunded',
					'amount_refunded' => $charge->amount_refunded,
				)
			);
		} else {
			// Create a new transaction log if it doesn't exist.
			$this->transactions->add(
				array(
					'form_id'             => $charge->payment_intent->metadata->simpay_form_id,
					'object'              => 'payment_intent',
					'object_id'           => $charge->payment_intent->id,
					'livemode'            => (bool) $charge->livemode,
					'amount_total'        => $charge->amount,
					'amount_subtotal'     => $charge->amount_refunded,
					'amount_shipping'     => 0,
					'amount_discount'     => 0,
					'amount_refunded'     => $charge->amount_refunded,
					'currency'            => $charge->currency,
					'payment_method_type' => null,
					'email'               => $charge->billing_details->email, // @phpstan-ignore-line
					'customer_id'         => $charge->customer->id, // @phpstan-ignore-line
					'subscription_id'     => null,
					'status'              => 'refunded',
					'application_fee'     => $this->application_fee->has_application_fee(),
				)
			);
		}
	}

	/**
	 * Logs a transaction when a PaymentIntent is created.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent PaymentIntent object.
	 * @param \SimplePay\Core\Abstracts\Form         $form Payment Form.
	 * @param array<mixed>                           $form_data Form data generated by the client.
	 * @param array<mixed>                           $form_values Form values.
	 * @return void
	 */
	public function add_on_payment_intent( $payment_intent, $form, $form_data, $form_values ) {
		// Retrieve from metadata if it exists (UPE only).
		if ( isset(
			$payment_intent->metadata->simpay_unit_amount,
			$payment_intent->metadata->simpay_quantity
		) ) {
			$quantity    = $payment_intent->metadata->simpay_quantity;
			$unit_amount = $payment_intent->metadata->simpay_unit_amount;

			$subtotal = $unit_amount * $quantity;

			// Calculate based on the form data (non-UPE).
		} else {
			$price = simpay_payment_form_prices_get_price_by_id(
				$form,
				$form_data['price']['id'] // @phpstan-ignore-line
			);

			if ( false === $price ) {
				return;
			}

			// Custom amount. Verify minimum amount.
			if ( false === simpay_payment_form_prices_is_defined_price( $price->id ) ) {
				// Ensure custom amount meets minimum requirement.
				$unit_amount = $form_data['customAmount'];

				if ( $unit_amount < $price->unit_amount_min ) {
					$unit_amount = $price->unit_amount_min;
				}
			} else {
				$unit_amount = $price->unit_amount;
			}

			/** @var int $unit_amount */

			// Backwards compatibility amount filter.
			if ( has_filter( 'simpay_form_' . $form->id . '_amount' ) ) {
				/** @var int $unit_amount */
				$unit_amount = simpay_get_filtered(
					'amount',
					simpay_convert_amount_to_dollars( $unit_amount ),
					$form->id
				);

				$unit_amount = simpay_convert_amount_to_cents( $unit_amount );
			}

			// Calculate quantity.
			$quantity = isset( $form_values['simpay_quantity'] )
				? intval( $form_values['simpay_quantity'] ) // @phpstan-ignore-line
				: 1;

			$subtotal = $unit_amount * $quantity;
		}

		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $payment_intent->customer;

		$this->transactions->add(
			array(
				'form_id'             => $form->id,
				'object'              => $payment_intent->object,
				'object_id'           => $payment_intent->id,
				'livemode'            => (bool) $payment_intent->livemode,
				'amount_total'        => $payment_intent->amount,
				'amount_subtotal'     => $subtotal,
				'amount_shipping'     => 0,
				'amount_discount'     => 0,
				'amount_tax'          => isset( $payment_intent->metadata->simpay_tax_unit_amount )
					? (int) $payment_intent->metadata->simpay_tax_unit_amount
					: 0,
				'currency'            => $payment_intent->currency,
				'payment_method_type' => null,
				'email'               => $customer->email,
				'customer_id'         => $customer->id,
				'subscription_id'     => null,
				'status'              => $payment_intent->status,
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Logs a transaction when a Subscription is created.
	 *
	 * Data will later be updated via the `invoice.payment_succeeded` webhook event.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Subscription object.
	 * @param \SimplePay\Core\Abstracts\Form        $form Payment Form.
	 * @return void
	 */
	public function add_on_subscription( $subscription, $form ) {
		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $subscription->customer;

		$this->transactions->add(
			array(
				'form_id'             => $form->id,
				'object'              => 'subscription',
				'object_id'           => $subscription->id,
				'livemode'            => (bool) $subscription->livemode,
				'amount_total'        => 0,
				'amount_subtotal'     => 0,
				'amount_shipping'     => 0,
				'amount_discount'     => 0,
				'amount_tax'          => 0,
				'currency'            => $customer->currency,
				'payment_method_type' => null,
				'email'               => $customer->email,
				'customer_id'         => $customer->id,
				'subscription_id'     => $subscription->id,
				'status'              => $subscription->status,
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Logs a transaction when a subsequent Invoice is created.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Invoice $invoice Invoice object.
	 *
	 * @return void
	 */
	public function add_on_multiple_line_invoice( $invoice ) {
		if ( ! is_string( $invoice->payment_intent ) ) {
			return;
		}

		$transaction = $this->transactions->get_by_object_id(
			$invoice->payment_intent
		);

		// Do nothing if the PaymentIntent has already been logged.
		if ( $transaction instanceof Transaction ) {
			return;
		}

		if ( null === $invoice->total_discount_amounts ) {
			$total_discount = 0;
		} else {
			$total_discount = array_reduce(
				$invoice->total_discount_amounts,
				/**
				 * Adds a discount amount to the total discount.
				 *
				 * @since 4.4.6
				 *
				 * @param int       $total_discount Total discount, so far.
				 * @param \stdClass $discount Discount object.
				 */
				function ( $total_discount, $discount ) {
					/** @var \stdClass $discount */
					return $total_discount + $discount->amount;
				},
				0
			);
		}

		$metadata = $invoice->metadata;

		if ( ! isset( $metadata->simpay_form_id ) ) {
			return;
		}

		$this->transactions->add(
			array(
				'form_id'             => (int) $metadata->simpay_form_id,
				'object'              => 'payment_intent',
				'object_id'           => $invoice->payment_intent,
				'livemode'            => (bool) $invoice->livemode,
				'amount_total'        => $invoice->total,
				'amount_subtotal'     => $invoice->subtotal,
				'amount_discount'     => $total_discount,
				'amount_shipping'     => 0,
				'amount_tax'          => null === $invoice->tax
					? 0
					: $invoice->tax,
				'currency'            => $invoice->currency,
				'payment_method_type' => null,
				'email'               => $invoice->customer_email,
				'customer_id'         => $invoice->customer,
				'subscription_id'     => $invoice->subscription,
				'status'              => $invoice->status,
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Updates a transaction's status when a subsequent Invoice is paid.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Event   $event Event object.
	 * @param \SimplePay\Vendor\Stripe\Invoice $invoice Invoice object.
	 * @return void
	 */
	public function update_on_multiple_line_invoice( $event, $invoice ) {
		if ( ! is_string( $invoice->payment_intent ) ) {
			return;
		}

		$transaction = $this->transactions->get_by_object_id(
			$invoice->payment_intent
		);

		if ( ! $transaction instanceof Transaction ) {
			return;
		}

		$form = simpay_get_form( $transaction->form_id );

		if ( false === $form ) {
			return;
		}

		$invoice = API\Invoices\retrieve(
			array(
				'id'     => (string) $invoice->id,
				'expand' => array(
					'payment_intent.payment_method',
				),
			),
			$form->get_api_request_args()
		);

		/** @var \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent */
		$payment_intent = $invoice->payment_intent;

		/** @var \SimplePay\Vendor\Stripe\PaymentMethod $payment_method */
		$payment_method = $payment_intent->payment_method;

		$this->transactions->update(
			$transaction->id,
			array(
				'payment_method_type' => $payment_method->type,
				'status'              => 'paid' === $invoice->status
					? 'succeeded'
					: 'canceled',
			)
		);
	}

	/**
	 * Logs a transaction when a subsequent Subscription Invoice is created.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Vendor\Stripe\Event        $event Event object.
	 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice Invoice object.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Subscription object.
	 * @return void
	 */
	public function add_on_invoice( $event, $invoice, $subscription ) {
		// Do nothing if this invoice event was triggered by creating a Subscription.
		// A previously inserted record will be updated instead.
		if ( 'subscription_cycle' !== $invoice->billing_reason ) {
			return;
		}

		if ( ! is_string( $invoice->payment_intent ) ) {
			return;
		}

		$transaction = $this->transactions->get_by_object_id(
			$invoice->payment_intent
		);

		// Do nothing if the PaymentIntent has already been logged.
		if ( $transaction instanceof Transaction ) {
			return;
		}

		if ( null === $invoice->total_discount_amounts ) {
			$total_discount = 0;
		} else {
			$total_discount = array_reduce(
				$invoice->total_discount_amounts,
				/**
				 * Adds a discount amount to the total discount.
				 *
				 * @since 4.4.6
				 *
				 * @param int       $total_discount Total discount, so far.
				 * @param \stdClass $discount Discount object.
				 */
				function ( $total_discount, $discount ) {
					/** @var \stdClass $discount */
					return $total_discount + $discount->amount;
				},
				0
			);
		}

		$metadata = $subscription->metadata;

		if ( ! isset( $metadata->simpay_form_id ) ) {
			return;
		}

		/** @var \SimplePay\Vendor\Stripe\PaymentMethod $payment_method */
		$payment_method      = $subscription->default_payment_method;
		$payment_method_type = $payment_method ? $payment_method->type : null;

		$this->transactions->add(
			array(
				'form_id'             => (int) $metadata->simpay_form_id,
				'object'              => 'payment_intent',
				'object_id'           => $invoice->payment_intent,
				'livemode'            => (bool) $invoice->livemode,
				'amount_total'        => $invoice->total,
				'amount_subtotal'     => $invoice->subtotal,
				'amount_discount'     => $total_discount,
				'amount_shipping'     => 0,
				'amount_tax'          => null === $invoice->tax
					? 0
					: $invoice->tax,
				'currency'            => $invoice->currency,
				'payment_method_type' => $payment_method_type,
				'email'               => $invoice->customer_email,
				'customer_id'         => $invoice->customer,
				'subscription_id'     => $invoice->subscription,
				'status'              => 'paid' === $invoice->status
					? 'succeeded'
					: 'canceled',
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Logs a transaction when a Checkout Session is created. Either a payment_intent
	 * or invoice object will be created.
	 *
	 * Data will later be updated via the `checkout.session.completed` webhook event.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Vendor\Stripe\Checkout\Session $checkout_session Checkout Session object.
	 * @param \SimplePay\Core\Abstracts\Form            $form Payment Form.
	 * @return void
	 */
	public function add_on_checkout_session( $checkout_session, $form ) {
		$this->transactions->add(
			array(
				'form_id'             => $form->id,
				'object'              => 'checkout_session',
				'object_id'           => $checkout_session->id,
				'livemode'            => (bool) $checkout_session->livemode,
				'amount_total'        => 0,
				'amount_subtotal'     => 0,
				'amount_shipping'     => 0,
				'amount_discount'     => 0,
				'amount_tax'          => 0,
				'currency'            => $checkout_session->currency,
				'payment_method_type' => null,
				'email'               => $checkout_session->customer_email,
				'customer_id'         => $checkout_session->customer,
				'subscription_id'     => $checkout_session->subscription,
				'status'              => $checkout_session->status,
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Updates a transaction's totals when receiving the `payment_intent.succeeded`
	 * webhook event.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Vendor\Stripe\Event         $event Event object.
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent PaymentIntent object.
	 * @return void
	 */
	public function update_on_payment_intent( $event, $payment_intent ) {
		$transaction = $this->transactions->get_by_object_id(
			$payment_intent->id
		);

		if ( ! $transaction instanceof Transaction ) {
			return;
		}

		/** @var \SimplePay\Vendor\Stripe\PaymentMethod $payment_method */
		$payment_method      = $payment_intent->payment_method;
		$payment_method_type = $payment_method ? $payment_method->type : null;

		$this->transactions->update(
			$transaction->id,
			array(
				'payment_method_type' => $payment_method_type,
				'status'              => $payment_intent->status,
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Updates a transaction's totals when receiving the `checkout.session.completed`
	 * webhook event. This is used to avoid making manual calculations for the totals
	 * when creating the original transaction.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Vendor\Stripe\Event              $event Event object.
	 * @param \SimplePay\Vendor\Stripe\Customer           $customer Customer object.
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent|null $payment_intent PaymentIntent object, if available.
	 * @param \SimplePay\Vendor\Stripe\Subscription|null  $subscription Subscription object, if available.
	 * @return void
	 */
	public function update_on_checkout_session( $event, $customer, $payment_intent, $subscription ) {
		/** @var \SimplePay\Vendor\Stripe\Checkout\Session $checkout_session */
		$checkout_session = $event->data->object; // @phpstan-ignore-line
		$transaction      = $this->transactions->get_by_object_id(
			$checkout_session->id
		);

		if ( ! $transaction instanceof Transaction ) {
			return;
		}

		// One time payment.
		if (
			'payment' === $checkout_session->mode &&
			null !== $payment_intent
		) {
			$object    = 'payment_intent';
			$object_id = $payment_intent->id;

			/** @var \SimplePay\Vendor\Stripe\PaymentMethod $payment_method */
			$payment_method      = $payment_intent->payment_method;
			$payment_method_type = $payment_method->type;

			// Recurring payment, paid today.
		} elseif (
			'subscription' === $checkout_session->mode &&
			null !== $subscription &&
			null !== $subscription->latest_invoice &&
			null === $checkout_session->setup_intent
		) {
			/** @var \SimplePay\Vendor\Stripe\Invoice $latest_invoice */
			$latest_invoice = $subscription->latest_invoice;
			/** @var \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent */
			$payment_intent = $latest_invoice->payment_intent;

			$object    = 'payment_intent';
			$object_id = $payment_intent->id;

			/** @var \SimplePay\Vendor\Stripe\PaymentMethod $payment_method */
			$payment_method      = $subscription->default_payment_method;
			$payment_method_type = $payment_method->type;

			// Recurring payment, trial.
			// Free trials/non-payment invoices for non-Stripe Checkout payment
			// forms do not have access to the SetupIntent.
			// @todo maybe set the object_id to null for consistent behavior?
		} elseif (
			'subscription' === $checkout_session->mode &&
			null !== $subscription &&
			null !== $checkout_session->setup_intent
		) {
			$object    = 'setup_intent';
			$object_id = $checkout_session->setup_intent;

			/** @var \SimplePay\Vendor\Stripe\PaymentMethod $payment_method */
			$payment_method      = $subscription->default_payment_method;
			$payment_method_type = $payment_method ? $payment_method->type : null;

			// Something else.
		} else {
			$object_id           = null;
			$object              = null;
			$payment_method_type = null;
		}

		/**
		 * @var \stdClass $default_totals
		 * @property int $amount_shipping
		 * @property int $amount_discount
		 * @property int $amount_tax
		 */
		$default_totals                  = new stdClass();
		$default_totals->amount_shipping = 0;
		$default_totals->amount_discount = 0;
		$default_totals->amount_tax      = 0;

		/**
		 * @var \stdClass $totals
		 * @property int $amount_shipping
		 * @property int $amount_discount
		 * @property int $amount_tax
		 */
		$totals = null !== $checkout_session->total_details
			? $checkout_session->total_details
			: $default_totals;

		$this->transactions->update(
			$transaction->id,
			array(
				'object'              => $object,
				'object_id'           => $object_id,
				'amount_total'        => $checkout_session->amount_total,
				'amount_subtotal'     => $checkout_session->amount_subtotal,
				'amount_shipping'     => $totals->amount_shipping,
				'amount_discount'     => $totals->amount_discount,
				'amount_tax'          => $totals->amount_tax,
				'payment_method_type' => $payment_method_type,
				'email'               => $customer->email,
				'customer_id'         => $customer->id,
				'subscription_id'     => $checkout_session->subscription,
				'status'              => 'succeeded',
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Updates a transaction's totals when viewing the [simpay_payment_receipt]
	 * shortcode in Lite, which cannot rely on webhooks.
	 *
	 * @since 4.4.6
	 *
	 * @param array<string, mixed> $payment_confirmation_data Payment confirmation data.
	 * @return void
	 */
	public function update_on_checkout_session_lite( $payment_confirmation_data ) {
		// Session ID is not available, do nothing.
		if ( ! isset( $_GET['session_id'] ) ) {
			return;
		}

		$session_id  = sanitize_text_field( $_GET['session_id'] );
		$transaction = $this->transactions->get_by_object_id( $session_id );

		// Transaction cannot be found, do nothing.
		if ( ! $transaction instanceof Transaction ) {
			return;
		}

		/** @var \SimplePay\Core\Abstracts\Form|false $form */
		$form = $payment_confirmation_data['form'];

		if ( false === $form ) {
			return;
		}

		try {
			$session = API\CheckoutSessions\retrieve(
				array(
					'id'     => $session_id,
					'expand' => array(
						'customer',
						'payment_intent',
					),
				),
				$form->get_api_request_args()
			);

			/** @var \SimplePay\Vendor\Stripe\Customer $customer */
			$customer = $session->customer;

			/** @var \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent */
			$payment_intent = $session->payment_intent;

			$object    = 'payment_intent';
			$object_id = $payment_intent->id;

			/**
			 * @var \stdClass $default_totals
			 * @property int $amount_shipping
			 * @property int $amount_discount
			 * @property int $amount_tax
			 */
			$default_totals                  = new stdClass();
			$default_totals->amount_shipping = 0;
			$default_totals->amount_discount = 0;
			$default_totals->amount_tax      = 0;

			/**
			 * @var \stdClass $totals
			 * @property int $amount_shipping
			 * @property int $amount_discount
			 * @property int $amount_tax
			 */
			$totals = null !== $session->total_details
				? $session->total_details
				: $default_totals;

			$this->transactions->update(
				$transaction->id,
				array(
					'object'              => $object,
					'object_id'           => $object_id,
					'amount_total'        => $session->amount_total,
					'amount_subtotal'     => $session->amount_subtotal,
					'amount_shipping'     => $totals->amount_shipping,
					'amount_discount'     => $totals->amount_discount,
					'amount_tax'          => $totals->amount_tax,
					'payment_method_type' => 'card',
					'email'               => $customer->email,
					'customer_id'         => $customer->id,
					'status'              => 'succeeded',
				)
			);
		} catch ( Exception $e ) {
			// Do nothing if Session cannot be found.
		}
	}

	/**
	 * Updates a transaction's totals when receiving the `invoice.payment_succeeded`
	 * webhook event, only for the first invoice created for a Subscription.
	 *
	 * Subsequent invoices are handled in `self::add_on_invoice()`.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Vendor\Stripe\Event        $event Event object.
	 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice Invoice object.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Subscription object.
	 * @return void
	 */
	public function update_on_invoice( $event, $invoice, $subscription ) {
		// Do nothing if this invoice event was not triggered by creating a Subscription.
		// A new record will be created instead for subsequent invoices.
		if ( 'subscription_create' !== $invoice->billing_reason ) {
			return;
		}

		$transaction = $this->transactions->get_by_object_id(
			$subscription->id
		);

		if ( ! $transaction instanceof Transaction ) {
			return;
		}

		// No trial, or there was a setup fee, so there is a charge.
		if ( null !== $invoice->payment_intent ) {
			$object    = 'payment_intent';
			$object_id = $invoice->payment_intent;

			// Trial and no setup fee.
			// Stripe doesn't create a SetupIntent or PaymentIntent if the first
			// invoice is free. Set to a setup_intent and do not link anything.
			// This creates slightly different behavior than trials with Stripe
			// Checkout, which do have an accessible SetupIntent.
		} else {
			$object    = 'setup_intent';
			$object_id = null;
		}

		if ( null === $invoice->total_discount_amounts ) {
			$total_discount = 0;
		} else {
			$total_discount = array_reduce(
				$invoice->total_discount_amounts,
				/**
				 * Adds a discount amount to the total discount.
				 *
				 * @since 4.4.6
				 *
				 * @param int       $total_discount Total discount, so far.
				 * @param \stdClass $discount Discount object.
				 */
				function ( $total_discount, $discount ) {
					/** @var \stdClass $discount */
					return $total_discount + $discount->amount;
				},
				0
			);
		}

		/** @var \SimplePay\Vendor\Stripe\PaymentMethod $payment_method */
		$payment_method      = $subscription->default_payment_method;
		$payment_method_type = $payment_method ? $payment_method->type : null;

		$this->transactions->update(
			$transaction->id,
			array(
				'object'              => $object,
				'object_id'           => $object_id,
				'amount_total'        => $invoice->total,
				'amount_subtotal'     => $invoice->subtotal,
				'amount_discount'     => $total_discount,
				'amount_tax'          => null === $invoice->tax
					? 0
					: $invoice->tax,
				'payment_method_type' => $payment_method_type,
				'email'               => $invoice->customer_email,
				'customer_id'         => $invoice->customer,
				'subscription_id'     => $invoice->subscription,
				'status'              => in_array(
					$subscription->status,
					array( 'active', 'trialing' ),
					true
				)
					? 'succeeded'
					: 'canceled',
				'application_fee'     => $this->application_fee->has_application_fee(),
			)
		);
	}

	/**
	 * Updates a transaction's status on `charge.failed`.
	 *
	 * @since 4.6.7
	 *
	 * @param \SimplePay\Vendor\Stripe\Event  $event Event object.
	 * @param \SimplePay\Vendor\Stripe\Charge $charge Charge object.
	 * @return void
	 */
	public function update_on_failed( $event, $charge ) {
		/** @var \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent */
		$payment_intent = $charge->payment_intent;

		/** @var \SimplePay\Vendor\Stripe\Invoice $invoice */
		$invoice = $charge->invoice;

		// Find from a Subscription.
		if ( $invoice && $invoice->subscription ) {
			/** @var \SimplePay\Vendor\Stripe\Subscription $subscription */
			$subscription   = $invoice->subscription;
			$transaction_id = $subscription->id;

			// Find from a one time payment.
		} else {
			$transaction_id = $payment_intent->id;
		}

		$transaction = $this->transactions->get_by_object_id( $transaction_id );

		if ( ! $transaction instanceof Transaction ) {
			return;
		}

		/**
		 * @var \stdClass $payment_method_details
		 * @property string $type
		 */
		$payment_method_details = $charge->payment_method_details;

		$this->transactions->update(
			$transaction->id,
			array(
				'object'              => 'payment_intent',
				'object_id'           => $payment_intent->id,
				'status'              => $charge->status,
				'payment_method_type' => $payment_method_details->type,
			)
		);
	}

	/**
	 * Possibly decremeents available stock/inventory if the price option requires it.
	 *
	 * @since 4.6.4
	 *
	 * @param stdClass                       $object Stripe object.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 * @param array<mixed>                   $form_data Form data generated by the client.
	 * @param array<mixed>                   $form_values Form values.
	 * @return void
	 */
	public function maybe_decrement_stock( $object, $form, $form_data, $form_values ) {
		if ( false === $form->is_managing_inventory() ) {
			return;
		}

		$behavior = $form->get_inventory_behavior();
		$prices   = $object->metadata->simpay_price_instances;
		$prices   = explode( '|', $prices );

		switch ( $behavior ) {
			case 'combined':
				$price_option       = current( $prices );
				$price_option_parts = explode( ':', $price_option );
				$quantity           = intval( $price_option_parts[1] );
				$form->adjust_inventory( 'decrement', $quantity, null );

				break;
			case 'individual':
				foreach ( $prices as $price_option ) {
					$price_option_parts = explode( ':', $price_option );
					$instance_id        = $price_option_parts[0];
					$quantity           = intval( $price_option_parts[1] );

					$form->adjust_inventory( 'decrement', $quantity, $instance_id );
				}

				break;
		}
	}

	/**
	 * Increments inventory when a payment fails to process.
	 *
	 * @since 4.6.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Event  $event Webhook event.
	 * @param \SimplePay\Vendor\Stripe\Charge $charge Charge object.
	 * @return void
	 */
	public function maybe_increment_stock( $event, $charge ) {
		// Subscription.
		if ( $charge->invoice ) {
			/** @var \SimplePay\Vendor\Stripe\Invoice $invoice Expanded by event receiver. */
			$invoice = $charge->invoice;

			if ( 'subscription_create' !== $invoice->billing_reason ) {
				return;
			}

			$object = $invoice->subscription;

			// One-time.
		} else {
			$object = $charge->payment_intent;
		}

		if ( ! isset( $object->metadata->simpay_form_id ) ) {
			return;
		}

		$form = simpay_get_form( $object->metadata->simpay_form_id );

		if ( false === $form ) {
			return;
		}

		if ( ! isset( $object->metadata->simpay_price_instances ) ) {
			return;
		}

		$prices = $object->metadata->simpay_price_instances;
		$prices = explode( '|', $prices );

		foreach ( $prices as $price_option ) {
			$price_option_parts = explode( ':', $price_option );
			$instance_id        = $price_option_parts[0];
			$quantity           = intval( $price_option_parts[1] );

			$form->adjust_inventory( 'increment', $quantity, $instance_id );
		}
	}
}
