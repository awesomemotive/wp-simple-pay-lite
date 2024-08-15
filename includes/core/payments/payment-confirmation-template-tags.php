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
		'customer-email',
		'customer-url',
		'item-description',
		'card-last4',
		'name-on-card',
		'total-amount',
		'payment-type',
		'payment-url',
		'subtotal-amount',
		'receipt',
	);

	/**
	 * Filters available smart tags used in Payment Confirmation content.
	 *
	 * @since unknown
	 *
	 * @param array $tags Payment Confirmation smart tags.
	 */
	$tags = apply_filters( 'simpay_payment_details_template_tags', $tags );

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
					$keys = explode( '|', $tag_with_keys );

					$tag_with_keys  = isset( $keys[0] ) ? trim( $keys[0] ) : '';
					$fallback_value = isset( $keys[1] ) ? trim( trim( substr( $keys[1], 1, -1 ), '"' ) ) : '';

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

					if ( empty( $value ) ) {
						$value = $fallback_value;
					}

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
	// Remove non-breaking spaces for tag only.
	$content = preg_replace_callback(
		'/\{([^}]*)\}/',
		function ( $matches ) {
			return str_replace( "\xC2\xA0", '', $matches[0] );
		},
		$content
	);

	$pattern = '/{\s*' . preg_quote( $tag, '/' ) . '(?:\s*\|\s*"([^"]*)")?\s*}/';

	// Escape dollar signs in the value to prevent them from being interpreted as backreferences.
	$escaped_value = str_replace( '$', '\$', $value );

	$content = preg_replace( $pattern, $escaped_value, $content );

	return $content;
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
	// Remove non-breaking spaces for tag only.
	$content        = preg_replace_callback(
		'/\{([^}]*)\}/',
		function ( $matches ) {
			return str_replace( "\xC2\xA0", '', $matches[0] );
		},
		$content
	);
	$tags_with_keys = array();
	$pattern        = '/{\s*' . $tag . '(?::[\w\s-]+)*(?:\s*\|\s*["\'][^"\']*["\'])?\s*}/';

	preg_match_all( $pattern, $content, $matches );

	if ( ! empty( $matches[0] ) ) {
		foreach ( $matches[0] as $match ) {
			$tags_with_keys[] = trim( $match, '{} ' );
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
		} elseif ( isset( $value->$key ) ) {
				$value = $value->$key;
		} else {
			$value = '';
			break;
		}
	}

	return $value;
}

/**
 * Replaces the {receipt} Smart Tag with the purchase receipt data.
 *
 * @since 4.11.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form    $form Payment form.
 *   @type object                            $subscriptions Subscriptions associated with the Customer.
 *   @type object                            $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function receipt( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['customer'] ) ) {
		return $value;
	}

	$form       = $payment_confirmation_data['form'];
	$tax_status = get_post_meta( $form->id, '_tax_status', true );

	$currency        = 'USD';
	$line_items      = array();
	$subtotal        = 0;
	$discount        = 0;
	$fee_recovery    = 0;
	$setup_fee       = 0;
	$tax             = 0;
	$total           = 0;
	$recurring       = '';
	$price_instances = null;
	$is_recurring    = false;

	// Subscription (on-site or Stripe Checkout) or One-Time Invoice (on-site).
	if ( isset( $payment_confirmation_data['initial_invoice'] ) ) {
		$invoice = $payment_confirmation_data['initial_invoice'];

		$currency = strtoupper( $invoice->currency );

		$discount = array_reduce(
			$invoice->total_discount_amounts,
			function ( $carry, $discount_amount ) {
				return $carry + $discount_amount->amount;
			},
			0
		);

		if ( $invoice->subscription ) {
			$fee_recovery = isset( $invoice->subscription->metadata->simpay_fee_recovery_initial_unit_amount )
				? $invoice->subscription->metadata->simpay_fee_recovery_initial_unit_amount
				: ( isset( $invoice->subscription->metadata->simpay_fee_recovery_unit_amount )
					? $invoice->subscription->metadata->simpay_fee_recovery_unit_amount
					: 0 );

			$is_recurring = true;
		} else {
			$fee_recovery = isset( $invoice->metadata->simpay_fee_recovery_unit_amount )
				? $invoice->metadata->simpay_fee_recovery_unit_amount
				: 0;
		}

		$subtotal = $invoice->subtotal;
		$subtotal = $subtotal - $fee_recovery;

		$tax = array_reduce(
			$invoice->total_tax_amounts,
			function ( $carry, $tax_amount ) {
				return $carry + $tax_amount->amount;
			},
			0
		);

		$total = $invoice->total;

		if ( $invoice->subscription ) {
			if ( isset( $invoice->subscription->metadata->simpay_price_instances ) ) {
				$price_instances = $invoice->subscription->metadata->simpay_price_instances;
			} elseif ( isset( $payment_confirmation_data['checkout_session'] ) ) {
				$price_instances = $payment_confirmation_data['checkout_session']->metadata->simpay_price_instances;
			}
		} else {
			$price_instances = $invoice->metadata->simpay_price_instances;
		}
	} else {
		$payment_intent = current( $payment_confirmation_data['paymentintents'] );

		$currency = $payment_intent->currency;

		$fee_recovery = isset( $payment_intent->metadata->simpay_fee_recovery_unit_amount )
			? $payment_intent->metadata->simpay_fee_recovery_unit_amount
			: 0;

		if ( isset( $payment_confirmation_data['checkout_session'] ) ) {
			$session  = $payment_confirmation_data['checkout_session'];
			$discount = $session->total_details->amount_discount;
			$tax      = $session->total_details->amount_tax;
			$subtotal = $session->amount_subtotal;
			$total    = $session->amount_total;
		} else {
			$discount = isset( $payment_intent->metadata->simpay_discount_unit_amount )
				? $payment_intent->metadata->simpay_discount_unit_amount
				: 0;

			$tax = isset( $payment_intent->metadata->simpay_tax_unit_amount_exclusive )
				? $payment_intent->metadata->simpay_tax_unit_amount_exclusive
				: 0;

			$subtotal = $payment_intent->amount - $fee_recovery - $tax;

			$total = $payment_intent->amount;
		}

		$is_recurring = false;

		$price_instances = $payment_intent->metadata->simpay_price_instances;
	}

	if ( ! is_null( $price_instances ) ) {
		$price_instances = explode( '|', $price_instances );
		$price_instances = array_map(
			function ( $price_instance ) {
				$parts = explode( ':', $price_instance );

				return array(
					'instance_id' => $parts[0],
					'quantity'    => (int) $parts[1],
					'amount'      => isset( $parts[2] ) ? (int) $parts[2] : 0,
				);
			},
			$price_instances
		);

		foreach ( $price_instances as $price_instance ) {
			$price_option = simpay_payment_form_prices_get_price_by_instance_id(
				$form,
				$price_instance['instance_id']
			);

			$amount = 0 === $price_instance['amount']
				? $price_instance['quantity'] * $price_option->unit_amount
				: $price_instance['amount'];

			if ( isset( $price_option->recurring, $price_option->recurring['trial_period_days'] ) ) {
				$amount   = 0;
				$is_trial = true;
			} else {
				$is_trial = false;
			}

			$line_items[] = array(
				'description' => $price_option->get_display_label(),
				'quantity'    => $price_instance['quantity'],
				'unit_amount' => $price_option->unit_amount,
				'amount'      => $amount,
				'is_trial'    => $is_trial,
			);

			if (
				$is_recurring &&
				$price_option->line_items &&
				! empty( $price_option->line_items )
			) {
				foreach ( $price_option->line_items as $line_item ) {
					$setup_fee += $line_item['unit_amount'];
				}
			}
		}
	}

	if ( function_exists( '\SimplePay\Pro\Payments\Payment_Confirmation\Template_Tags\recurring_amount' ) ) {
		$recurring = \SimplePay\Pro\Payments\Payment_Confirmation\Template_Tags\recurring_amount(
			'',
			$payment_confirmation_data
		);
	}

	ob_start();

	include SIMPLE_PAY_DIR . '/views/smart-tag-receipt.php'; // @phpstan-ignore-line

	return ob_get_clean();
}
add_filter( 'simpay_payment_confirmation_template_tag_receipt', __NAMESPACE__ . '\\receipt', 10, 2 );

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
 * Replaces the {customer-email} template tag with the email of the Customer.
 *
 * @since 4.7.3
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer             $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form                $form Payment form.
 *   @type array<\SimplePay\Vendor\Stripe\Subscription>  $subscriptions Subscriptions associated with the Customer.
 *   @type array<\SimplePay\Vendor\Stripe\PaymentIntent> $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function customer_email( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['customer'] ) ) {
		return $value;
	}

	return esc_html( $payment_confirmation_data['customer']->email );
}
add_filter(
	'simpay_payment_confirmation_template_tag_customer-email',
	__NAMESPACE__ . '\\customer_email',
	10,
	2
);

/**
 * Replaces the {customer-url} template tag with the URL to the Customer.
 *
 * @since 4.7.3
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer             $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form                $form Payment form.
 *   @type array<\SimplePay\Vendor\Stripe\Subscription>  $subscriptions Subscriptions associated with the Customer.
 *   @type array<\SimplePay\Vendor\Stripe\PaymentIntent> $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function customer_url( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['customer'] ) ) {
		return $value;
	}

	return esc_html(
		sprintf(
			'https://dashboard.stripe.com%s/customers?email=%s',
			simpay_is_test_mode() ? '/test' : '',
			$payment_confirmation_data['customer']->email
		)
	);
}
add_filter(
	'simpay_payment_confirmation_template_tag_customer-url',
	__NAMESPACE__ . '\\customer_url',
	10,
	2
);

/**
 * Replaces the {payment-url} template tag with the URL to the Payment.
 *
 * @since 4.7.3
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer             $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form                $form Payment form.
 *   @type array<\SimplePay\Vendor\Stripe\Subscription>  $subscriptions Subscriptions associated with the Customer.
 *   @type array<\SimplePay\Vendor\Stripe\PaymentIntent> $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function payment_url( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['paymentintents'] ) ) {
		return $value;
	}

	$payment = current( $payment_confirmation_data['paymentintents'] );

	return esc_html(
		sprintf(
			'https://dashboard.stripe.com%s/payments/%s',
			simpay_is_test_mode() ? '/test' : '',
			$payment->id
		)
	);
}
add_filter(
	'simpay_payment_confirmation_template_tag_payment-url',
	__NAMESPACE__ . '\\payment_url',
	10,
	2
);

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

	$payment_intent = current( $payment_confirmation_data['paymentintents'] );

	return esc_html( $payment_intent->id );
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

	$payment_intent = current( $payment_confirmation_data['paymentintents'] );

	// Localize to current timezone and formatting.
	$value = get_date_from_gmt(
		date( 'Y-m-d H:i:s', $payment_intent->created ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
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
	$value         = '';
	$paymentintent = current( $payment_confirmation_data['paymentintents'] );
	if ( empty( $paymentintent ) ) {
		// Get the amount from the Subscription.
		$subscription = current( $payment_confirmation_data['subscriptions'] );
		$value        = simpay_format_currency(
			$subscription->latest_invoice->amount_paid,
			$subscription->currency
		);

	} else {
		// Get the amount from the PaymentIntent.
		$value = simpay_format_currency(
			$paymentintent->amount,
			$paymentintent->currency
		);
	}

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
 * Replaces {subtotal-amount} with the payment subtotal.
 *
 * @since 4.9.0
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
function subtotal_amount( $value, $payment_confirmation_data ) {
	if ( ! simpay_is_upe() ) {
		return $value;
	}

	if ( $payment_confirmation_data['form']->allows_multiple_line_items() ) {
		$invoice = $payment_confirmation_data['initial_invoice'];

		return simpay_format_currency(
			$invoice->subtotal,
			$invoice->currency
		);
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	if ( $subscription ) {
		$payment  = $subscription->latest_invoice->subscription_details;
		$currency = $subscription->currency;
	} else {
		$payment  = current( $payment_confirmation_data['paymentintents'] );
		$currency = $payment->currency;
	}

	return simpay_format_currency(
		$payment->metadata->simpay_unit_amount * $payment->metadata->simpay_quantity,
		$currency
	);
}
add_filter(
	'simpay_payment_confirmation_template_tag_subtotal-amount',
	__NAMESPACE__ . '\\subtotal_amount',
	10,
	2
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

	if ( simpay_is_upe() ) {
		$tags['subtotal-amount'] = esc_html__(
			'The cumulative cost of selected items.',
			'stripe'
		);
	}

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

		$tags['fee-recovery-amount'] = esc_html__(
			'The calculated fee recovery amount based on the total and the fee recovery percent setting.',
			'stripe'
		);

		$tags['receipt'] = esc_html__(
			'The receipt breakdown of the payment including items, adjustments, and totals.',
			'stripe'
		);

		if ( simpay_is_upe() ) {
			$tags['coupon-amount'] = esc_html__(
				'The amount of the coupon applied to the payment.',
				'stripe'
			);
		}
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
