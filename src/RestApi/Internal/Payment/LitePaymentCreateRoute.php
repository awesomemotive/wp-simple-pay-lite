<?php
/**
 * Lite payment creation route
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment;

use Exception;
use SimplePay\Core\i18n;
use SimplePay\Core\Utils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\SchemaUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\TokenValidationUtils;
use SimplePay\Core\RestApi\Internal\Payment\Exception\ValidationException;
use WP_REST_Response;
use WP_REST_Server;

/**
 * LitePaymentCreateRoute class.
 *
 * @since 4.7.0
 */
class LitePaymentCreateRoute extends AbstractPaymentCreateRoute {

	// Payment helpers.
	use Traits\CheckoutSessionTrait;

	/**
	 * {@inheritdoc}
	 */
	public function register_route() {
		$create_item_route = array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_payment' ),
			'permission_callback' => array(
				$this,
				'create_payment_permissions_check',
			),
			'args'                => array(
				'form_id'  => SchemaUtils::get_form_id_schema(),
				'price_id' => SchemaUtils::get_price_id_schema(),
				'quantity' => SchemaUtils::get_quantity_schema(),
				'token'    => SchemaUtils::get_token_schema(),
			),
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
	 * @throws \Simplepay\Core\RestApi\Internal\Payment\Exception\ValidationException If a validation error occurs.
	 */
	public function create_payment( $request ) {
		try {
			// Check rate limit.
			// This is done here to avoid double increments (in authorization callback)
			// or non-human-friendly error messages (in API argument validation).
			if ( false === $this->validate_rate_limit( $request ) ) {
				throw new ValidationException(
					__(
						'Sorry, you have made too many requests. Please try again later.',
						'stripe'
					)
				);
			}

			// Check form token.
			if ( false === TokenValidationUtils::validate_token( $request ) ) {
				throw new ValidationException(
					__( 'Invalid CAPTCHA. Please try again.', 'stripe' )
				);
			}

			$payment = $this->create_checkout_session(
				$request,
				$this->get_checkout_session_args( $request, null )
			);

			return new WP_REST_Response(
				array(
					'redirect' => $payment->url,
				)
			);
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
	 * Returns arguments used to create a Checkout Session.
	 *
	 * These arguments are available in both Lite and Pro.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request                       $request The payment request.
	 * @param null|\SimplePay\Vendor\Stripe\Customer $customer The Stripe customer.
	 * @return array<string, mixed>
	 */
	protected function get_checkout_session_args( $request, $customer ) {
		$form     = PaymentRequestUtils::get_form( $request );
		$price    = PaymentRequestUtils::get_price( $request );
		$quantity = PaymentRequestUtils::get_quantity( $request );

		$session_args = array(
			'customer_creation' => 'always',
			'locale'            => $form->locale,
			'metadata'          => array(
				'simpay_form_id' => $form->id,
			),
			'mode'              => 'payment',
		);

		// Collect Billing Address.
		if ( true === $form->enable_billing_address ) {
			$session_args['billing_address_collection'] = 'required';
		} else {
			$session_args['billing_address_collection'] = 'auto';
		}

		// Collect Shipping Address.
		if ( true === $form->enable_shipping_address ) {
			$session_args['shipping_address_collection'] = array(
				'allowed_countries' => i18n\get_available_shipping_address_countries(),
			);
		}

		// Success URL.
		$session_args['success_url'] = add_query_arg(
			'session_id',
			'{CHECKOUT_SESSION_ID}',
			PaymentRequestUtils::get_return_url( $request )
		);

		// Cancel URL.
		$session_args['cancel_url'] = PaymentRequestUtils::get_cancel_url( $request );

		// Submit type.
		if ( ! empty( $form->checkout_submit_type ) ) {
			$session_args['submit_type'] = $form->checkout_submit_type;
		}

		// Phone number.
		$enable_phone = 'yes' === simpay_get_saved_meta(
			$form->id,
			'_enable_phone',
			'no'
		);

		if ( true === $enable_phone ) {
			$session_args['phone_number_collection'] = array(
				'enabled' => true,
			);
		}

		// Line item.
		$item = array(
			'price'    => $price->id,
			'quantity' => $quantity,
		);

		$enable_quantity = 'yes' === simpay_get_saved_meta(
			$form->id,
			'_enable_quantity',
			'no'
		);

		if ( $enable_quantity ) {
			$item['adjustable_quantity'] = array(
				'enabled' => true,
				'minimum' => 1,
			);
		}

		$session_args['line_items'] = array( $item );

		// Payment method types.
		/** @var array<string, array<string, array<string, string>>> $payment_methods */
		$payment_methods = simpay_get_saved_meta(
			$form->id,
			'_payment_methods',
			array()
		);

		if ( empty( $payment_methods ) || ! isset( $payment_methods['stripe-checkout'] ) ) {
			$session_args['payment_method_types'] = array( 'card' );
		} else {
			$session_args['payment_method_types'] = array_keys(
				$payment_methods['stripe-checkout']
			);
		}

		// Build additional data used to create the underlying Payment Intent.
		$payment_intent_data = PaymentRequestUtils::get_payment_intent_data(
			$request
		);

		// ... add an application fee, if needed.
		if ( $this->application_fee->has_application_fee() ) {
			$payment_intent_data['application_fee_amount'] =
				$this->application_fee->get_application_fee_amount(
					$price->unit_amount
				);
		}

		$session_args['payment_intent_data'] = $payment_intent_data;

		$session_args['custom_fields'] = $this->get_custom_fields( $request );

		return $session_args;
	}

	/**
	 * Returns a list of custom fields to be added to the Checkout Session.
	 *
	 * @since 4.7.7
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<array<string, mixed>>
	 */
	private function get_custom_fields( $request ) {
		$form   = PaymentRequestUtils::get_form( $request );
		$fields = $form->custom_fields;

		$fields = array_filter(
			$fields,
			function( $field ) {
				return 'payment_button' !== $field['type'];
			}
		);

		if ( empty( $fields ) ) {
			return array();
		}

		$custom_fields = array();

		foreach ( $fields as $k => $field ) {
			$type = $field['type'];

			// Use the label as the key, if it exists, or create one from the type.
			$label = ! empty( $field['label'] )
				? $field['label']
				: sprintf( '%s-%d', $type, $k );

			// Create a key from the label.
			$key = preg_replace( "/[^a-zA-Z0-9]/", '', $label );

			$args = array(
				'key'      => $key,
				'label'    => array(
					'type'   => 'custom',
					'custom' => $label,
				),
				'type'     => 'number' === $type ? 'numeric' : $type,
				'optional' => ! isset( $field['required'] ),
			);

			// Add dropdown options, if needed.
			if ( 'dropdown' === $type ) {
				$options = $field['options'];
				$options = explode( simpay_list_separator(), $options );
				$options = array_map( 'trim', $options );
				$options = array_filter( $options );

				$args['dropdown']['options'] = array_map(
					function( $option ) {
						return array(
							'label' => $option,
							'value' => preg_replace( "/[^a-zA-Z0-9]/", '', $option ),
						);
					},
					$options
				);
			}

			$custom_fields[] = $args;
		}

		return $custom_fields;
	}

}
