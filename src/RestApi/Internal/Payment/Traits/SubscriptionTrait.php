<?php
/**
 * Subscription trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Traits;

use SimplePay\Core\API;
use SimplePay\Core\RestApi\Internal\Payment\Utils\FeeRecoveryUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\TaxUtils;

/**
 * SubscriptionTrait trait.
 *
 * @since 4.7.0
 */
trait SubscriptionTrait {

	/**
	 * Application fee handling.
	 *
	 * @since 4.7.0
	 *
	 * @var \SimplePay\Core\StripeConnect\ApplicationFee
	 */
	protected $application_fee;

	/**
	 * Creates a Subscription for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request                  $request The payment request.
	 * @param \SimplePay\Vendor\Stripe\Customer $customer The customer.
	 * @return \SimplePay\Vendor\Stripe\Subscription
	 */
	private function create_subscription( $request, $customer ) {
		$customer_id = $customer->id;
		$form        = PaymentRequestUtils::get_form( $request );
		$form_values = PaymentRequestUtils::get_form_values( $request );

		$subscription_args                      = $this->get_subscription_args( $request );
		$subscription_args['payment_behavior']  = 'default_incomplete';
		$subscription_args['customer']          = $customer_id;
		$subscription_args['items']             = $this->get_subscription_recurring_line_items( $request );
		$subscription_args['add_invoice_items'] = $this->get_subscription_additional_invoice_line_items( $request );
		$subscription_args['off_session']       = true;
		$subscription_args['payment_settings']  = array(
			'payment_method_types'        => PaymentRequestUtils::get_payment_method_types( $request ),
			'payment_method_options'      => PaymentRequestUtils::get_payment_method_options( $request ),
			'save_default_payment_method' => 'on_subscription',
		);
		$subscription_args['expand']            = array(
			'latest_invoice.payment_intent',
			'customer',
			'pending_setup_intent',
		);

		// Add the fee recovery line items, if needed.
		// Instead of running this in `self::get_subscription_recurring_line_items()`
		// and `self::get_subscription_additional_invoice_line_items()`, we run it here
		// so we have access to all of the line items at once.
		$is_covering_fees = PaymentRequestUtils::is_covering_fees( $request );

		if (
			$form->has_fee_recovery() &&
			( $form->has_forced_fee_recovery() || $is_covering_fees )
		) {
			$subscription_args = FeeRecoveryUtils::add_subscription_fee_recovery_line_items(
				$request,
				$subscription_args
			);
		}

		// Add tax rates to line items, if needed.

		/** @var array<string, mixed> $items */
		$items = $subscription_args['items'];

		$subscription_args['items'] = TaxUtils::add_tax_rates_to_line_items(
			$request,
			$items
		);

		/** @var array<string, mixed> $add_invoice_items */
		$add_invoice_items = $subscription_args['add_invoice_items'];

		$subscription_args['add_invoice_items'] = TaxUtils::add_tax_rates_to_line_items(
			$request,
			$add_invoice_items
		);

		// Add automatic tax collection, if needed.
		$subscription_args = TaxUtils::add_automatic_tax_args(
			$request,
			$subscription_args
		);

		// Add the application fee, if needed.
		if ( $this->application_fee->has_application_fee() ) {
			$subscription_args['application_fee_percent'] =
				$this->application_fee->get_application_fee_percentage();
		}

		/**
		 * Filters arguments used to generate a Subscription from a payment form request.
		 *
		 * @since 3.6.0
		 *
		 * @param array<string, mixed>           $subscription_args Subscription arguments.
		 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array<mixed>                   $arg2 Deprecated.
		 * @param array<string, mixed>           $form_values Form values.
		 * @param string                         $customer Customer ID.
		 * @return array<string, mixed>
		 */
		$subscription_args = apply_filters(
			'simpay_get_subscription_args_from_payment_form_request',
			$subscription_args,
			$form,
			array(),
			$form_values,
			$customer_id
		);

		/**
		 * Allow further processing before a Subscription is created from a posted form.
		 *
		 * @since 3.6.0
		 *
		 * @param array<string, mixed>           $subscription_args Subscription arguments.
		 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array<mixed>                   $arg2 Deprecated.
		 * @param array<string, mixed>           $form_values Form values.
		 * @param string                         $customer Customer ID.
		 */
		do_action(
			'simpay_before_subscription_from_payment_form_request',
			$subscription_args,
			$form,
			array(),
			$form_values,
			$customer_id
		);

		$subscription = API\Subscriptions\create(
			$subscription_args,
			$form->get_api_request_args()
		);

		/**
		 * Allow further processing after a Subscription is created from a posted form.
		 *
		 * @since 3.6.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Subscription..
		 * @param \SimplePay\Core\Abstracts\Form        $form Form instance.
		 * @param array<mixed>                          $form_data Deprecated.
		 * @param array<string, mixed>                  $form_values Form values.
		 * @param string                                $customer Customer ID.
		 */
		do_action(
			'simpay_after_subscription_from_payment_form_request',
			$subscription,
			$form,
			array(),
			$form_values,
			$customer_id
		);

		return $subscription;
	}

	/**
	 * Returns data for a Subscription for the given request.
	 *
	 * This is generic data that applies to a base PaymentIntent, regardless
	 * of what creates it (Checkout Session, etc). Additional arguments used
	 * just for Stripe Billing are added in `SubscriptionTrait::create_subscription()`.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<string, string|array<string, mixed>>
	 */
	private function get_subscription_args( $request ) {
		$price             = PaymentRequestUtils::get_price( $request );
		$form              = PaymentRequestUtils::get_form( $request );
		$subscription_data = array();

		// Set the trial period, if needed.
		if ( $form->allows_multiple_line_items() ) {
			$trial_period_days = PaymentRequestUtils::get_trial_period_days_from_price_ids( $request );
			if ( $trial_period_days > 0 ) {
				$subscription_data['trial_period_days'] = $trial_period_days;
			}
		} elseif ( $price->recurring && isset( $price->recurring['trial_period_days'] ) ) {
				$subscription_data['trial_period_days'] = $price->recurring['trial_period_days'];
		}

		// Set the metadata with a combination of the standard payment metadata and
		// additional subscription metadata.
		$subscription_data['metadata'] = array_merge(
			PaymentRequestUtils::get_payment_metadata( $request ),
			array(
				'simpay_subscription_key' => $this->get_subscription_key(),
			)
		);

		// Add invoice limit metadata, if needed.
		$max_charges = isset( $price->recurring['invoice_limit'] )
			? $price->recurring['invoice_limit']
			: 0;

		if ( 0 !== $max_charges ) {
			$charge_count = isset( $price->recurring['trial_period_days'] ) ? -1 : 0;

			$subscription_data['metadata']['simpay_charge_max']   = $max_charges;
			$subscription_data['metadata']['simpay_charge_count'] = $charge_count;
		}

		return $subscription_data;
	}

	/**
	 * Returns recurring line items for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_subscription_recurring_line_items( $request ) {
		$line_items    = array();
		$form          = PaymentRequestUtils::get_form( $request );
		$price         = PaymentRequestUtils::get_price( $request );
		$quantity      = PaymentRequestUtils::get_quantity( $request );
		$custom_amount = PaymentRequestUtils::get_custom_unit_amount( $request );
		$tax_status    = get_post_meta( $form->id, '_tax_status', true );
		$tax_behavior  = get_post_meta( $form->id, '_tax_behavior', true );

		// If multiple line items are allowed, then add them.
		if ( $form->allows_multiple_line_items() ) {
			$prices      = PaymentRequestUtils::get_price_ids( $request );
			$price_items = array();

			foreach ( $prices as $price ) {
				// Skip one-time prices. These are added to the first invoice only
				// via `self::get_subscription_additional_invoice_line_items()`.
				if ( ! PaymentRequestUtils::is_recurring( $request, $price ) ) {
					continue;
				}

				$price_item = array(
					'quantity' => $price['quantity'],
					'metadata' => array(
						'simpay_price_instance_id' => $price['price_data']['instance_id'],
					),
				);

				if (
					! is_null( $price['price_data']['recurring'] ) &&
					isset(
						$price['price_data']['recurring']['interval'],
						$price['price_data']['recurring']['interval_count']
					) &&
					(
						! simpay_payment_form_prices_is_defined_price( $price['price_id'] ) ||
						$price['is_optionally_recurring']
					)
				) {
					$price_item['price_data'] = array(
						'unit_amount'  => $price['custom_amount'],
						'currency'     => $price['price_data']['currency'],
						'product'      => $price['price_data']['product_id'],
						'recurring'    => array(
							'interval'       => $price['price_data']['recurring']['interval'],
							'interval_count' => $price['price_data']['recurring']['interval_count'],
						),
						'tax_behavior' => 'automatic' === $tax_status
							? $tax_behavior
							: 'unspecified',
					);

				} else {
					$price_item['price'] = $price['price_id'];
				}

				$price_items[] = $price_item;
			}

			return $price_items;
		}

		// Otherwise add a single line item.
		$base_item = array(
			'quantity' => $quantity,
			'metadata' => array(
				'simpay_price_instance_id' => $price->instance_id,
			),
		);

		$custom_price_data = array(
			'unit_amount'  => $custom_amount,
			'currency'     => $price->currency,
			'recurring'    => array(
				'interval'       => $price->recurring['interval'],
				'interval_count' => $price->recurring['interval_count'],
			),
			'product'      => $price->product_id,
			'tax_behavior' => 'automatic' === $tax_status
				? $tax_behavior
				: 'unspecified',
		);

		// Set the base line item price when optionally recurring.
		if ( true === PaymentRequestUtils::is_optionally_recurring( $request ) ) {
			// Optional recurring option is a defined price.
			if (
				isset( $price->recurring['id'] ) &&
				true === simpay_payment_form_prices_is_defined_price(
					$price->recurring['id']
				)
			) {
				$base_item['price'] = $price->recurring['id'];

				// Optional recurring option is a custom amount.
			} else {
				$base_item['price_data'] = $custom_price_data;
			}

			// Always recurring custom amount.
		} elseif ( false === simpay_payment_form_prices_is_defined_price( $price->id ) ) {
			$base_item['price_data'] = $custom_price_data;

			// Always recurring defined price.
		} else {
			$base_item['price'] = $price->id;
		}

		// If this subscription is being created for Stripe Checkout, then check
		// if quantity adjustment is allowed.
		if ( 'stripe_checkout' === $form->get_display_type() ) {
			$enable_quantity = 'yes' === simpay_get_saved_meta(
				$form->id,
				'_enable_quantity',
				'no'
			);

			if ( $enable_quantity ) {
				$base_item['adjustable_quantity'] = array(
					'enabled' => true,
					'minimum' => 1,
				);
			}
		}

		$line_items[] = $base_item;

		return $line_items;
	}

	/**
	 * Returns one-time line items (first invoice only) for the given request.
	 *
	 * Currently this is used for a recurring price option's setup fee(s).
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_subscription_additional_invoice_line_items( $request ) {
		$form = PaymentRequestUtils::get_form( $request );

		// If multiple line items are allowed, add non-recurring price options, or fees.
		if ( $form->allows_multiple_line_items() ) {
			$prices      = PaymentRequestUtils::get_price_ids( $request );
			$price_items = array();

			foreach ( $prices as $price ) {
				// If the price is recurring, look for Setup Fees.
				if ( PaymentRequestUtils::is_recurring( $request, $price ) ) {

					if (
						isset( $price['price_data']['line_items'] ) &&
						! empty( $price['price_data']['line_items'] )
					) {
						foreach ( $price['price_data']['line_items']  as $fees ) {
							$price_items[] = array(
								'price_data' => array(
									'unit_amount' => $fees['unit_amount'],
									'currency'    => $price['price_data']['currency'],
									'product'     => $price['price_data']['product_id'],
								),
								'quantity'   => 1,
							);
						}
					}

					// Otherwise, skip.
					continue;
				}

				$price_item = array(
					'quantity' => $price['quantity'],
				);

				if ( ! simpay_payment_form_prices_is_defined_price( $price['price_id'] ) ) {
					$price_item['price_data'] = array(
						'unit_amount' => $price['custom_amount'],
						'currency'    => $price['price_data']['currency'],
						'product'     => $price['price_data']['product_id'],
					);
				} else {
					$price_item['price'] = $price['price_id'];
				}

				$price_items[] = $price_item;
			}

			return $price_items;
		}

		$line_items = array();

		// Add a line item for the legacy per-plan fee, if needed.
		$plan_fee = $this->get_subscription_additional_invoice_line_item(
			$request,
			'plan'
		);

		if ( ! empty( $plan_fee ) ) {
			$line_items[] = $plan_fee;
		}

		// Add a line item for the initial setup fee, if needed.
		$setup_fee = $this->get_subscription_additional_invoice_line_item(
			$request,
			'setup'
		);

		if ( ! empty( $setup_fee ) ) {
			$line_items[] = $setup_fee;
		}

		return $line_items;
	}

	/**
	 * Returns a one-time line item (first invoice only) for the given request and type.
	 *
	 * This is a helper method to make it easier to create a legacy "plan fee"
	 * alongside the still-supported "setup fee".
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $type    The type of line item to return. Either "plan" or "setup".
	 * @return array<string, mixed>
	 */
	private function get_subscription_additional_invoice_line_item( $request, $type ) {
		$form         = PaymentRequestUtils::get_form( $request );
		$price        = PaymentRequestUtils::get_price( $request );
		$tax_status   = get_post_meta( $form->id, '_tax_status', true );
		$tax_behavior = get_post_meta( $form->id, '_tax_behavior', true );
		$line_item    = array();

		// Fetch price option line items.
		$price_line_items = ! empty( $price->line_items )
			? $price->line_items
			: array();

		if ( empty( $price_line_items ) ) {
			return $line_item;
		}

		// Find the line item for the given type.
		$line_item_index = 'plan' === $type ? 1 : 0;
		$price_line_item = isset( $price_line_items[ $line_item_index ] )
			? $price_line_items[ $line_item_index ]
			: array();

		if ( empty( $price_line_item ) ) {
			return $line_item;
		}

		$unit_amount = $price_line_item['unit_amount'];
		$currency    = $price->currency;

		if ( 0 === $unit_amount ) {
			return $line_item;
		}

		$line_item_args = array(
			'quantity'   => 1,
			'price_data' => array(
				'unit_amount'  => $unit_amount,
				'currency'     => $currency,
				'tax_behavior' => 'automatic' === $tax_status
					? $tax_behavior
					: 'unspecified',
			),
		);

		// Use a dynamically created Product with Stripe Checkout for better
		// line item naming since it is displayed on the Stripe Checkout page.
		if ( 'stripe_checkout' === $form->get_display_type() ) {
			$line_item_args['price_data']['product_data'] = array(
				'name' => 'plan' === $type
					? __( 'Plan Setup Fee', 'stripe' )
					: __( 'Initial Setup Fee', 'stripe' ),
			);

			// Set the dynamic parent product's tax information, if needed.
			$tax_status   = get_post_meta( $form->id, '_tax_status', true );
			$tax_code     = get_post_meta( $form->id, '_tax_code', true );
			$tax_behavior = get_post_meta( $form->id, '_tax_behavior', true );

			if ( 'automatic' === $tax_status ) {
				$line_item_args['price_data']['tax_behavior']             = $tax_behavior;
				$line_item_args['price_data']['product_data']['tax_code'] = $tax_code;
			}

			// Otherwise set an existing Product.
		} else {
			$line_item_args['price_data']['product'] = $price->product_id;
		}

		$filter_name = 'plan' === $type
		? 'simpay_get_plan_setup_fee_args_from_payment_form_request'
		: 'simpay_get_setup_fee_args_from_payment_form_request';

		/**
		 * Filters the arguments used to create the additional line item.
		 *
		 * @since 3.6.0
		 *
		 * @param array<string, mixed>           $plan_fee_args Arguments used to create the InvoiceItem.
		 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array                          $form_data Form data generated by the client.
		 * @param array                          $form_values Values of named fields in the payment form.
		 * @param string                         $customer_id Stripe Customer ID.
		 */
		$line_item_args = apply_filters(
			$filter_name,
			$line_item_args,
			$form,
			array(),
			PaymentRequestUtils::get_form_values( $request ),
			''
		);

		return $line_item_args;
	}

	/**
	 * Returns a unique subscription key.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	private function get_subscription_key() {
		$auth_key         = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$hash             = date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'wpsp', true ); // @phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$subscription_key = strtolower( md5( $hash ) );

		return $subscription_key;
	}
}
