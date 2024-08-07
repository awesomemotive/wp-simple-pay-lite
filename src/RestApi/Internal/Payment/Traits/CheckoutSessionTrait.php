<?php
/**
 * Checkout Session trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Traits;

use SimplePay\Core\API;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;

/**
 * CheckoutSessionTrait trait.
 *
 * @since 4.7.0
 */
trait CheckoutSessionTrait {

	/**
	 * Creates a Checkout Session for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request     $request The payment request.
	 * @param array<string, mixed> $session_args Checkout Session arguments.
	 * @return \SimplePay\Vendor\Stripe\Checkout\Session
	 */
	private function create_checkout_session( $request, $session_args ) {
		$form        = PaymentRequestUtils::get_form( $request );
		$form_values = PaymentRequestUtils::get_form_values( $request );

		/** @var string|null $customer_id */
		$customer_id = isset( $session_args['customer'] )
			? $session_args['customer']
			: null;

		/**
		 * Filters arguments used to create a Checkout Session from a payment form request.
		 *
		 * @since 3.6.0
		 *
		 * @param array<string, mixed>           $session_args Checkout Session arguments.
		 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array<mixed>                   $form_data Deprecated.
		 * @param array<string, mixed>           $form_values Form values.
		 * @param string|null                    $customer_id Customer ID, if being used.
		 */
		$session_args = apply_filters(
			'simpay_get_session_args_from_payment_form_request',
			$session_args,
			$form,
			array(),
			$form_values,
			$customer_id
		);

		/**
		 * Allows processing before a Checkout\Session is created from a payment form request.
		 *
		 * @since 3.6.0
		 *
		 * @param array<string, mixed>           $session_args Checkout Session arguments.
		 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array<mixed>                   $form_data Deprecated.
		 * @param array<string, mixed>           $form_values Form values.
		 * @param string|null                    $customer_id Customer ID, if being used.
		 */
		do_action(
			'simpay_before_checkout_session_from_payment_form_request',
			$session_args,
			$form,
			array(),
			$form_values,
			$customer_id
		);

		$session = API\CheckoutSessions\create(
			$session_args,
			$form->get_api_request_args()
		);

		/**
		 * Allows further processing after a Checkout\Session is created from a payment form request.
		 *
		 * @since 3.6.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Checkout\Session $session Checkout Session.
		 * @param \SimplePay\Core\Abstracts\Form            $form Form instance.
		 * @param array<mixed>                              $form_data Deprecated.
		 * @param array<string, mixed>                      $form_values Form values.
		 * @param string|null                               $customer_id Customer ID, if being used.
		 */
		do_action(
			'simpay_after_checkout_session_from_payment_form_request',
			$session,
			$form,
			array(),
			$form_values,
			$customer_id
		);

		return $session;
	}

}
