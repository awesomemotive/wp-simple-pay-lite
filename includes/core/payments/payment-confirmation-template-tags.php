<?php
/**
 * Payment confirmation smart tags
 *
 * @package SimplePay\Core\Payments\Payment_Confirmation\Template_Tags
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Payment_Confirmation\Template_Tags;

use SimplePay\Core\Utils;
use SimplePay\Core\Payments;
use SimplePay\Core\Payments\Stripe_API;
use function SimplePay\Core\SimplePay;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves available smart tags used in Payment Confirmation content.
 *
 * @since 3.6.0 Removed default values and `type` configuration. Values are computed based
 *              on applied filters, and if nothing is applied they default to an empty string,
 *              matching the previous behavior.
 *
 * @param array $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return array
 */
function get_tags( $payment_confirmation_data ) {
	$tags = array(
		'form-title',
		'form-description',
		'card-brand',
		'charge-id',
		'charge-date',
		'company-name',
		'customer-name',
		'item-description',
		'card-last4',
		'name-on-card',
		'total-amount',
		'payment-type',
	);

	// Backwards compatibility.
	if ( isset( $payment_confirmation_data['form'] ) ) {

		// Setup a backwards compatible Payment object.
		$payment = new Payments\Payment( $payment_confirmation_data['form'] );

		if ( ! empty( $payment_confirmation_data['paymentintents']->data ) ) {
			// Retrieve the first charge so the action can be called to maintain compatibility.
			$charges = current( $payment_confirmation_data['paymentintents']->data )->charges;

			if ( ! empty( $charges ) ) {
				$charge = current( $charges->data );

				$payment->customer = $payment_confirmation_data['customer'];
				$payment->charge   = $charge;

				if ( $payment->charge ) {
					$payment->charge->source = $charge->billing_details;
				}
			}
		}
	} else {
		$payment = new \stdClass();
	}

	/**
	 * Filters available smart tags used in Payment Confirmation content.
	 *
	 * @since unknown
	 *
	 * @param array $tags Payment Confirmation smart tags.
	 * @param Payment $payment Deprecated.
	 */
	$tags = apply_filters( 'simpay_payment_details_template_tags', $tags, $payment );

	return $tags;
}

/**
 * Parses confirmation content and apply registered smart tags.
 *
 * @since 3.6.0
 *
 * @param string $content Payment confirmation content.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
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
			$value   = $deprecated['value'];
			$content = replace_tag( $tag, $value, $content );
		} else {
			$tag            = $deprecated;
			$tags_with_keys = get_tags_with_keys( $tag, $content );

			if ( has_filter( sprintf( 'simpay_payment_confirmation_template_tag_%s', $tag ) ) ) {
				foreach ( $tags_with_keys as $tag_with_keys ) {
					/**
					 * Filters the value used to replace the smart tag with.
					 *
					 * @since 3.6.0
					 * @since 3.7.0 Name of smart tag, excluding curly braces.
					 * @since 3.7.0 Name of smart tag with keys, excluding curly braces.
					 *
					 * @param string $value Default value (empty string).
					 * @param array  $payment_confirmation_data {
					 *   Contextual information about this payment confirmation.
					 *
					 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
					 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
					 *   @type object                         $subscriptions Subscriptions associated with the Customer.
					 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
					 * }
					 * @param string  $tag Payment confirmation smart tag name, excluding curly braces.
					 * @param array   $tags_with_keys Payment confirmation smart tags including keys, excluding curly braces.
					 */
					$value = apply_filters(
						sprintf( 'simpay_payment_confirmation_template_tag_%s', $tag ), // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
						$value,
						$payment_confirmation_data,
						$tag,
						$tag_with_keys
					);

					$content = replace_tag( $tag_with_keys, $value, $content );
				}
			}
		}
	}

	return $content;
}

/**
 * Replaces a base tag (and optional keys) with a found value.
 *
 * @since 3.7.0
 *
 * @param string $tag Payment confirmation smart tag (and optional keys), excluding curly braces.
 * @param string $value Tag value.
 * @param string $content Payment confirmation content.
 * @return string
 */
function replace_tag( $tag, $value, $content ) {
	return str_replace( '{' . $tag . '}', $value, $content );
}

/**
 * Finds full tag matches with keys from a registered tag base.
 *
 * `metadata:foo` will match the registered `metadata` tag.
 *
 * @since 3.7.0
 *
 * @param string $tag Payment confirmation smart tag name, excluding curly braces.
 * @param string $content Payment confirmation content.
 * @return string $tags_with_keys Tag including keys, excluding curly braces.
 */
function get_tags_with_keys( $tag, $content ) {
	$tags_with_keys = array();

	preg_match_all( '/{' . $tag . '(:.*)?}/U', $content, $matches );

	if ( ! empty( $matches ) ) {
		$full_matches = $matches[0];

		foreach ( $full_matches as $match ) {
			// Remove { from start and } from end.
			$tags_with_keys[] = substr( $match, 1, -1 );
		}
	}

	return $tags_with_keys;
}

/**
 * Retrieves any potential keys from a full tag.
 *
 * Suffixes are split via a colon :.
 *
 * @since 3.7.0
 *
 * @param string $tag_with_keys Payment confirmation smart tag name including keys, excluding curly braces.
 * @return array List of tag keys.
 */
function get_tag_keys( $tag_with_keys ) {
	$pieces = array_map(
		'trim',
		explode( ':', $tag_with_keys )
	);

	array_shift( $pieces );

	return $pieces;
}

/**
 * Finds the deepest object property given a list of keys.
 *
 * @since 3.7.0
 *
 * @param array  $keys Deep keys to look through.
 * @param object $ref Object reference to search through.
 * @return string $value Property value.
 */
function get_object_property_deep( $keys, $ref ) {
	$value = $ref;

	foreach ( $keys as $key ) {
		if ( is_array( $value ) ) {
			if ( isset( $value[ $key ] ) ) {
				$value = $value[ $key ];
			} else {
				$value = '';
				break;
			}
		} else {
			if ( isset( $value->$key ) ) {
				$value = $value->$key;
			} else {
				$value = '';
				break;
			}
		}
	}

	return $value;
}

/**
 * Replaces the {customer-name} template tag with the name of the Customer.
 *
 * @since 4.5.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function customer_name( $value, $payment_confirmation_data ) {

	if ( empty( $payment_confirmation_data['customer'] ) ) {
		return $value;
	}

	return esc_html( $payment_confirmation_data['customer']->name );
}
add_filter( 'simpay_payment_confirmation_template_tag_customer-name', __NAMESPACE__ . '\\customer_name', 10, 3 );

/**
 * Replaces the {card-brand} template tag with the name on the Customer's credit card.
 *
 * @since 4.5.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type \SimplePay\Core\Payments\Stripe_API   $payment_methods or something
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function card_brand( $value, $payment_confirmation_data ) {
	// Get all cards.
	$payment_methods = \SimplePay\Core\Payments\Stripe_API::request(
		'PaymentMethod',
		'all',
		array(
			'customer' => $payment_confirmation_data['customer']->id,
			'type'     => 'card',
		),
		$payment_confirmation_data['form']->get_api_request_args()
	);

	if ( empty( $payment_methods->data ) ) {
		return $value;
	}

	// Find the most recent card.
	$card = current( $payment_methods->data );

	if ( empty( $card->card ) ) {
		return $value;
	}

	return ucwords( $card->card->brand );
}
add_filter( 'simpay_payment_confirmation_template_tag_card-brand', __NAMESPACE__ . '\\card_brand', 10, 3 );

/**
 * Replaces the {card-last4} template tag with the name on the Customer's credit card.
 *
 * @since 4.5.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type \SimplePay\Core\Payments\Stripe_API   $payment_methods or something
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function card_last4( $value, $payment_confirmation_data ) {
	// Get all cards.
	$payment_methods = \SimplePay\Core\Payments\Stripe_API::request(
		'PaymentMethod',
		'all',
		array(
			'customer' => $payment_confirmation_data['customer']->id,
			'type'     => 'card',
		),
		$payment_confirmation_data['form']->get_api_request_args()
	);

	if ( empty( $payment_methods->data ) ) {
		return $value;
	}

	// Find the most recent card.
	$card = current( $payment_methods->data );

	if ( empty( $card->card ) ) {
		return $value;
	}

	return ( $card->card->last4 );
}
add_filter( 'simpay_payment_confirmation_template_tag_card-last4', __NAMESPACE__ . '\\card_last4', 10, 3 );

/**
 * Replaces {charge-id} with the Customer's first PaymentIntent's first Charge ID.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
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
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
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

	// Localize to current timezone and formatting.
	$value = get_date_from_gmt(
		date( 'Y-m-d H:i:s', $first_charge->created ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		'U'
	);

	$value = date_i18n( get_option( 'date_format' ), $value );

	/**
	 * Filters the {charge-date} smart tag value.
	 *
	 * @since 3.0.0
	 * @deprecated 3.6.0
	 *
	 * @param string $value Charge date.
	 */
	$value = apply_filters_deprecated(
		'simpay_details_order_date',
		array(
			$value,
		),
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
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
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
		$first_charge->amount,
		$first_charge->currency
	);

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_total-amount', __NAMESPACE__ . '\\charge_amount', 10, 3 );

/**
 * Replaces {company-name} or {form-title} with the Payment Form's title.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
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
add_filter(
	'simpay_payment_confirmation_template_tag_form-title',
	__NAMESPACE__ . '\\company_name',
	10,
	3
);

/**
 * Replaces {item-description} or {form-description} with the Payment Form's
 * description.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
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
add_filter(
	'simpay_payment_confirmation_template_tag_form-description',
	__NAMESPACE__ . '\\item_description',
	10,
	3
);

/**
 * Replaces {payment-type} with the payment type.
 *
 * @since 4.1.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function payment_type( $value, $payment_confirmation_data ) {
	if (
		isset( $payment_confirmation_data['subscriptions'] ) &&
		! empty( $payment_confirmation_data['subscriptions'] )
	) {
		return esc_html__( 'Subscription', 'stripe' );
	} else {
		return esc_html__( 'One time', 'stripe' );
	}
}
add_filter(
	'simpay_payment_confirmation_template_tag_payment-type',
	__NAMESPACE__ . '\\payment_type',
	10,
	3
);

/**
 * Returns a list of available smart tags and their descriptions.
 *
 * @todo Temporary until this can be more easily generated through a tag registry.
 *
 * @since 4.0.0
 *
 * @return array
 */
function __unstable_get_tags_and_descriptions() { // phpcs:ignore PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.FunctionDoubleUnderscore
	$tags = array(
		'form-title'       => esc_html__(
			'The form\'s title.',
			'stripe'
		),
		'form-description' => esc_html__(
			'The form\'s description.',
			'stripe'
		),
		'total-amount'     => esc_html__(
			'The total price of the payment.',
			'stripe'
		),
		'customer-name'    => esc_html__(
			'The value of the Name form field.',
			'stripe'
		),
		'charge-date'      => esc_html__(
			'The charge date returned from Stripe.',
			'stripe'
		),
		'charge-id'        => esc_html__(
			'The unique charge ID returned from Stripe.',
			'stripe'
		),
	);

	if ( class_exists( 'SimplePay\Pro\SimplePayPro' ) ) {
		$tags['payment-type'] = esc_html__(
			'The type of payment (one-time or recurring).',
			'stripe'
		);

		$tags['card-brand'] = esc_html__(
			'The brand of the card used. Visa, Amex, etc.',
			'stripe'
		);

		$tags['card-last4'] = esc_html__(
			'The last four digits of the card used.',
			'stripe'
		);

		$tags['tax-amount'] = esc_html__(
			'The calculated tax amount based on the total and the tax percent setting.',
			'stripe'
		);
	}

	return $tags;
}

/**
 * Prints a list of available smart tags and their descriptions.
 *
 * @todo Temporary until this can be more easily generated through a tag registry.
 *
 * @since 4.0.0
 *
 * @param string $description smart tag description.
 * @param array  $tags List of smart tags and descriptions.
 */
function __unstable_print_tag_list( $description, $tags ) { // phpcs:ignore PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.FunctionDoubleUnderscore
	printf(
		'<p class="description">%s</p>',
		esc_html( $description )
	);

	printf(
		'<p><strong>%s</strong></p>',
		esc_html__( 'Available smart tags:', 'stripe' )
	);

	foreach ( $tags as $tag_id => $description ) {
		printf(
			'<p><code>{%s}</code> - %s</p>',
			esc_html( $tag_id ),
			$description
		);
	}
}
