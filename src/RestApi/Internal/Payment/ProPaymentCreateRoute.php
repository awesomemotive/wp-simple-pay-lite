<?php
/**
 * Pro Payment creation route
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment;

use Exception;
use SimplePay\Core\Utils;
use SimplePay\Core\RestApi\Internal\Payment\Exception\ValidationException;
use SimplePay\Core\RestApi\Internal\Payment\Utils\CouponUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\SchemaUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\TaxUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\TokenValidationUtils;
use SimplePay\Vendor\Stripe\Customer;
use WP_REST_Response;
use WP_REST_Server;

/**
 * ProPaymentCreateRoute class.
 *
 * @since 4.7.0
 */
class ProPaymentCreateRoute extends LitePaymentCreateRoute {

	use Traits\CheckoutSessionTrait;
	use Traits\CustomerTrait;
	use Traits\PaymentIntentTrait;
	use Traits\SubscriptionTrait;

	/**
	 * Registers the `POST /wpsp/__internal__/payment/create` route.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function register_route() {
		$create_args = array(
			'form_id'                 => SchemaUtils::get_form_id_schema(),
			'form_values'             => SchemaUtils::get_form_values_schema(),
			'price_id'                => SchemaUtils::get_price_id_schema(),
			'quantity'                => SchemaUtils::get_quantity_schema(),
			'custom_amount'           => SchemaUtils::get_custom_amount_schema(),
			'payment_method_type'     => SchemaUtils::get_payment_method_type_schema(),
			'is_optionally_recurring' => SchemaUtils::get_is_optionally_recurring_schema(),
			'is_covering_fees'        => SchemaUtils::get_is_covering_fees_schema(),
			'coupon_code'             => SchemaUtils::get_coupon_code_schema(),
			'billing_address'         => SchemaUtils::get_billing_address_schema(),
			'shipping_address'        => SchemaUtils::get_shipping_address_schema(),
			'token'                   => SchemaUtils::get_token_schema(),
		);

		$create_item_route = array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_payment' ),
			'permission_callback' => array( $this, 'create_payment_permissions_check' ),
			'args'                => $create_args,
		);

		register_rest_route(
			$this->namespace,
			$this->route,
			$create_item_route
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Exception If the payment could not be created.
	 * @throws \Simplepay\Core\RestApi\Internal\Payment\Exception\ValidationException If a validation error occurs.
	 */
	public function create_payment( $request ) {
		try {
			// Check rate limit.
			// This is done here to avoid double increments (in authorization callback)
			// and more human-friendly error messages.
			if ( false === $this->validate_rate_limit( $request ) ) {
				throw new ValidationException(
					__(
						'Sorry, you have made too many requests. Please try again later.',
						'stripe'
					)
				);
			}

			/**
			 * Allows processing before a payment is created.
			 *
			 * @since 4.7.x
			 *
			 * @param \WP_REST_Request $request The request object.
			 */
			do_action( 'simpay_before_payment_create', $request );

			// Check form token.
			// This is done here to avoid double increments (in authorization callback)
			// and more human-friendly error messages.
			if ( false === TokenValidationUtils::validate_token( $request ) ) {
				throw new ValidationException(
					__( 'Invalid CAPTCHA. Please try again.', 'stripe' )
				);
			}

			// Add a Customer, if needed.
			$customer = $this->get_customer( $request );

			// Handle the payment based on the form type.
			$form = PaymentRequestUtils::get_form( $request );

			// Handle an off-site Checkout Session form.
			if ( 'stripe_checkout' === $form->get_display_type() ) {
				$payment = $this->create_checkout_session(
					$request,
					$this->get_checkout_session_args( $request, $customer )
				);

				return new WP_REST_Response(
					array(
						'redirect' => $payment->url,
					)
				);

				// Handle an on-site Elements form.
			} else {
				if ( ! $customer instanceof Customer ) {
					throw new Exception(
						__( 'Invalid request. Please try again.', 'stripe' )
					);
				}

				if ( PaymentRequestUtils::is_recurring( $request ) ) {
					$object = $this->create_subscription( $request, $customer );

					if ( isset( $object->pending_setup_intent ) ) {
						$intent = $object->pending_setup_intent;
					} else {
						/** @var \SimplePay\Vendor\Stripe\Invoice $latest_invoice */
						$latest_invoice = $object->latest_invoice;
						$intent         = $latest_invoice->payment_intent;
					}
				} else {
					$object = $this->create_payment_intent( $request, $customer );
					$intent = $object;
				}

				/** @var \SimplePay\Vendor\Stripe\PaymentIntent|\SimplePay\Vendor\Stripe\SetupIntent $intent */

				return new WP_REST_Response(
					array(
						// Send back the parent object ID. This is may be used
						// in subsequent requests to retrieve the object and
						// update the payment intent being used.
						'object_id'     => $object->id,
						'customer_id'   => $customer->id,
						'client_secret' => $intent->client_secret,
						'return_url'    => esc_url(
							add_query_arg(
								array(
									'customer_id' => $customer->id,
								),
								$form->payment_success_page
							)
						),
					)
				);
			}
		} catch ( ValidationException $e ) {
			return new WP_REST_Response(
				array(
					'message' => Utils\handle_exception_message( $e ),
				),
				rest_authorization_required_code()
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
	 * Overrides the Checkout Session arguments used in Lite, adding additional
	 * for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request                       $request The payment request.
	 * @param \SimplePay\Vendor\Stripe\Customer|null $customer Customer, if one was created.
	 * @return array<string, mixed>
	 * @throws \Exception If the custom amount is not valid.
	 */
	protected function get_checkout_session_args( $request, $customer ) {
		$form         = PaymentRequestUtils::get_form( $request );
		$tax_status   = get_post_meta( $form->id, '_tax_status', true );
		$tax_behavior = get_post_meta( $form->id, '_tax_behavior', true );

		// Pull the base Checkout Session arguments used in Lite.
		$session_args = parent::get_checkout_session_args( $request, $customer );

		// Remove custom fields from the Checkout Session arguments as those fields
		// are collected on-site in Pro.
		unset( $session_args['custom_fields'] );

		// Update the application fee to account for discounts and taxes, if needed.
		if ( isset(
			$session_args['payment_intent_data'],
			$session_args['payment_intent_data']['application_fee_amount'] // @phpstan-ignore-line
		) ) {
			$unit_amount = PaymentRequestUtils::get_unit_amount( $request );

			// Remove the coupon amount, if needed.
			$discount = CouponUtils::get_discount_unit_amount(
				$request,
				$unit_amount,
				$customer ? $customer->id : null
			);

			if ( 0 !== $discount ) {
				$unit_amount = $unit_amount - $discount;
			}

			// Add the tax amount, if needed.
			$tax = TaxUtils::get_tax_unit_amount( $request, $unit_amount );

			if ( 0 !== $tax ) {
				// Automatic tax, and exclusive, so add the amount.
				if ( 'automatic' === $tax_status && 'exclusive' === $tax_behavior ) {
					$unit_amount = $unit_amount + $tax;

					// Fixed global, add the amount (accounts for inclusive) in calculatinos.
				} elseif ( empty( $tax_behavior ) || 'fixed-global' === $tax_behavior ) {
					$unit_amount = $unit_amount + $tax;
				}
			}

			$session_args['payment_intent_data']['application_fee_amount'] =
				$this->application_fee->get_application_fee_amount( $unit_amount );
		}

		// Add the customer, if needed.
		if ( $customer instanceof Customer ) {
			$session_args['customer']        = $customer->id;
			$session_args['customer_update'] = array(
				'name'     => 'auto',
				'address'  => 'auto',
				'shipping' => 'auto',
			);

			// You may only specify one of these parameters: customer, customer_creation.
			unset( $session_args['customer_creation'] );
		}

		// Merge in additional top level metadata.
		$session_args['metadata'] = array_merge(
			$session_args['metadata'], // @phpstan-ignore-line
			$session_args['payment_intent_data']['metadata'] // @phpstan-ignore-line
		);

		// Set the allowed payment method types.
		$session_args['payment_method_types'] = PaymentRequestUtils::get_payment_method_types(
			$request
		);

		// Set the payment method options.
		$session_args['payment_method_options'] = PaymentRequestUtils::get_payment_method_options(
			$request
		);

		// Add a discount, if needed.
		$coupon = PaymentRequestUtils::get_coupon_code( $request );

		if ( ! empty( $coupon ) ) {
			$session_args['discounts'] = array(
				array(
					'coupon' => $coupon,
				),
			);
		}

		// Allow promotion codes, if needed.
		$enable_coupons = 'yes' === simpay_get_saved_meta(
			$form->id,
			'_enable_promotion_codes',
			'no'
		);

		if ( true === $enable_coupons && empty( $coupon ) ) {
			$session_args['allow_promotion_codes'] = true;
		}

		// Collect the Customer Tax ID, if needed.
		$enable_tax_id = 'yes' === simpay_get_saved_meta(
			$form->id,
			'_enable_tax_id',
			'no'
		);

		if ( true === $enable_tax_id ) {
			$session_args['tax_id_collection'] = array(
				'enabled' => true,
			);
		}

		// Update the line items, if needed (due to a custom amount).
		$price = PaymentRequestUtils::get_price( $request );

		if ( false === simpay_payment_form_prices_is_defined_price( $price->id ) ) {
			$custom_amount = PaymentRequestUtils::get_custom_unit_amount( $request );

			$price_data = array(
				'currency'    => $price->currency,
				'unit_amount' => $custom_amount,
				'product'     => $price->product_id,
			);

			// Remove defined price and add custom price data.
			unset( $session_args['line_items'][0]['price'] ); // @phpstan-ignore-line
			$session_args['line_items'][0]['price_data'] = $price_data; // @phpstan-ignore-line
		}

		// Make adjustments to the arguments if a subscription is being created.
		if ( PaymentRequestUtils::is_recurring( $request ) ) {
			// Remove unsupported arguments.
			unset( $session_args['payment_intent_data'] );
			unset( $session_args['submit_type'] );
			unset( $session_args['customer_creation'] );

			// Set the mode to Subscription.
			$session_args['mode'] = 'subscription';

			// Set the subscription data.
			$session_args['subscription_data'] = $this->get_subscription_args(
				$request
			);

			// Merge subscription data metadata into the top level metadata.
			if ( isset( $session_args['subscription_data']['metadata'] ) ) {
				$session_args['metadata'] = array_merge(
					$session_args['metadata'], // @phpstan-ignore-line
					$session_args['subscription_data']['metadata'] // @phpstan-ignore-line
				);
			}

			// Set the line items.
			// Checkout Sessions use a singular `line_items` argument.
			$session_args['line_items'] = array_merge(
				$this->get_subscription_recurring_line_items( $request ),
				$this->get_subscription_additional_invoice_line_items( $request )
			);

			if ( $this->application_fee->has_application_fee() ) {
				$session_args['subscription_data']['application_fee_percent'] =
					$this->application_fee->get_application_fee_percentage();
			}
		}

		// Add tax rates to line items, if needed.
		$session_args['line_items'] = TaxUtils::add_tax_rates_to_line_items(
			$request,
			$session_args['line_items'] // @phpstan-ignore-line
		);

		// Add automatic tax collection, if needed.
		$session_args = TaxUtils::add_automatic_tax_args(
			$request,
			$session_args
		);

		return $session_args;
	}

}
