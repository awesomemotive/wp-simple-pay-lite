<?php
/**
 * Update payment method route
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
use SimplePay\Core\RestApi\Internal\Payment\Exception\ValidationException;
use SimplePay\Core\RestApi\Internal\Payment\Utils\SchemaUtils;
use WP_REST_Response;
use WP_REST_Server;

/**
 * UpdatePaymentMethodRoute class.
 *
 * @since 4.7.0
 */
class UpdatePaymentMethodRoute extends AbstractPaymentRoute {

	/**
	 * Registers the `POST /wpsp/__internal__/payment/update-payment-method` route.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function register_route() {
		$update_args = array(
			'form_id'           => SchemaUtils::get_form_id_schema(),
			'customer_id'       => SchemaUtils::get_customer_id_schema(),
			'setup_intent_id'   => SchemaUtils::get_setup_intent_id_schema(),
			'payment_method_id' => SchemaUtils::get_payment_method_id_schema(),
			'subscription_id'   => SchemaUtils::get_subscription_id_schema(),
			'subscription_key'  => SchemaUtils::get_subscription_key_schema(),
		);

		$update_item_route = array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_payment_method' ),
			'permission_callback' => array( $this, 'update_payment_method_permissions_check' ),
			'args'                => $update_args,
		);

		register_rest_route(
			$this->namespace,
			'payment/update-payment-method',
			$update_item_route
		);
	}

	/**
	 * Determines if the current request should be able to update the payment method.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The update payment method request.
	 * @return bool
	 */
	public function update_payment_method_permissions_check( $request ) {
		// Next, validate that the form exists.
		// When using a parameter inside of a validation function, we do not know
		// if it has been validated yet. So we need to validate it again.
		/** @var int $form_id */
		$form_id = $request->get_param( 'form_id' );
		$form_id = intval( $form_id );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return false;
		}

		/** @var string $customer_id */
		$customer_id = $request->get_param( 'customer_id' );
		$customer_id = sanitize_text_field( $customer_id );

		/** @var string $subscription_id */
		$subscription_id = $request->get_param( 'subscription_id' );
		$subscription_id = sanitize_text_field( $subscription_id );

		/** @var string $subscription_key */
		$subscription_key = $request->get_param( 'subscription_key' );
		$subscription_key = sanitize_text_field( $subscription_key );

		try {
			$subscription = API\Subscriptions\retrieve(
				$subscription_id,
				$form->get_api_request_args()
			);

			// Ensure customers match.
			if ( $subscription->customer !== $customer_id ) {
				return false;
			}

			// Ensure keys match.
			if (
				! isset( $subscription->metadata->simpay_subscription_key ) ||
				$subscription->metadata->simpay_subscription_key !== $subscription_key
			) {
				return false;
			}

			/** @var string $setup_intent_id */
			$setup_intent_id = $request->get_param( 'setup_intent_id' );

			$setup_intent = API\SetupIntents\retrieve(
				$setup_intent_id,
				$form->get_api_request_args()
			);

			if ( $setup_intent->customer !== $customer_id ) {
				return false;
			}

			// Something went wrong.
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Updates a Subscription's payment method.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The update payment method request.
	 * @throws \SimplePay\Core\RestApi\Internal\Payment\Exception\ValidationException When the request is invalid.
	 * @return \WP_REST_Response
	 */
	public function update_payment_method( $request ) {
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

			/** @var string $subscription_id */
			$subscription_id = $request->get_param( 'subscription_id' );

			/** @var string $payment_method_id */
			$payment_method_id = $request->get_param( 'payment_method_id' );

			API\Subscriptions\update(
				$subscription_id,
				array(
					'default_payment_method' => $payment_method_id,
					'cancel_at_period_end'   => false,
				)
			);

			return new WP_REST_Response( array() );
		} catch ( Exception $e ) {
			return new WP_REST_Response(
				array(
					'message' => $e->getMessage(),
				),
				400
			);
		}
	}

}
