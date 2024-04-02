<?php
/**
 * Utils: Schema
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

/**
 * SchemaUtils class.
 *
 * @since 4.7.0
 */
class SchemaUtils {

	/**
	 * Returns the schema for the `form_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_form_id_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'integer',
				'required'          => true,
				'description'       => __(
					'The payment form ID to use for the payment.',
					'stripe'
				),
				'validate_callback' => array(
					SchemaValidationUtils::class,
					'validate_form_id_arg',
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `form_values` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_form_values_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'object',
				'required'          => true,
				'description'       => __(
					'The payment form values to use for the payment.',
					'stripe'
				),
				'validate_callback' => array(
					SchemaValidationUtils::class,
					'validate_form_values_arg',
				),
				'sanitize_callback' => array(
					SchemaSanitizationUtils::class,
					'sanitize_form_values_arg',
				),
			)
		);
	}

	/**
	 * Returns the schema for the `token` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_token_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => false,
				'description'       => __(
					'A security token (usually from a CAPTCHA service) to verify the payment request.',
					'stripe'
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				// Instead of using a custom `validate_callback` we perform validation manually
				// in the payment request so we can display a more specific error message.
				'validate_callback' => 'rest_validate_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `price_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_price_id_schema( $args = array() ) {
		return wp_parse_args(
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The ID of the price to use for the payment.',
					'stripe'
				),
				'validate_callback' => array(
					SchemaValidationUtils::class,
					'validate_price_id_arg',
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `quantity` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_quantity_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'integer',
				'minimum'           => 1,
				'required'          => true,
				'description'       => __(
					'The purchase quantity for the payment.',
					'stripe'
				),
				'validate_callback' => array(
					SchemaValidationUtils::class,
					'validate_quantity_arg',
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `custom_amount` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_custom_amount_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'integer',
				'required'          => false,
				'description'       => __(
					'The custom amount for the payment.',
					'stripe'
				),
				'validate_callback' => array(
					SchemaValidationUtils::class,
					'validate_custom_amount_arg',
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `currency` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_currency_schema( $args = array() ) {
		/** @var string $default_currency */
		$default_currency = simpay_get_setting( 'currency', 'USD' );
		$default_currency = strtolower( $default_currency );

		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => false,
				'description'       => __(
					'The currency for the payment.',
					'stripe'
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
				'enum'              => array_map(
					'strtolower',
					array_keys( simpay_get_currencies() )
				),
				'default'           => $default_currency,
			)
		);
	}

	/**
	 * Returns the schema for the `is_optionally_recurring` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_is_optionally_recurring_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'boolean',
				'required'          => false,
				'description'       => __(
					'If the user has opted in to a recurring payment.',
					'stripe'
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `is_covering_fees` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_is_covering_fees_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'boolean',
				'required'          => false,
				'description'       => __(
					'If the user has opted in to pay processing fees.',
					'stripe'
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `coupon_code` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_coupon_code_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => false,
				'description'       => __(
					'The coupon code to apply to the payment.',
					'stripe'
				),
				// @todo validate it can apply to form
				'validate_callback' => array(
					SchemaValidationUtils::class,
					'validate_coupon_code_arg',
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `subtotal` parameter.
	 *
	 * Currently this is only used when previewing coupon validation.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_subtotal_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'integer',
				'required'          => false,
				'description'       => __(
					'The subtotal of the payment.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `billing_address` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_billing_address_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'object',
				'required'          => false,
				'description'       => __(
					'The customers\'s billing address.',
					'stripe'
				),
				'properties'        => array_merge(
					array(
						'name' => array(
							'description' => __( 'Name.', 'stripe' ),
							'type'        => 'string',
						),
					),
					self::get_address_fields_schema()
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `shipping_address` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_shipping_address_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'object',
				'required'          => false,
				'description'       => __(
					'The customers\'s shipping address.',
					'stripe'
				),
				'properties'        => array_merge(
					array(
						'name'  => array(
							'description' => __( 'Recipient name.', 'stripe' ),
							'type'        => 'string',
						),
						'phone' => array(
							'description' => __( 'Recipient phone number.', 'stripe' ),
							'type'        => 'string',
						),
					),
					self::get_address_fields_schema()
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `payment_method_type` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_payment_method_type_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The payment method type used to make a payment.',
					'stripe'
				),
				'validate_callback' => array(
					SchemaValidationUtils::class,
					'validate_payment_method_type_arg',
				),
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `customer_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_customer_id_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The payment\'s customer.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `subscription_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_subscription_id_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The payment\'s subscription ID.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `setup_intent_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_setup_intent_id_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The payment\'s SetupIntent ID.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `payment_method_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_payment_method_id_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The payment method\'s ID.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `subscription_key` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_subscription_key_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The payment\'s subscription key.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `object_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_object_id_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __(
					'The payment object to update.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for the `tax_calc_id` parameter.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, mixed> $args Argument overrides.
	 * @return array<string, mixed>
	 */
	public static function get_tax_calc_id_schema( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'required'          => false,
				'description'       => __(
					'The payment\'s tax calculation.',
					'stripe'
				),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			)
		);
	}

	/**
	 * Returns the schema for addresses.
	 *
	 * @since 4.7.0
	 *
	 * @return array<string, mixed>
	 */
	private static function get_address_fields_schema() {
		return array(
			'line1'    => array(
				'description' => __( 'Address.', 'stripe' ),
				'type'        => array( 'string', 'null' ),
			),
			'line2'    => array(
				'description' => __( 'Apartment, suite, etc.', 'stripe' ),
				'type'        => array( 'string', 'null' ),
			),
			'city'     => array(
				'description' => __( 'City.', 'stripe' ),
				'type'        => array( 'string', 'null' ),
			),
			'state'    => array(
				'description' => __( 'State/County code, or name of the state, county, province, or district.', 'stripe' ),
				'type'        => array( 'string', 'null' ),
			),
			'postcode' => array(
				'description' => __( 'Postal code.', 'stripe' ),
				'type'        => array( 'string', 'null' ),
			),
			'country'  => array(
				'description' => __( 'Country/Region code in ISO 3166-1 alpha-2 format.', 'stripe' ),
				'type'        => 'string',
			),
		);
	}

}
