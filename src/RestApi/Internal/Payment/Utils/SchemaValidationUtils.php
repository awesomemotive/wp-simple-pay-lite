<?php
/**
 * Utils: Schema validation
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

use SimplePay\Core\PaymentForm\PriceOption;
use SimplePay\Pro\Coupons\Coupon;
use SimplePay\Pro\Coupons\Coupon_Query;
use SimplePay\Pro\Payment_Methods;

/**
 * SchemaValidationUtils class.
 *
 * @since 4.7.0
 */
class SchemaValidationUtils {

	/**
	 * Validates that a `form_id` parameter can be transformed into a PaymentForm object.
	 *
	 * @since 4.7.0
	 *
	 * @param int              $value The `form_id parameter value.
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $param The parameter name.
	 * @return bool
	 */
	public static function validate_form_id_arg( $value, $request, $param ) {
		// First, validate the argument based on its registered schema.
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return false;
		}

		// Next, validate that the form exists.
		$form = simpay_get_form( $value );

		if ( false === $form ) {
			return false;
		}

		// Next, ensure the form has available schedule.
		$available = $form->has_available_schedule();

		if ( false === $available ) {
			return false;
		}

		// Finally, this form ID can be used.
		return true;
	}

	/**
	 * Validates that a `payment_method_type` parameter is valid for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param string           $value The `payment_method_type` parameter value.
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $param The parameter name.
	 * @return bool
	 */
	public static function validate_payment_method_type_arg( $value, $request, $param ) {
		// First, validate the argument based on its registered schema.
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return false;
		}

		// Next, validate that the form exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		$form_id = intval( $request->get_param( 'form_id' ) );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return false;
		}

		// Next, determine if the payment method is enabled.
		$payment_methods_types = Payment_Methods\get_form_payment_method_ids( $form );

		if ( ! in_array( $value, $payment_methods_types, true ) ) {
			return false;
		}

		// Finally, this payment method type can be used.
		return true;
	}

	/**
	 * Validates that a `price_id` parameter can be transformed into a PriceOption object.
	 *
	 * @since 4.7.0
	 *
	 * @param string           $value The `price_id parameter value.
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $param The parameter name.
	 * @return bool
	 */
	public static function validate_price_id_arg( $value, $request, $param ) {
		// First, validate the argument based on its registered schema.
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return false;
		}

		// Next, validate that the form exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		$form_id = intval( $request->get_param( 'form_id' ) );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return false;
		}

		// Next, validate that the price exists.
		$price = simpay_payment_form_prices_get_price_by_id( $form, $value );

		if ( ! $price instanceof PriceOption ) {
			return false;
		}

		// Next, determine if a custom amount also needs to be supplied.
		//
		// We do not require the `custom_amount` parameter at the schema level...
		// though we probably could...
		$custom_amount = intval( $request->get_param( 'custom_amount' ) );

		if ( $price->unit_amount_min && ! $custom_amount ) {
			return false;
		}

		// Finally, this price ID can be used.
		return true;
	}

	/**
	 * Validates that a `quantity` value is valid stock.
	 *
	 * @since 4.7.0
	 *
	 * @param int              $value The `quantity` parameter value.
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $param The parameter name.
	 * @return bool
	 */
	public static function validate_quantity_arg( $value, $request, $param ) {
		// First, validate the argument based on its registered schema.
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return false;
		}

		// Next, validate that the form exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		$form_id = intval( $request->get_param( 'form_id' ) );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return false;
		}

		// Next, validate that the price exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		/** @var string $price_id */
		$price_id = $request->get_param( 'price_id' );
		$price_id = sanitize_text_field( $price_id );
		$price    = simpay_payment_form_prices_get_price_by_id(
			$form,
			$price_id
		);

		if ( ! $price instanceof PriceOption ) {
			return false;
		}

		// Next, determine if the price still has enough stock remaining.
		if ( ! $price->is_in_stock( $value ) ) {
			return false;
		}

		// Finally, this quantity can be used.
		return true;
	}

	/**
	 * Validates that a `custom_amount` parameter is a valid amount.
	 *
	 * @since 4.7.0
	 *
	 * @param string           $value The `custom_amount` parameter value.
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $param The parameter name.
	 * @return bool
	 */
	public static function validate_custom_amount_arg( $value, $request, $param ) {
		// First, validate the argument based on its registered schema.
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return false;
		}

		// Next, validate that the form exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		$form_id = intval( $request->get_param( 'form_id' ) );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return false;
		}

		// Next, validate that the price exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		/** @var string $price_id */
		$price_id = $request->get_param( 'price_id' );
		$price_id = sanitize_text_field( $price_id );
		$price    = simpay_payment_form_prices_get_price_by_id(
			$form,
			$price_id
		);

		if ( ! $price instanceof PriceOption ) {
			return false;
		}

		// Next, validate that the price option is a custom amount. If it is not,
		// do not accept a custom amount.
		$is_defined_price = simpay_payment_form_prices_is_defined_price(
			$price->id
		);

		if ( true === $is_defined_price ) {
			return false;
		}

		// Next, validate that the custom amount meets the minimum amount.
		// Ensure we are comparing integers. I'm not sure why PriceOption
		// was not setting this previously.
		$unit_amount_min = intval( $price->unit_amount_min );

		if ( $value < $unit_amount_min ) {
			return false;
		}

		// Finally, this custom amount can be used.
		return true;
	}

	/**
	 * Validates that a `coupon_code` parameter is valid.
	 *
	 * @since 4.7.0
	 *
	 * @param string           $value The `coupon_code` parameter value.
	 * @param \WP_REST_Request $request The payment request.
	 * @param string           $param The parameter name.
	 * @return bool
	 */
	public static function validate_coupon_code_arg( $value, $request, $param ) {
		// First, validate the argument based on its registered schema.
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return false;
		}

		// Next, validate that the form exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		$form_id = intval( $request->get_param( 'form_id' ) );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return false;
		}

		// We have already validated that the coupon won't put the amount
		// below the minimum amount when applying it via the form.
		//
		// Someone using the REST API directly could still apply a coupon that
		// would put the amount below the minimum amount, but Stripe will reject.
		// So we don't need to validate that here, just that it applies to the form.

		// Next, if the coupon was created within WP Simple Pay, check the form restrictions.
		$api_args = $form->get_api_request_args();
		$coupons  = new Coupon_Query(
			$form->is_livemode(),
			$api_args['api_key']
		);

		$coupon = $coupons->get_by_name( $value );

		// ...the coupon was not created within WP Simple Pay, so it is valid,
		// since it cannot have form restrictions if it was created outside of WP Simple Pay.
		if ( ! $coupon instanceof Coupon ) {
			return true;
		}

		if (
			$request->get_param( 'price_id' ) &&
			false === $coupon->applies_to_form( $form->id )
		) {
			return false;
		}

		// Finally, this coupon can be used.
		return true;
	}

	/**
	 * Determines if the REST API request contains all required fields.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, string|array<string, string>> $value The `form_values` parameter value.
	 * @param \WP_REST_Request                            $request The payment request.
	 * @param string                                      $param The parameter name.
	 * @return bool
	 */
	public static function validate_form_values_arg( $value, $request, $param ) {
		// First, validate the argument based on its registered schema.
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return false;
		}

		// Next, validate that the form exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		$form_id = intval( $request->get_param( 'form_id' ) );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return false;
		}

		// Next, check for required fields.

		/** @var array<string, array<string, mixed>> $custom_fields */
		$custom_fields = simpay_get_saved_meta( $form->id, '_custom_fields' );

		/** @var array<string, string> $form_values */
		$form_values = $value;

		$always_required = array(
			'email',
			'address',
		);

		foreach ( $custom_fields as $custom_field_type => $custom_field_types ) {
			foreach ( $custom_field_types as $field ) {
				/** @var array<string, string> $field */
				if (
					! in_array( $custom_field_type, $always_required, true ) &&
					! isset( $field['required'] )
				) {
					continue;
				}

				// Check custom fields.
				if ( isset( $field['metadata'] ) ) {
					if ( empty( $field['metadata'] ) ) {
						$id = isset( $field['uid'] )
							? $field['uid']
							: '';

						$meta_key = 'simpay-form-' . $form->id . '-field-' . $id;
					} else {
						$meta_key = $field['metadata'];
					}

					if ( ! isset( $form_values['simpay_field'][ $meta_key ] ) ) {
						return false;
					}

					$value = trim( $form_values['simpay_field'][ $meta_key ] );

					if ( empty( $value ) ) {
						return false;
					}
				}

				// Check Customer fields.
				switch ( $custom_field_type ) {
					case 'tax_id':
						if (
							! isset( $form_values['simpay_tax_id'] ) ||
							! isset( $form_values['simpay_tax_id_type'] )
						) {
							return false;
						}

						/** @var string $tax_id */
						$tax_id = $form_values['simpay_tax_id'];

						/** @var string $tax_type */
						$tax_type = $form_values['simpay_tax_id_type'];

						$tax_id   = trim( $tax_id );
						$tax_type = trim( $tax_type );

						if ( empty( $tax_id ) || empty( $tax_type ) ) {
							return false;
						}

						break;
					case 'address':
						$address_type = (
							isset( $field['collect-shipping'] ) &&
							'yes' === $field['collect-shipping']
						)
							? 'shipping'
							: 'billing';

						/** @var array<string, string|array<string, string>> $address */
						$address = $request->get_param( $address_type . '_address' );

						if ( ! isset( $address['name'] ) ) {
							return false;
						}

						if ( ! isset( $address['address']['country'] ) ) {
							return false;
						}

						if ( ! isset( $address['address']['postal_code'] ) ) {
							return false;
						}

						break;
					case 'email':
					case 'customer_name':
					case 'telephone':
						if ( ! isset( $form_values[ 'simpay_' . $custom_field_type ] ) ) {
							return false;
						}

						/** @var string $value */
						$value = $form_values[ 'simpay_' . $custom_field_type ];
						$value = trim( $value );

						if ( empty( $value ) ) {
							return false;
						}

						break;
				}
			}
		}

		// Finally, these values can be used.
		return true;
	}

}
