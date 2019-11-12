<?php
/**
 * Payment receipt/confirmation functionality.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Payment_Confirmation\Template_Tags;

use SimplePay\Core\Payments;
use function SimplePay\Core\SimplePay;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves available template tags used in Payment Confirmation content.
 *
 * @since 3.6.0 Removed default values and `type` configuration. Values are computed based
 *              on applied filters, and if nothing is applied they default to an empty string,
 *              matching the previous behavior.
 *
 * @param array $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return array
 */
function get_tags( $payment_confirmation_data ) {
	$tags = array(
		'charge-id',
		'charge-date',
		'company-name',
		'item-description',
		'total-amount',
	);

	// Backwards compatibility.

	// Setup a backwards compatible Payment object.
	$payment = new Payments\Payment( $payment_confirmation_data['form'] );

	if ( ! empty( $payment_confirmation_data['paymentintents']->data ) ) {
		// Retrieve the first charge so the action can be called to maintain compatibility.
		$charges = current( $payment_confirmation_data['paymentintents']->data )->charges;

		if ( ! empty( $charges ) ) {
			$charge = current( $charges->data );

			$payment->customer       = $payment_confirmation_data['customer'];
			$payment->charge         = $charge;

			if ( $payment->charge ) {
				$payment->charge->source = $charge->billing_details;
			}
		}
	}

	/**
	 * Filters available template tags used in Payment Confirmation content.
	 *
	 * @since unknown
	 *
	 * @param array $tags Payment Confirmation template tags.
	 * @param Payment $payment Deprecated.
	 */
	$tags = apply_filters( 'simpay_payment_details_template_tags', $tags, $payment );

	return $tags;
}

/**
 * Parses confirmation content and apply registered template tags.
 *
 * @since 3.6.0
 *
 * @param string $content Payment confirmation content.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function parse_content( $content, $payment_confirmation_data ) {
	$tags = get_tags( $payment_confirmation_data );

	foreach ( $tags as $tag => $deprecated ) {
		$value = '';

		// <= 3.5.x support.
		// @link https://github.com/wpsimplepay/WP-Simple-Pay-Snippet-Library/blob/master/custom-template-tags.php
		if ( is_array( $deprecated ) ) {
			$value = $deprecated['value'];
		} else {
			$tag = $deprecated;

			/**
			 * Filters the value used to replace the template tag with.
			 *
			 * @since 3.6.0
			 *
			 * @param string $value Default value (empty string).
			 * @param array  $payment_confirmation_data {
			 *   Contextual information about this payment confirmation.
			 *
			 *   @type \Stripe\Customer               $customer Stripe Customer
			 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
			 *   @type object                         $subscriptions Subscriptions associated with the Customer.
			 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
			 * }
			 */
			$value = apply_filters(
				sprintf( 'simpay_payment_confirmation_template_tag_%s', $tag ),
				$value,
				$payment_confirmation_data
			);
		}

		$content = str_replace( sprintf( '{%s}', $tag ), $value, $content );
	}

	return $content;
}

/**
 * Replaces {charge-id} with the Customer's first PaymentIntent's first Charge ID.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function charge_id( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['paymentintents'] ) ) {
		return $value;
	}

	$charges = current( $payment_confirmation_data['paymentintents'] )->charges;

	// Do nothing if there are no charges available in the PaymentIntent.
	if ( empty( $charges ) ) {
		return $value;
	}

	// Assume and use the first charge in the list.
	$first_charge = current( $charges->data );

	return esc_html( $first_charge->id );
}
add_filter( 'simpay_payment_confirmation_template_tag_charge-id', __NAMESPACE__ . '\\charge_id', 10, 3 );

/**
 * Replaces {charge-date} with the PaymentIntent's first Charge date.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function charge_date( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['paymentintents'] ) ) {
		return $value;
	}

	$charges = current( $payment_confirmation_data['paymentintents'] )->charges;

	// Do nothing if there are no charges available in the PaymentIntent.
	if ( empty( $charges ) ) {
		return $value;
	}

	// Assume and use the first charge in the list.
	$first_charge = current( $charges->data );

	// Localize format.
	$value = date_i18n( get_option( 'date_format' ), $first_charge->created );

	/**
	 * @deprecated 3.6.0
	 */
	$value = apply_filters_deprecated(
		'simpay_details_order_date',
		array( $value ),
		'3.6.0',
		'simpay_payment_confirmation_template_tag_charge-date'
	);

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_charge-date', __NAMESPACE__ . '\\charge_date', 10, 3 );

/**
 * Replaces {total-amount} with the PaymentIntent's first Charge amount.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function charge_amount( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['paymentintents'] ) ) {
		return $value;
	}

	$charges = current( $payment_confirmation_data['paymentintents'] )->charges;

	// Do nothing if there are no charges available in the PaymentIntent.
	if ( empty( $charges ) ) {
		return $value;
	}

	// Assume and use the first charge in the list.
	$first_charge = current( $charges->data );
	
	$value = simpay_format_currency(
		simpay_convert_amount_to_dollars( $first_charge->amount ),
		$first_charge->currency
	);

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_total-amount', __NAMESPACE__ . '\\charge_amount', 10, 3 );

/**
 * Replaces {company-name} with the form data's set Company Name.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function company_name( $value, $payment_confirmation_data ) {
	return esc_html( $payment_confirmation_data['form']->company_name );
}
add_filter( 'simpay_payment_confirmation_template_tag_company-name', __NAMESPACE__ . '\\company_name', 10, 3 );

/**
 * Replaces {item-description} with the form data's set description.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function item_description( $value, $payment_confirmation_data ) {
	return esc_html( $payment_confirmation_data['form']->item_description );
}
add_filter( 'simpay_payment_confirmation_template_tag_item-description', __NAMESPACE__ . '\\item_description', 10, 3 );
