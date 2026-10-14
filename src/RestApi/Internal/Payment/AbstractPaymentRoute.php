<?php
/**
 * Payment route
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\RestApi\Internal\Utils\RateLimitValidationUtils;
use SimplePay\Core\Utils\PaymentFormNonce;

/**
 * AbstractPaymentCreateRoute class.
 *
 * @since 4.7.0
 */
abstract class AbstractPaymentRoute implements SubscriberInterface {

	use RateLimitValidationUtils;

	/**
	 * The REST API namespace.
	 *
	 * @since 4.7.0
	 *
	 * @var non-falsy-string
	 */
	protected $namespace = 'wpsp/__internal__';

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API routes for the endpoint.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	abstract public function register_route();

	/**
	 * Verifies the payment-form nonce on an incoming REST request.
	 *
	 * The `_wpnonce` value is read from one of two locations to support every
	 * caller in the codebase. Read order matters for cache compatibility:
	 *
	 *   1. `$request['_wpnonce']`                (top-level — the fresh per-form
	 *                                             nonce minted by `FormDataRoute`
	 *                                             and sent explicitly by JS).
	 *   2. `$request['form_values']['_wpnonce']` (Pro server-rendered form's
	 *                                             serialized hidden input —
	 *                                             potentially stale on cached
	 *                                             pages).
	 *
	 * The top-level value wins to ensure that fresh nonces sent by cache-aware
	 * callers are not overridden by the stale `_wpnonce` hidden input that
	 * `serialize( paymentForm, { hash: true } )` carries on cached pages.
	 *
	 * The nonce is verified against the per-form action
	 * `simpay_payment_form_{form_id}` first. If that fails, it falls back to
	 * the legacy generic `simpay_payment_form` action so older cached HTML
	 * and third-party integrations keep working during the transition.
	 *
	 * @since 4.17.5
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request The payment request.
	 * @return \WP_Error|true Error if the nonce is missing or invalid; true otherwise.
	 */
	protected function check_form_nonce( $request ) {
		/** @var mixed $form_id_param */
		$form_id_param = $request->get_param( 'form_id' );
		$form_id       = is_numeric( $form_id_param ) ? (int) $form_id_param : 0;

		$nonce = '';

		// Read top-level `_wpnonce` FIRST: this is the explicit "fresh" channel
		// populated by JS callers from the form-data REST response (i.e.
		// `paymentForm.simpayNonce`). It must take precedence over the legacy
		// `form_values._wpnonce` channel because on cached pages the inline
		// `_wpnonce` hidden input in the serialized form HTML is potentially
		// hours-to-days old and would fail verification under the shortened
		// 1h TTL the form-data route mints under.
		/** @var mixed $top_level */
		$top_level = $request->get_param( '_wpnonce' );
		if ( is_string( $top_level ) && '' !== $top_level ) {
			$nonce = $top_level;
		}

		// Fall back to `form_values._wpnonce` (Pro server-rendered form's
		// serialized hidden input) when no explicit top-level nonce is sent.
		if ( '' === $nonce ) {
			/** @var mixed $form_values */
			$form_values = $request->get_param( 'form_values' );
			if (
				is_array( $form_values ) &&
				isset( $form_values['_wpnonce'] ) &&
				is_string( $form_values['_wpnonce'] )
			) {
				$nonce = $form_values['_wpnonce'];
			}
		}

		// Verify the per-form action first; fall back to the legacy generic
		// action for backward compatibility with older form HTML. A missing
		// nonce never verifies.
		if ( PaymentFormNonce::verify( $nonce, $form_id ) ) {
			return true;
		}

		// A dedicated error code, so the frontend refreshes the nonce and
		// retries only for nonce failures, not for any `rest_forbidden`.
		return new \WP_Error(
			PaymentFormNonce::ERROR_CODE,
			__( 'Invalid request. Please try again.', 'stripe' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}
}
