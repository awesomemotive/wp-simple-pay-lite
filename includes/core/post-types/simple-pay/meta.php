<?php
/**
 * Simple Pay: Meta
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Meta
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers `simple-pay` meta fields to store Payment Form settings.
 *
 * @since 3.8.0
 */
function register() {
	// Amount.
	register_post_meta(
		'simple-pay',
		'_amount',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form amount.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Success redirect type.
	register_post_meta(
		'simple-pay',
		'_success_redirect_type',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form success redirect type.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Success redirect page.
	register_post_meta(
		'simple-pay',
		'_success_redirect_page',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form success page redirect page.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'absint',
		)
	);

	// Success redirect URL.
	register_post_meta(
		'simple-pay',
		'_success_redirect_url',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form success page redirect URL.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'esc_url',
		)
	);

	// Company name.
	register_post_meta(
		'simple-pay',
		'_company_name',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form title.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Item description.
	register_post_meta(
		'simple-pay',
		'_item_description',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form description.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Stripe Checkout - Image URL.
	register_post_meta(
		'simple-pay',
		'_image_url',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form image URL.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'esc_url',
		)
	);

	// Stripe Checkout - Submit button type.
	register_post_meta(
		'simple-pay',
		'_checkout_submit_type',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Stripe Checkout submit button type.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Stripe Checkout - Enable Billing Address.
	register_post_meta(
		'simple-pay',
		'_enable_billing_address',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Stripe Checkout billing address.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Stripe Checkout - Enable Shipping Address.
	register_post_meta(
		'simple-pay',
		'_enable_shipping_address',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Stripe Checkout shipping address.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Custom fields.
	register_post_meta(
		'simple-pay',
		'_custom_fields',
		array(
			'type'              => 'array',
			'description'       => __( 'Payment Form custom fields.', 'stripe' ),
			'single'            => true,
			'sanitize_callback' => null,
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\\register', 20 );
