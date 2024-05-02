<?php
/**
 * Payment update route
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment;

use Exception;
use SimplePay\Core\API;
use SimplePay\Core\Utils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\FeeRecoveryUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\SchemaUtils;
use WP_REST_Response;
use WP_REST_Server;

/**
 * PaymentUpdateRoute class.
 *
 * @since 4.7.0
 */
class PaymentUpdateRoute extends AbstractPaymentRoute {

	use Traits\SubscriptionTrait;

	/**
	 * Registers the `POST /wpsp/__internal__/payment/update` route.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function register_route() {
		$update_args = array(
			'form_id'             => SchemaUtils::get_form_id_schema(),
			'object_id'           => SchemaUtils::get_object_id_schema(),
			'customer_id'         => SchemaUtils::get_customer_id_schema(),
			'price_id'            => SchemaUtils::get_price_id_schema(),
			'custom_amount'       => SchemaUtils::get_custom_amount_schema(),
			'payment_method_type' => SchemaUtils::get_payment_method_type_schema(),
			'is_covering_fees'    => SchemaUtils::get_is_covering_fees_schema(),
		);

		$update_item_route = array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_payment' ),
			'permission_callback' => array( $this, 'update_payment_permissions_check' ),
			'args'                => $update_args,
		);

		register_rest_route(
			$this->namespace,
			'payment/update',
			$update_item_route
		);
	}

	/**
	 * Determines if the current request should be able to create a payment.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	public function update_payment_permissions_check( $request ) {
		return true;
	}

	/**
	 * Updates a payment amount when the payment method changes, to account for
	 * new fee recovery amounts.
	 *
	 * This works by retrieving the payment intent or subscription, and then
	 * using the stored sipmay_fee_recovery_unit_amount metadata to calculate
	 * the original price so we can add the new fee recovery amount.
	 *
	 * With payment intents this is done simply by updating the existing payment intent.
	 * With subscriptions the finalized (but not collected) invoice must be voided and
	 * a new subscription created (voiding the first invoice cancels the subscription).
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return \WP_REST_Response The response object.
	 * @throws \Exception If the payment does not need to be updated.
	 */
	public function update_payment( $request ) {
		$form = PaymentRequestUtils::get_form( $request );

		/** @var string $object_id */
		$object_id = $request->get_param( 'object_id' );

		/** @var string $customer_id */
		$customer_id = $request->get_param( 'customer_id' );

		/** @var bool $is_covering_fees */
		$is_covering_fees = $request->get_param( 'is_covering_fees' );

		try {
			// Ensure fee recovery is enabled on at least one payment method, and
			// if fee recovery is optional, that it is opted-in.
			if (
				! $form->has_fee_recovery() ||
				( ! $form->has_forced_fee_recovery() && ! $is_covering_fees )
			) {
				throw new Exception(
					__( 'Invalid request. Please try again', 'stripe' )
				);
			}

			// If the payment ID starts with pi_, then it is a PaymentIntent, retrieve it.
			if ( 0 === strpos( $object_id, 'pi_' ) ) {
				$object = API\PaymentIntents\retrieve(
					array(
						'id'     => $object_id,
						'expand' => array(
							'customer',
						),
					),
					$form->get_api_request_args()
				);

				$object_type = 'payment_intent';

				// If it starts with sub_, then it is a Subscription, retrieve it.
			} elseif ( 0 === strpos( $object_id, 'sub_' ) ) {
				$object = API\Subscriptions\retrieve(
					array(
						'id'     => $object_id,
						'expand' => array(
							'latest_invoice.payment_intent',
							'customer',
							'pending_setup_intent',
						),
					),
					$form->get_api_request_args()
				);

				$object_type = 'subscription';
			} else {
				throw new Exception(
					__( 'Invalid request. Please try again', 'stripe' )
				);
			}

			// Double check that the customer ID in the request matches the customer ID on the object.
			/** @var \SimplePay\Vendor\Stripe\Customer $customer */
			$customer = $object->customer;

			if ( $customer->id !== $customer_id ) {
				throw new Exception(
					__( 'Invalid request. Please try again', 'stripe' )
				);
			}

			// Objects can only be updated if they were created with fee recovery.
			/** @var \SimplePay\Vendor\Stripe\StripeObject $metadata */
			$metadata = $object->metadata;

			if ( ! isset( $metadata->simpay_fee_recovery_unit_amount ) ) {
				throw new Exception(
					__( 'Invalid request. Please try again', 'stripe' )
				);
			}

			// Create a new object and return a relevant intent.
			switch ( $object_type ) {
				case 'payment_intent':
					/** @var \SimplePay\Vendor\Stripe\PaymentIntent $object */
					$intent    = $this->update_payment_intent( $request, $object );
					$object_id = $intent->id;

					break;
				case 'subscription':
					/** @var \SimplePay\Vendor\Stripe\Subscription $object */
					$subscription = $this->update_subscription( $request, $object );
					$object_id    = $subscription->id;

					if ( isset( $subscription->pending_setup_intent ) ) {
						$intent = $subscription->pending_setup_intent;
					} else {
						/** @var \SimplePay\Vendor\Stripe\Invoice $invoice */
						$invoice = $subscription->latest_invoice;
						$intent  = $invoice->payment_intent;
					}
					break;
				default:
					throw new Exception(
						__( 'Invalid request. Please try again', 'stripe' )
					);
			}

			/** @var \SimplePay\Vendor\Stripe\PaymentIntent $intent */

			return new WP_REST_Response(
				array(
					'object_id'     => $object_id,
					'client_secret' => $intent->client_secret,
					'changed'       => $object->id !== $object_id,
				)
			);
		} catch ( Exception $e ) {
			return new WP_REST_Response(
				array(
					'message' => Utils\handle_exception_message( $e ),
				),
				400
			);
		}
	}

	/**
	 * Updates a payment intent's amount to include the fee recovery amount
	 * for the current payment method.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request                       $request The payment request.
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent Payment intent.
	 * @return \SimplePay\Vendor\Stripe\PaymentIntent
	 */
	private function update_payment_intent( $request, $payment_intent ) {
		/** @var \SimplePay\Vendor\Stripe\StripeObject $metadata */
		$metadata = $payment_intent->metadata;

		if ( ! isset( $metadata->simpay_fee_recovery_unit_amount ) ) {
			return $payment_intent;
		}

		$form                    = PaymentRequestUtils::get_form( $request );
		$fee_recovery_amount     = intval(
			$metadata->simpay_fee_recovery_unit_amount
		);
		$total_amount            = intval( $payment_intent->amount );
		$base_amount             = $total_amount - $fee_recovery_amount;
		$new_fee_recovery_amount = FeeRecoveryUtils::get_fee_recovery_unit_amount(
			$request,
			$base_amount
		);

		// Do not update the intent if the recovery amount has not changed.
		if ( (int) $fee_recovery_amount === (int) $new_fee_recovery_amount ) {
			return $payment_intent;
		}

		return API\PaymentIntents\update(
			$payment_intent->id,
			array(
				'amount'   => $base_amount + $new_fee_recovery_amount,
				'metadata' => array(
					'simpay_fee_recovery_unit_amount' => $new_fee_recovery_amount,
				),
				'expand'   => array(
					'customer',
				),
			),
			$form->get_api_request_args()
		);
	}

	/**
	 * Updates a subscription's initial amount to include the fee recovery amount
	 * for the current payment method.
	 *
	 * To to this we must gather the line items from the current subscription object
	 * and discard the line item that matches the amount of the recorded fee recovery
	 * amount. Then, we void the initial invoice which in turn cancels the current
	 * incomplete subscription. Finally, we create a new subscription with the new
	 * fee recovery line item and the remaining line items from the original subscription.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request                      $request The payment request.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Subscription.
	 * @return \SimplePay\Vendor\Stripe\Subscription
	 */
	private function update_subscription( $request, $subscription ) {
		$form    = PaymentRequestUtils::get_form( $request );
		$price   = PaymentRequestUtils::get_price( $request );
		$invoice = $subscription->latest_invoice;

		// Copy the base arguments from the current subscription.
		/** @var \SimplePay\Vendor\Stripe\StripeObject $metadata */
		$metadata = $subscription->metadata;
		$metadata = $metadata->toArray();

		$original_metadata = $metadata;
		unset( $metadata['simpay_fee_recovery_unit_amount'] );
		unset( $metadata['simpay_subscription_key'] );

		/** @var \SimplePay\Vendor\Stripe\StripeObject $automatic_tax */
		$automatic_tax = $subscription->automatic_tax;

		/** @var \SimplePay\Vendor\Stripe\StripeObject $payment_settings */
		$payment_settings = $subscription->payment_settings;

		$new_subscription_args = array(
			'customer'          => $subscription->customer,
			'metadata'          => array_merge(
				$metadata,
				array(
					'simpay_subscription_key' => $this->get_subscription_key(),
				)
			),
			'trial_period_days' => $price->recurring && isset( $price->recurring['trial_period_days'] )
				? $price->recurring['trial_period_days']
				: null,
			'automatic_tax'     => $automatic_tax->toArray(),
			'payment_behavior'  => 'default_incomplete',
			'off_session'       => true,
			'payment_settings'  => $payment_settings->toArray(),
			'expand'            => array(
				'latest_invoice.payment_intent',
				'customer',
				'pending_setup_intent',
			),
		);

		// Find the non-fee recovery line items from the current subscription.
		$setup_fee_amounts = ! empty( $price->line_items )
			? wp_list_pluck( $price->line_items, 'unit_amount' )
			: array();

		$price_amounts = array_merge(
			array( PaymentRequestUtils::get_unit_amount( $request ) ),
			$setup_fee_amounts
		);

		/** @var \SimplePay\Vendor\Stripe\Invoice $invoice */

		$non_fee_recovery_line_items = array_filter(
			$invoice->lines->data,
			function ( $line_item ) use ( $price_amounts ) {
				return in_array( $line_item->amount, $price_amounts, true );
			}
		);

		$one_time_line_items = array_filter(
			$non_fee_recovery_line_items,
			function ( $line_item ) {
				/** @var \SimplePay\Vendor\Stripe\Price $price */
				$price = $line_item->price;

				return 'one_time' === $price->type;
			}
		);

		$one_time_line_items = array_map(
			array( $this, 'recreate_line_item' ),
			$one_time_line_items
		);

		$recurring_line_items = array_filter(
			$non_fee_recovery_line_items,
			function ( $line_item ) {
				/** @var \SimplePay\Vendor\Stripe\Price $price */
				$price = $line_item->price;

				return 'recurring' === $price->type;
			}
		);

		$recurring_line_items = array_map(
			array( $this, 'recreate_line_item' ),
			$recurring_line_items
		);

		// Add the found line items to the new subscription arguments.
		$new_subscription_args['items'] = array_values(
			$recurring_line_items
		);

		$new_subscription_args['add_invoice_items'] = array_values(
			$one_time_line_items
		);

		// Add the fee recovery line items, if needed.
		// Instead of running this in `self::get_subscription_recurring_line_items()`
		// and `self::get_subscription_additional_invoice_line_items()`, we run it here
		// so we have access to all of the line items at once.
		$new_subscription_args = FeeRecoveryUtils::add_subscription_fee_recovery_line_items(
			$request,
			$new_subscription_args
		);

		// Do not update void and recreate a subscription if the fee recovery amount has not changed.

		/** @var int $original_fee_recovery_amount */
		$original_fee_recovery_amount = $original_metadata['simpay_fee_recovery_unit_amount'];

		/** @var array<string, int> $metadata */
		$metadata                = $new_subscription_args['metadata'];
		$new_fee_recovery_amount = $metadata['simpay_fee_recovery_unit_amount'];

		if ( (int) $original_fee_recovery_amount === (int) $new_fee_recovery_amount ) {
			return $subscription;
		}

		// Void the previous Invoice.
		$invoice->voidInvoice( $invoice->id, $form->get_api_request_args() ); // @phpstan-ignore-line

		// Return the new Subscription.
		return API\Subscriptions\create(
			$new_subscription_args,
			$form->get_api_request_args()
		);
	}

	/**
	 * Returns line item data from a Stripe line item object that can be used to
	 * create a new line item.
	 *
	 * @since 4.7.0
	 *
	 * @param \SimplePay\Vendor\Stripe\InvoiceLineItem $line_item Line item object.
	 * @return array<string, mixed> Line item data.
	 */
	private function recreate_line_item( $line_item ) {
		/** @var \SimplePay\Vendor\Stripe\Price $price */
		$price = $line_item->price;

		if ( $price->active ) {
			return array(
				'price'    => $price->id,
				'quantity' => $line_item->quantity,
			);
		}

		return array(
			'quantity'   => $line_item->quantity,
			'price_data' => array(
				'unit_amount' => $line_item->amount,
				'currency'    => $line_item->currency,
				'product'     => $price->product,
				'recurring'   => isset( $price->recurring )
					? array(
						'interval'       => isset( $price->recurring->interval )
							? $price->recurring->interval
							: null,
						'interval_count' => isset( $price->recurring->interval_count )
							? $price->recurring->interval_count
							: null,
					)
					: null,
			),
		);
	}

}
