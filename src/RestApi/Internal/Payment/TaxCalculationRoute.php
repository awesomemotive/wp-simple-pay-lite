<?php
/**
 * Tax calculation route
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
use SimplePay\Core\i18n;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Utils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\CouponUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\SchemaUtils;
use WP_REST_Response;
use WP_REST_Server;

/**
 * TaxCalculationRoute class.
 *
 * @since 4.7.0
 */
class TaxCalculationRoute extends AbstractPaymentRoute {

	use Traits\SubscriptionTrait;

	/**
	 * Registers the `POST /wpsp/__internal__/payment/calculate-tax` route.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function register_route() {
		$create_args = array(
			'form_id'                 => SchemaUtils::get_form_id_schema(),
			// Form values aren't required, but we check for the Customer Tax ID
			// if it is available.
			'form_values'             => SchemaUtils::get_form_values_schema(
				array(
					'required'          => false,
					'validate_callback' => 'rest_validate_request_arg',
				)
			),
			'price_id'                => SchemaUtils::get_price_id_schema(),
			'quantity'                => SchemaUtils::get_quantity_schema(),
			'custom_amount'           => SchemaUtils::get_custom_amount_schema(),
			'billing_address'         => SchemaUtils::get_billing_address_schema(),
			'shipping_address'        => SchemaUtils::get_shipping_address_schema(),
			'coupon_code'             => SchemaUtils::get_coupon_code_schema(),
			'is_covering_fees'        => SchemaUtils::get_is_covering_fees_schema(),
			'is_optionally_recurring' => SchemaUtils::get_is_optionally_recurring_schema(),
		);

		$create_item_route = array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_calculation' ),
			'permission_callback' => array( $this, 'create_calculation_permissions_check' ),
			'args'                => $create_args,
		);

		register_rest_route(
			$this->namespace,
			'payment/calculate-tax',
			$create_item_route
		);
	}

	/**
	 * Determines if the current request should be able to create a tax calculation.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	public function create_calculation_permissions_check( $request ) {
		return true;
	}

	/**
	 * Creates a tax calculation (preview) for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return \WP_REST_Response The calculation response.
	 * @throws \Exception If the request does not need a tax calculation.
	 */
	public function create_calculation( $request ) {
		$form         = PaymentRequestUtils::get_form( $request );
		$price        = PaymentRequestUtils::get_price( $request );
		$form_values  = PaymentRequestUtils::get_form_values( $request );
		$is_recurring = PaymentRequestUtils::is_recurring( $request );
		$tax_status   = get_post_meta( $form->id, '_tax_status', true );
		$tax_behavior = get_post_meta( $form->id, '_tax_behavior', true );

		try {
			if ( 'automatic' !== $tax_status ) {
				throw new Exception(
					__( 'Invalid request. Please try again.', 'stripe' )
				);
			}

			$args = array(
				'currency'         => $price->currency,
				'customer_details' => array(),
				'tax_date'         => time(),
				'preview'          => false,
			);

			// Add the provided billing or shipping adddress.
			if ( $request->get_param( 'billing_address' ) ) {
				$args['customer_details']['address_source'] = 'billing';
				$args['customer_details']['address']        = $request->get_param(
					'billing_address'
				);
			} elseif ( $request->get_param( 'shipping_address' ) ) {
				$args['customer_details']['address_source'] = 'shipping';
				$args['customer_details']['address']        = $request->get_param(
					'shipping_address'
				);
			}

			if ( ! isset( $args['customer_details']['address'] ) ) {
				throw new Exception(
					__(
						'Please enter a valid address to calculate tax.',
						'stripe'
					)
				);
			}

			// Remove empty values.
			/** @var array<string, string> $address */
			$address = $args['customer_details']['address'];

			$args['customer_details']['address'] = array_filter(
				$address,
				function ( $value ) {
					return ! empty( $value );
				}
			);

			// Add the tax ID, if provided.
			if ( isset( $form_values['simpay_tax_id'] ) ) {
				/** @var string $tax_id_type */
				$tax_id_type = isset( $form_values['simpay_tax_id_type'] )
					? $form_values['simpay_tax_id_type']
					: '';

				$valid_tax_id_types = i18n\get_stripe_tax_id_types();

				if ( false === array_key_exists( $tax_id_type, $valid_tax_id_types ) ) {
					throw new Exception(
						esc_html__(
							'Please select a valid Tax ID type.',
							'stripe'
						)
					);
				}

				/** @var string $tax_id */
				$tax_id = $form_values['simpay_tax_id'];
				$tax_id = sanitize_text_field( $tax_id );

				$args['customer_details']['tax_ids'] = array(
					array(
						'type'  => $tax_id_type,
						'value' => $tax_id,
					),
				);
			}

			// @todo These aren't the best names, but it requires less refactoring
			// of the client Cart.
			$total_details    = array(
				'amount_discount' => 0,
				'amount_shipping' => 0,
				'amount_tax'      => 0,
			);
			$upcoming_invoice = $total_details;

			$today_line_items = $this->get_tax_today_line_items(
				$request
			);

			if ( ! empty( $today_line_items ) ) {
				$today_tax_calculation = Stripe_API::request(
					'Tax\Calculation',
					'create',
					array_merge(
						$args,
						array(
							'line_items' => $today_line_items,
						)
					),
					$form->get_api_request_args()
				);

				$total_details['amount_tax'] = 'exclusive' === $tax_behavior
					? $today_tax_calculation->tax_amount_exclusive
					: $today_tax_calculation->tax_amount_inclusive;
			} else {
				$total_details['amount_tax'] = 0;
			}

			if ( $is_recurring ) {
				$upcoming_line_items = $this->get_tax_upcoming_line_items(
					$request
				);

				if ( ! empty( $upcoming_line_items ) ) {
					$upcoming_tax_calculation = Stripe_API::request(
						'Tax\Calculation',
						'create',
						array_merge(
							$args,
							array(
								'line_items' => $upcoming_line_items,
							)
						),
						$form->get_api_request_args()
					);

					$upcoming_invoice['amount_tax'] = 'exclusive' === $tax_behavior
						? $upcoming_tax_calculation->tax_amount_exclusive
						: $upcoming_tax_calculation->tax_amount_inclusive;
				} else {
					$upcoming_invoice['amount_tax'] = 0;
				}
			}

			return new WP_REST_Response(
				array(
					'id'               => ! empty( $today_tax_calculation )
						? $today_tax_calculation->id
						: null,
					'tax'              => array(
						'behavior' => $tax_behavior,
					),
					'total_details'    => $total_details,
					'upcoming_invoice' => $upcoming_invoice,
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
	 * Returns the line items for the tax due today for the given request.
	 *
	 * Plan and Setup Fees are added to a singular line item so the discount amount
	 * can be easily calculated. When automatic tax is used with Billing, they
	 * are added as separate line items.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<array<string, mixed>> The line items.
	 */
	private function get_tax_today_line_items( $request ) {
		$form             = PaymentRequestUtils::get_form( $request );
		$price            = PaymentRequestUtils::get_price( $request );
		$base_unit_amount = PaymentRequestUtils::get_unit_amount( $request );
		$quantity         = PaymentRequestUtils::get_quantity( $request );
		$tax_code         = get_post_meta( $form->id, '_tax_code', true );
		$tax_behavior     = get_post_meta( $form->id, '_tax_behavior', true );

		// If there is a trial period, reset the amount to 0.
		if (
			$price->recurring &&
			isset( $price->recurring['trial_period_days'] )
		) {
			$base_unit_amount = 0;
		}

		// Add the setup (and plan) fee, if needed.
		if ( ! empty( $price->line_items ) ) {
			$setup_fee_unit_amount = array_reduce(
				$price->line_items,
				function ( $carry, $item ) {
					$carry = $carry + $item['unit_amount'];
					return $carry;
				},
				0
			);

			$base_unit_amount = $base_unit_amount + $setup_fee_unit_amount;
		}

		// Remove the coupon amount, if needed.
		$discount = CouponUtils::get_discount_unit_amount(
			$request,
			$base_unit_amount
		);

		if ( 0 !== $discount ) {
			$base_unit_amount = $base_unit_amount - $discount;
		}

		// If the amount is 0, there is no tax due.
		if ( 0 === $base_unit_amount ) {
			return array();
		}

		return array(
			array(
				'amount'       => $base_unit_amount,
				'product'      => $price->product_id,
				'quantity'     => $quantity,
				'tax_behavior' => $tax_behavior,
				'tax_code'     => $tax_code,
				'reference'    => $price->id,
			),
		);
	}

	/**
	 * Returns the line items for the tax due on the next invoice for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<array<string, mixed>> The line items.
	 */
	private function get_tax_upcoming_line_items( $request ) {
		$form             = PaymentRequestUtils::get_form( $request );
		$price            = PaymentRequestUtils::get_price( $request );
		$base_unit_amount = PaymentRequestUtils::get_unit_amount( $request );
		$quantity         = PaymentRequestUtils::get_quantity( $request );
		$tax_code         = get_post_meta( $form->id, '_tax_code', true );
		$tax_behavior     = get_post_meta( $form->id, '_tax_behavior', true );
		$coupon_code      = PaymentRequestUtils::get_coupon_code( $request );

		// Remove the coupon amount, if needed.
		// We need to ensure the coupon is valid for the next invoice.
		if ( $coupon_code ) {
			$coupon = API\Coupons\retrieve(
				$coupon_code,
				$form->get_api_request_args()
			);

			if ( 'once' !== $coupon->duration ) {
				$discount = CouponUtils::get_discount_unit_amount(
					$request,
					$base_unit_amount
				);

				if ( 0 !== $discount ) {
					$base_unit_amount = $base_unit_amount - $discount;
				}
			}
		}

		// If the amount is 0, there is no tax due.
		if ( 0 === $base_unit_amount ) {
			return array();
		}

		return array(
			array(
				'amount'       => $base_unit_amount,
				'product'      => $price->product_id,
				'quantity'     => $quantity,
				'tax_behavior' => $tax_behavior,
				'tax_code'     => $tax_code,
				'reference'    => wp_generate_uuid4(),
			),
		);
	}

}
