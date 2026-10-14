<?php
/**
 * Webhook: Invoice payload
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.5
 */

namespace SimplePay\Core\Webhook;

/**
 * InvoicePayload class.
 *
 * Reads IDs from a Stripe Invoice in either the legacy (<= 2024-06-20) or the
 * newer API payload shape.
 *
 * The plugin pins its outbound API version, but webhook payloads are versioned
 * by the Stripe account / endpoint configuration, so a handler can receive
 * either shape. This is the single source of truth for both
 * `SimplePay\Pro\Webhooks\get_invoice_*_id()` and the Transaction observer.
 *
 * @since 4.17.5
 */
class InvoicePayload {

	/**
	 * Returns the Subscription ID from an Invoice payload.
	 *
	 * Reads the legacy top-level `subscription` field, then the newer
	 * `parent.subscription_details.subscription` location. Both may be an ID
	 * string or an expanded object.
	 *
	 * @since 4.17.5
	 *
	 * @param object $invoice Stripe Invoice object.
	 * @return string|null Subscription ID, or null if none is present.
	 */
	public static function get_subscription_id( $invoice ) {
		/** @var \stdClass $invoice */

		if ( ! empty( $invoice->subscription ) ) {
			return self::get_id( $invoice->subscription );
		}

		if ( ! empty( $invoice->parent->subscription_details->subscription ) ) {
			return self::get_id( $invoice->parent->subscription_details->subscription );
		}

		return null;
	}

	/**
	 * Returns the PaymentIntent ID from an Invoice payload.
	 *
	 * Reads the legacy top-level `payment_intent` field, then the newer
	 * `payments.data[].payment.payment_intent` list. In the list, the payment
	 * that paid the Invoice wins; otherwise the last open attempt is used.
	 * Canceled attempts are never returned.
	 *
	 * `payments` is not included in webhook payloads unless expanded, so a
	 * newer-shape payload may carry neither field -- callers must handle null.
	 *
	 * @since 4.17.5
	 *
	 * @param object $invoice Stripe Invoice object.
	 * @return string|null PaymentIntent ID, or null if none is present.
	 */
	public static function get_payment_intent_id( $invoice ) {
		/** @var \stdClass $invoice */

		if ( ! empty( $invoice->payment_intent ) ) {
			return self::get_id( $invoice->payment_intent );
		}

		if ( empty( $invoice->payments->data ) || ! is_iterable( $invoice->payments->data ) ) {
			return null;
		}

		$payment_intent_id = null;

		foreach ( $invoice->payments->data as $invoice_payment ) {
			/** @var \stdClass $invoice_payment */
			if (
				! isset( $invoice_payment->payment->type ) ||
				'payment_intent' !== $invoice_payment->payment->type ||
				empty( $invoice_payment->payment->payment_intent )
			) {
				continue;
			}

			$status = isset( $invoice_payment->status )
				? $invoice_payment->status
				: null;

			// A canceled attempt never paid the Invoice.
			if ( 'canceled' === $status ) {
				continue;
			}

			$id = self::get_id( $invoice_payment->payment->payment_intent );

			if ( null === $id ) {
				continue;
			}

			$payment_intent_id = $id;

			// Prefer the payment that actually paid the Invoice.
			if ( 'paid' === $status ) {
				break;
			}
		}

		return $payment_intent_id;
	}

	/**
	 * Returns the ID of a Stripe reference that may be an ID string or an
	 * expanded object.
	 *
	 * @since 4.17.5
	 *
	 * @param mixed $reference ID string or expanded object.
	 * @return string|null
	 */
	private static function get_id( $reference ) {
		if ( is_string( $reference ) && '' !== $reference ) {
			return $reference;
		}

		if ( is_object( $reference ) && isset( $reference->id ) && is_string( $reference->id ) ) {
			return $reference->id;
		}

		return null;
	}
}
