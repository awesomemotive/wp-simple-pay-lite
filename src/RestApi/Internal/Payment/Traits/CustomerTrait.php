<?php
/**
 * Customer trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Traits;

use Exception;
use SimplePay\Core\API;
use SimplePay\Core\i18n;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\Utils;

/**
 * CustomerTrait trait.
 *
 * @since 4.7.0
 */
trait CustomerTrait {

	/**
	 * Determines if the payment request requires a customer.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	private function is_using_customer( $request ) {
		$form = PaymentRequestUtils::get_form( $request );

		// On-site forms always need a customer.
		if ( 'stripe_checkout' !== $form->get_display_type() ) {
			return true;
		}

		/** @var array<string, array<string, mixed>> $custom_fields */
		$custom_fields = simpay_get_saved_meta(
			PaymentRequestUtils::get_form( $request )->id,
			'_custom_fields',
			array()
		);

		return (
			array_key_exists( 'customer_name', $custom_fields ) ||
			array_key_exists( 'email', $custom_fields ) ||
			array_key_exists( 'telephone', $custom_fields ) ||
			array_key_exists( 'address', $custom_fields ) ||
			array_key_exists( 'coupon', $custom_fields )
		);
	}

	/**
	 * Returns the customer arguments for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return null|\SimplePay\Vendor\Stripe\Customer
	 */
	private function get_customer( $request ) {
		if ( false === $this->is_using_customer( $request ) ) {
			return null;
		}

		// @todo in the future we may allow using an existing customer.
		return $this->create_customer( $request );
	}

	/**
	 * Creates a Customer for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return \SimplePay\Vendor\Stripe\Customer
	 */
	private function create_customer( $request ) {
		$form          = PaymentRequestUtils::get_form( $request );
		$form_values   = PaymentRequestUtils::get_form_values( $request );
		$customer_args = $this->get_customer_args( $request );

		/**
		 * Filters arguments used to create a Customer from a payment form request.
		 *
		 * @since 3.6.0
		 *
		 * @param array<string, mixed>           $customer_args Customer arguments.
		 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array<mixed>                   $arg2 Deprecated.
		 * @param array<string, mixed>           $form_values Form values.
		 * @return array<string, mixed>
		 */
		$customer_args = apply_filters(
			'simpay_get_customer_args_from_payment_form_request',
			$customer_args,
			$form,
			array(),
			$form_values
		);

		/**
		 * Allow further processing before a Customer is created from a posted form.
		 *
		 * @since 3.6.0
		 *
		 * @param array<string, mixed>           $customer_args Customer arguments.
		 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array<mixed>                   $arg2 Deprecated.
		 * @param array<string, mixed>           $form_values Form values.
		 */
		do_action(
			'simpay_before_customer_from_payment_form_request',
			$customer_args,
			$form,
			array(),
			$form_values
		);

		$customer = API\Customers\create(
			$customer_args,
			$form->get_api_request_args()
		);

		/**
		 * Allow further processing after a Customer is created from a posted form.
		 *
		 * @since 3.6.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Customer $customer Customer.
		 * @param \SimplePay\Core\Abstracts\Form    $form Form instance.
		 * @param array<mixed>                      $form_data Deprecated.
		 * @param array<string, mixed>              $form_values Form values.
		 */
		do_action(
			'simpay_after_customer_from_payment_form_request',
			$customer,
			$form,
			array(),
			$form_values
		);

		return $customer;
	}

	/**
	 * Returns arguments for creating a Customer for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return array<string, mixed>
	 * @throws \Exception If the tax ID is not valid.
	 */
	private function get_customer_args( $request ) {
		$form        = PaymentRequestUtils::get_form( $request );
		$form_values = PaymentRequestUtils::get_form_values( $request );

		$customer_args = array(
			'name'     => null,
			'phone'    => null,
			'email'    => null,
			'metadata' => array(
				'simpay_form_id' => $form->id,
			),
			'tax'      => array(
				'ip_address' => Utils\get_current_ip_address(),
			),
		);

		// Attach coupon to metadata.
		if ( ! empty( $request->get_param( 'coupon_code' ) ) ) {
			$customer_args['coupon'] = $request->get_param( 'coupon_code' );

			// Clear Stripe object cache so dynamic values are available.
			// @todo implement cache clearing within Stripe_Object_Query_Trait
			// when it is available in this namespace.
			delete_transient( 'simpay_stripe_' . $customer_args['coupon'] );
		}

		// Attach email.
		if ( isset( $form_values['simpay_email'] ) ) {
			/** @var string $email */
			$email                  = $form_values['simpay_email'];
			$customer_args['email'] = sanitize_text_field( $email );
		}

		// Attach name.
		if ( isset( $form_values['simpay_customer_name'] ) ) {
			/** @var string $name */
			$name                  = $form_values['simpay_customer_name'];
			$customer_args['name'] = sanitize_text_field( $name );
		}

		// Attach phone number.
		if ( isset( $form_values['simpay_telephone'] ) ) {
			/** @var string $phone */
			$phone                  = $form_values['simpay_telephone'];
			$customer_args['phone'] = sanitize_text_field( $phone );
		}

		// Attach a Tax ID.
		if ( isset( $form_values['simpay_tax_id'] ) ) {
			/** @var string $tax_id_type */
			$tax_id_type = isset( $form_values['simpay_tax_id_type'] )
				? $form_values['simpay_tax_id_type']
				: '';

			$valid_tax_id_types = i18n\get_stripe_tax_id_types();

			if ( false === array_key_exists( $tax_id_type, $valid_tax_id_types ) ) {
				throw new Exception(
					esc_html__( 'Please select a valid Tax ID type.', 'stripe' )
				);
			}

			/** @var string $tax_id */
			$tax_id = $form_values['simpay_tax_id'];
			$tax_id = sanitize_text_field( $tax_id );

			$customer_args['tax_id_data'] = array(
				array(
					'type'  => $tax_id_type,
					'value' => $tax_id,
				),
			);
		}

		// Attach billing address.
		/** @var array<string, string|array<string, string>> */
		$billing_address = $request->get_param( 'billing_address' );

		if ( $billing_address ) {
			$customer_args['address'] = isset( $billing_address['address'] )
				? $billing_address['address']
				: null;

			if ( isset( $billing_address['name'] ) && ! isset( $customer_args['name'] ) ) {
				$customer_args['name'] = $billing_address['name'];
			}
		}

		// Attach shipping address.
		/** @var array<string, string|array<string, string>> */
		$shipping_address = $request->get_param( 'shipping_address' );

		if ( $shipping_address ) {
			$customer_args['shipping'] = $shipping_address;

			// Set a phone number if available.
			$customer_args['shipping']['phone'] = isset( $customer_args['phone'] )
				? $customer_args['phone']
				: null;
		}

		// Remove null values, Stripe doesn't like them.
		// Do this before Shipping, because we need a value for Shipping Name.
		$customer_args = array_filter(
			$customer_args,
			function( $var ) {
				return ! is_null( $var );
			}
		);

		return $customer_args;
	}

}
