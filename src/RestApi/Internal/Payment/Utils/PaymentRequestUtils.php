<?php
/**
 * Utils: Payment request
 *
 * Utility methods for retrieving request data.
 *
 * Warning: These methods should only be called within a validated REST API
 * request. Do use these methods when validating the request, as not all parameters
 * can be guaranteed to be validated.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

use SimplePay\Core\Abstracts\Form;
use SimplePay\Pro\Payment_Methods;
use function SimplePay\Pro\Post_Types\Simple_Pay\Util\get_custom_fields;

/**
 * PaymentRequestUtils class.
 *
 * @since 4.7.0
 */
class PaymentRequestUtils {

	/**
	 * Returns the payment form for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return \SimplePay\Core\Abstracts\Form
	 */
	public static function get_form( $request ) {
		static $payment_form = null;

		if ( $payment_form instanceof Form ) {
			return $payment_form;
		}

		/** @var int $form_id This has already been validated by the schema. IDE helper. */
		$form_id = $request->get_param( 'form_id' );

		/** @var \SimplePay\Core\Abstracts\Form $form This has already been validated by the schema. IDE helper. */
		$form = simpay_get_form( $form_id );

		return $form;
	}

	/**
	 * Returns the form values for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<string, string|array<string, string>>
	 */
	public static function get_form_values( $request ) {
		/** @var array<string, string|array<string, string>> $form_values */
		$form_values = $request->get_param( 'form_values' );

		return $form_values;
	}

	/**
	 * Returns the `PriceOption` for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return \SimplePay\Core\PaymentForm\PriceOption
	 */
	public static function get_price( $request ) {
		/** @var string $price_id This has already been validated by the schema. IDE helper. */
		$price_id = $request->get_param( 'price_id' );

		/** @var \SimplePay\Core\PaymentForm\PriceOption $price This has already been validated by the schema. IDE helper. */
		$price = simpay_payment_form_prices_get_price_by_id(
			self::get_form( $request ),
			$price_id
		);

		return $price;
	}

	/**
	 * Returns the currency for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return string
	 */
	public static function get_currency( $request ) {
		return self::get_price( $request )->currency;
	}

	/**
	 * Returns the purchase quantity for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return int
	 */
	public static function get_quantity( $request ) {
		/** @var int $quantity This has already been validated by the schema. IDE helper. */
		$quantity = $request->get_param( 'quantity' );

		return $quantity;
	}

	/**
	 * Returns the custom amount for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return int
	 */
	public static function get_custom_unit_amount( $request ) {
		/** @var int $custom_amount This has already been validated by the schema. IDE helper. */
		$custom_amount = $request->get_param( 'custom_amount' );

		return $custom_amount;
	}

	/**
	 * Returns the coupon code for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return string
	 */
	public static function get_coupon_code( $request ) {
		/** @var string $coupon_code This has already been validated by the schema. IDE helper. */
		$coupon_code = $request->get_param( 'coupon_code' );

		return $coupon_code;
	}

	/**
	 * Returns the tax calculation ID for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return string
	 */
	public static function get_tax_calc_id( $request ) {
		/** @var string $tax_calc_id This has already been validated by the schema. IDE helper. */
		$tax_calc_id = $request->get_param( 'tax_calc_id' );

		return $tax_calc_id;
	}

	/**
	 * Determines if the payment is opted-in to optionally recurring.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	public static function is_optionally_recurring( $request ) {
		/** @var bool $is_optionally_recurring This has already been validated by the schema. IDE helper. */
		$is_optionally_recurring = $request->get_param(
			'is_optionally_recurring'
		);

		return $is_optionally_recurring;
	}

	/**
	 * Determines if the payment is opted-in to covering processing fees.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	public static function is_covering_fees( $request ) {
		/** @var bool $is_covering_fees This has already been validated by the schema. IDE helper. */
		$is_covering_fees = $request->get_param( 'is_covering_fees' );

		return $is_covering_fees;
	}

	/**
	 * Determines if the payment is recurring.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	public static function is_recurring( $request ) {
		$price                   = self::get_price( $request );
		$is_optionally_recurring = self::is_optionally_recurring( $request );

		// Price option can recur, and is, so it is recurring.
		if ( $price->can_recur && $is_optionally_recurring ) {
			return true;

			// Price can recur, but it is not opted in, so it's not.
		} elseif ( $price->can_recur && ! $is_optionally_recurring ) {
			return false;
		}

		// Price option is recurring, so it is recurring.
		return is_array( $price->recurring );
	}

	/**
	 * Returns the unit amount for the given request.
	 *
	 * If a custom amount is being used, return that.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return int
	 */
	public static function get_unit_amount( $request ) {
		$price              = self::get_price( $request );
		$custom_unit_amount = self::get_custom_unit_amount( $request );

		if ( false === simpay_payment_form_prices_is_defined_price( $price->id ) ) {
			return $custom_unit_amount;
		}

		return $price->unit_amount;
	}

	/**
	 * Returns the total amount for a given request. This accounts for quantity,
	 * discounts, fee recovery, and taxes.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return int
	 */
	public static function get_amount( $request ) {
		$form         = PaymentRequestUtils::get_form( $request );
		$unit_amount  = PaymentRequestUtils::get_unit_amount( $request );
		$quantity     = PaymentRequestUtils::get_quantity( $request );
		$tax_status   = get_post_meta( $form->id, '_tax_status', true );
		$tax_behavior = get_post_meta( $form->id, '_tax_behavior', true );

		$unit_amount = $unit_amount * $quantity;

		// Add the fee recovery amount, if needed.
		if ( $form->has_fee_recovery() ) {
			$fee_recovery = FeeRecoveryUtils::get_fee_recovery_unit_amount(
				$request,
				$unit_amount
			);
			$unit_amount  = $unit_amount + $fee_recovery;
		}

		// Remove the coupon amount, if needed.
		$discount = CouponUtils::get_discount_unit_amount(
			$request,
			$unit_amount,
			null
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

		return $unit_amount;
	}

	/**
	 * Returns data for a PaymentIntent for the given request.
	 *
	 * This is generic data that applies to a base PaymentIntent, regardless
	 * of what creates it (Checkout Session, Subscription, Order, etc).
	 * Additional arguments used just for the PaymentIntent API are added
	 * in `PaymentIntentTrait::create_payment_intent()`.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<string, array<string, string>|string>
	 */
	public static function get_payment_intent_data( $request ) {
		$form                = self::get_form( $request );
		$price               = self::get_price( $request );
		$payment_intent_data = array(
			'metadata' => self::get_payment_metadata( $request ),
		);

		// Set the description.
		// Use price option label if one is set.
		if ( null !== $price->label ) {
			$payment_intent_data['description'] = $price->get_display_label();

			// Fall back to Payment Form title if set.
			// This is a change in behavior in 4.1, but matches the Stripe Checkout
			// usage that falls back to the Product title (Payment Form title).
		} else {
			if ( ! empty( $form->company_name ) ) {
				$payment_intent_data['description'] = $form->company_name;
			}
		}

		// Set the Statement Descriptor. This is empty by default.
		if ( ! empty( $form->statement_descriptor ) ) {
			$payment_intent_data['statement_descriptor'] = $form->statement_descriptor;
		}

		return $payment_intent_data;
	}

	/**
	 * Returns metadata for the primary payment object for the given request.
	 *
	 * This is generic data that applies can be applied to the primary payment
	 * object, i.e Checkout Session, Subscription, or PaymentIntent.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<string, string>
	 */
	public static function get_payment_metadata( $request ) {
		$form        = self::get_form( $request );
		$form_values = self::get_form_values( $request );
		$price       = self::get_price( $request );
		$unit_amount = self::get_unit_amount( $request );
		$quantity    = self::get_quantity( $request );
		$subtotal    = $unit_amount * $quantity;

		$metadata = array(
			'simpay_form_id'         => $form->id,
			'simpay_unit_amount'     => $unit_amount,
			'simpay_quantity'        => $quantity,
			'simpay_price_instances' => sprintf(
				'%s:%d',
				$price->instance_id,
				$quantity
			),
		);

		$license = simpay_get_license();

		// Add additional metadata for non-lite licenses.
		if ( false === $license->is_lite() ) {
			// Custom fields.
			/** @var array<string, string> $custom_fields Custom fields. */
			$custom_fields = isset( $form_values['simpay_field'] )
				? $form_values['simpay_field']
				: array();

			foreach ( $custom_fields as $key => $value ) {
				// Skip empty.
				if ( '' === trim( $value ) ) {
					continue;
				}

				$metadata[ $key ] = $value;
			}

			$amount = self::get_amount( $request );

			// Fee recovery.
			$fee_recovery = FeeRecoveryUtils::get_fee_recovery_unit_amount(
				$request,
				$subtotal // Safe to use subtotal here because fee recovery does not support taxes or coupons.
			);

			if ( 0 !== $fee_recovery ) {
				$metadata['simpay_fee_recovery_unit_amount'] = $fee_recovery;
			}

			// Tax.
			$tax_status      = get_post_meta( $form->id, '_tax_status', true );
			$tax_behavior    = get_post_meta( $form->id, '_tax_behavior', true );
			$tax_unit_amount = TaxUtils::get_tax_unit_amount(
				$request,
				$unit_amount
			);

			switch ( $tax_status ) {
				case 'automatic':
					// Find the tax percent based on the total amount, and tax amount.
					$tax_percent = ( $tax_unit_amount / ( $amount - $tax_unit_amount ) ) * 100;
					$tax_percent = round( $tax_percent );

					if ( 'exclusive' === $tax_behavior ) {
						$metadata['simpay_tax_percent_exclusive']     = $tax_percent;
						$metadata['simpay_tax_unit_amount_exclusive'] = $tax_unit_amount;
					} else {
						$metadata['simpay_tax_percent_inclusive']     = $tax_percent;
						$metadata['simpay_tax_unit_amount_inclusive'] = $tax_unit_amount;
					}

					break;
				case 'fixed-global':
					$metadata['simpay_tax_percent_exclusive'] = simpay_get_payment_form_tax_percentage(
						$form,
						'exclusive'
					);

					$metadata['simpay_tax_unit_amount_exclusive'] = round( $amount - $subtotal );

					$metadata['simpay_tax_percent_inclusive'] = simpay_get_payment_form_tax_percentage(
						$form,
						'inclusive'
					);

					$metadata['simpay_tax_unit_amount_inclusive'] = 0;

					if ( 0 !== $metadata['simpay_tax_percent_inclusive'] ) {
						$metadata['simpay_tax_unit_amount_inclusive'] = round(
							( $amount - $subtotal ) - ( $metadata['simpay_tax_percent_inclusive'] / 100 )
						);
					}

					break;
			}

			// Coupon.
			$coupon_data = CouponUtils::get_coupon_data(
				$request,
				self::get_coupon_code( $request ),
				$unit_amount,
				self::get_currency( $request )
			);

			if ( ! isset( $coupon_data['error'] ) ) {
				/** @var array<string, \SimplePay\Vendor\Stripe\Coupon> $coupon_data  */
				$metadata['simpay_coupon_code'] = $coupon_data['coupon']->id;
			}
		}

		// Sanitize all keys and values.
		$_metadata = array();

		foreach ( $metadata as $key => $value ) {
			/** @var string $key */
			/** @var string $value */

			$key   = sanitize_text_field( stripslashes( $key ) );
			$value = sanitize_text_field( stripslashes( $value ) );

			$key   = simpay_truncate_metadata( 'title', $key );
			$value = simpay_truncate_metadata( 'description', $value );

			$_metadata[ $key ] = $value;
		}

		/** @var array<string, string> $_metadata */
		return $_metadata;
	}

	/**
	 * Returns the payment method types available for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<string>
	 */
	public static function get_payment_method_types( $request ) {
		$form  = self::get_form( $request );
		$price = self::get_price( $request );

		$currency  = $price->currency;
		$recurring = null !== $price->recurring && false === $price->can_recur;

		$is_optionally_recurring = self::is_optionally_recurring( $request );

		/** @var array<\SimplePay\Pro\Payment_Methods\Payment_Method> */
		$payment_methods = Payment_Methods\get_form_payment_methods( $form );

		// Remove Payment Methods that do not support the current currency.
		$payment_methods = array_filter(
			$payment_methods,
			function( $payment_method ) use ( $currency ) {
				return in_array( $currency, $payment_method->currencies, true );
			}
		);

		// Remove Payment Methods that do not support the current recurring options
		// if recurring is being used.
		if ( true === $recurring || true === $is_optionally_recurring ) {
			$payment_methods = array_filter(
				$payment_methods,
				/**
				 * Determines if the given Payment Method supports recurring payments.
				 *
				 * @since unknown
				 *
				 * @param \SimplePay\Pro\Payment_Methods\Payment_Method $payment_method The Payment Method.
				 * @return bool
				 */
				function( $payment_method ) {
					// Check for Stripe Checkout-specific overrides first.
					if (
						is_array( $payment_method->stripe_checkout ) &&
						isset( $payment_method->stripe_checkout['recurring'] )
					) {
						return true === $payment_method->stripe_checkout['recurring'];
					}

					// Check general recurring capabilities.
					return true === $payment_method->recurring;
				}
			);
		}

		$payment_methods = array_map(
			function( $payment_method_id ) {
				switch ( $payment_method_id ) {
					case 'ach-debit':
						return 'us_bank_account';
					default:
						return str_replace( '-', '_', $payment_method_id );
				}
			},
			array_keys( $payment_methods )
		);

		// Check the Card configuration and enable Link, if needed.
		// Do not add if using Stripe Checkout.
		if ( 'stripe_checkout' !== $form->get_display_type() ) {
			$custom_fields = get_custom_fields( $form->id );

			$emails = array_filter(
				$custom_fields,
				function( $field ) {
					return 'email' === $field['type'];
				}
			);

			if ( ! empty( $emails ) ) {
				$email = current( $emails );

				$link_enabled = isset(
					$email['link'],
					$email['link']['enabled']
				)
					? 'yes' === $email['link']['enabled']
					: false;

				if ( in_array( 'card', $payment_methods, true ) && $link_enabled ) {
					$payment_methods[] = 'link';
				}
			}
		}

		return $payment_methods;
	}

	/**
	 * Returns the configuration for available payment method types for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<string, array<string, string>>
	 */
	public static function get_payment_method_options( $request ) {
		$is_recurring           = self::is_recurring( $request );
		$payment_method_types   = self::get_payment_method_types( $request );
		$payment_method_options = array(
			'card'            => array(
				'setup_future_usage' => 'off_session',
			),
			'link'            => array(
				'setup_future_usage' => 'off_session',
			),
			'sepa_debit'      => array(
				'setup_future_usage' => 'off_session',
			),
			'us_bank_account' => array(
				'verification_method' => 'instant',
				'setup_future_usage'  => 'off_session',
			),
		);

		// Remove `setup_future_usage` if the form is recurring. This gets set
		// at the Subscription's top level `off_session=true` parameter instead.
		$payment_method_options = array_map(
			function ( $payment_method_options ) use ( $is_recurring ) {
				if ( true === $is_recurring ) {
					unset( $payment_method_options['setup_future_usage'] );
				}

				return $payment_method_options;
			},
			$payment_method_options
		);

		// Filter out payment methods that are not available for the given request.
		return array_filter(
			$payment_method_options,
			function( $payment_method_type ) use ( $payment_method_types ) {
				return in_array( $payment_method_type, $payment_method_types, true );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Returns the payment method type for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return string
	 */
	public static function get_payment_method_type( $request ) {
		/** @var string $payment_method_type */
		$payment_method_type = $request->get_param( 'payment_method_type' );

		return $payment_method_type;
	}

	/**
	 * Returns the URL to redirect to after a successful payment.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return string
	 */
	public static function get_return_url( $request ) {
		$form       = self::get_form( $request );
		$return_url = esc_url_raw( $form->payment_success_page );

		if ( ! wp_http_validate_url( $return_url ) ) {
			$return_url = add_query_arg(
				array(
					'form_id' => $form->id,
				),
				esc_url_raw( home_url() )
			);
		}

		return $return_url;
	}

	/**
	 * Returns the URL to redirect to if a payment is cancelled.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return string
	 */
	public static function get_cancel_url( $request ) {
		$form       = self::get_form( $request );
		$cancel_url = esc_url_raw( $form->payment_cancelled_page );

		if ( empty( $cancel_url ) ) {
			$cancel_url = esc_url_raw( home_url() );
		}

		return $cancel_url;
	}

}
