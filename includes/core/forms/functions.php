<?php
/**
 * Payment Form: Functions
 *
 * @package SimplePay\Core\Payment_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

use SimplePay\Core\PaymentForm\PriceOptions;

/**
 * Returns a list of prices associated with a Payment Form's Payment Mode.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return \SimplePay\Core\PaymentForm\PriceOption[]
 */
function simpay_get_payment_form_prices( $form ) {
	if ( false === $form ) {
		return array();
	}

	$price_options = ( new PriceOptions( $form ) )->get_prices();

	/**
	 * Filters the price options for a specific Payment Form.
	 *
	 * @since 4.1.1
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption[] $price_options Price option.
	 * @param \SimplePay\Core\Abstracts\Form            $form Payment Form.
	 */
	$price_options = apply_filters(
		'simpay_get_payment_form_price_options',
		$price_options,
		$form
	);

	return $price_options;
}

/**
 * Retrieves a specific price option.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @param string                         $price_id Price option ID.
 * @return false|\SimplePay\Core\PaymentForm\PriceOption Price option. False if
 *                                                       not found.
 */
function simpay_payment_form_prices_get_price_by_id( $form, $price_id ) {
	$prices = simpay_get_payment_form_prices( $form );
	$prices = array_filter(
		$prices,
		function ( $price ) use ( $price_id ) {
			return $price->id === $price_id;
		}
	);

	if ( empty( $prices ) ) {
		return false;
	}

	return current( $prices );
}

/**
 * Retrieves a specific price option by instance ID.
 *
 * @since 4.11.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @param string                         $instance_id Price option Instance ID.
 * @return false|\SimplePay\Core\PaymentForm\PriceOption Price option. False if
 *                                                       not found.
 */
function simpay_payment_form_prices_get_price_by_instance_id( $form, $instance_id ) {
	$prices = simpay_get_payment_form_prices( $form );
	$prices = array_filter(
		$prices,
		function ( $price ) use ( $instance_id ) {
			return $price->instance_id === $instance_id;
		}
	);

	if ( empty( $prices ) ) {
		return false;
	}

	return current( $prices );
}

/**
 * Determines if the stored price has been defined in Stripe.
 *
 * @since 4.1.0
 *
 * @param string $price_id Price option ID.
 * @return bool
 */
function simpay_payment_form_prices_is_defined_price( $price_id ) {
	if ( null === $price_id ) {
		return false;
	}

	return strpos( $price_id, 'simpay_' ) !== 0;
}

/**
 * Determines if a list of Prices includes a recurring Price.
 *
 * @since 4.1.0
 *
 * @param array array<\SimplePay\Core\PaymentForm\PriceOption> $prices Price options.
 * @return bool
 */
function simpay_payment_form_prices_has_subscription_price( $prices ) {
	$has_subscription = false;

	foreach ( $prices as $price ) {
		if ( null !== $price->recurring ) {
			$has_subscription = true;
			break;
		}
	}

	return $has_subscription;
}

/**
 * Retrieves the first recurring price option.
 *
 * This is only used to fill legacy Payment Form properties and should not be
 * extended.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption[] $prices Price options.
 * @return \SimplePay\Core\PaymentForm\PriceOption[]|array
 */
function __unstable_simpay_get_payment_form_prices_subscription_price( $prices ) {
	$has_subscription = simpay_payment_form_prices_has_subscription_price( $prices );

	if ( false === $has_subscription ) {
		return array();
	}

	foreach ( $prices as $price ) {
		if ( null !== $price->recurring ) {
			return $price;
			break;
		}
	}

	return array();
}

/**
 * Returns the default price option for a Payment FOrm.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption[] $prices Prices list.
 * @return false|\SimplePay\Core\PaymentForm\PriceOption Default price, or first price.
 *                                                       False if no prices are found.
 */
function simpay_get_payment_form_default_price( $prices ) {
	$price = current(
		array_filter(
			$prices,
			function ( $price ) {
				return null !== $price->default && true === $price->default;
			}
		)
	);

	// Fallback to the first price.
	if ( false === $price ) {
		$price = current( $prices );
	}

	return $price;
}

/**
 * Determines if a list of prices includes the ability to set a custom amount.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption[] $prices Price options.
 * @return bool
 */
function simpay_payment_form_prices_has_custom_price( $prices ) {
	$prices = array_filter(
		$prices,
		function ( $price ) {
			return false === $price->is_defined_amount();
		}
	);

	return ! empty( $prices );
}

/**
 * Retrieves the first custom price option.
 *
 * This is only used to fill legacy Payment Form properties and should not be
 * extended.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption[] $prices Price options.
 * @return \SimplePay\Core\PaymentForm\PriceOption[]|false Price option or false if no custom
 *                                                         amounts are allowed.
 */
function __unstable_simpay_get_payment_form_custom_price( $prices ) {
	$has_custom_price = simpay_payment_form_prices_has_custom_price( $prices );

	if ( false === $has_custom_price ) {
		return false;
	}

	$prices = array_filter(
		$prices,
		function ( $price ) {
			return false === $price->is_defined_amount();
		}
	);

	if ( empty( $prices ) ) {
		return false;
	}

	return current( $prices );
}

/**
 * Determines if a list of prices includes the ability to optionally recur.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption[] $prices Price options.
 * @return bool
 */
function simpay_payment_form_prices_has_recurring_price( $prices ) {
	$prices = array_filter(
		$prices,
		function ( $price ) {
			return true === $price->can_recur;
		}
	);

	return ! empty( $prices );
}

/**
 * Ensures Payment Forms have required fields and remove unnecessary fields.
 *
 * @since 4.1.0
 *
 * @param array  $fields Payment Form custom fields.
 * @param int    $form_id Payment Form ID.
 * @param string $form_display_type Payment Form display type.
 * @return array{fields: array<string, mixed>, changes: array<string>}
 */
function simpay_payment_form_add_missing_custom_fields(
	$fields,
	$form_id,
	$form_display_type = 'embedded'
) {
	$form = simpay_get_form( $form_id );

	if ( false === $form ) {
		return $fields;
	}

	$prices = simpay_get_payment_form_prices( $form );

	$count = count( $fields );

	$payment_methods  = get_post_meta( $form_id, '_payment_methods', true );
	$tax_status       = get_post_meta( $form_id, '_tax_status', true );
	$allow_line_items = get_post_meta( $form_id, '_allow_purchasing_multiple_line_items', true );

	$changes = array();

	// Form display type-specific.

	switch ( $form_display_type ) {
		case 'overlay':
			if ( ! isset( $fields['payment_button'] ) ) {
				$fields['payment_button'][] = array(
					'uid' => $count,
					'id'  => 'simpay_' . $form_id . '_payment_button',
				);

				$changes[] = __(
					'Payment Button field is required, and has been added to the payment form.',
					'stripe'
				);
				++$count;
			}
			break;
		case 'stripe_checkout':
			if ( isset( $fields['coupon'] ) ) {
				unset( $fields['coupon'] );
				$changes[] = __( 'The coupon field has been removed since you selected Off-site Stripe Checkout. ', 'stripe' );
			}

			break;
		case 'embedded':
			// Ensure "Customer Name" exists if using Bancontact, or p24, or SEPA.
			if (
				! simpay_is_upe() &&
				! isset( $fields['customer_name'] ) &&
				(
					(
						isset( $payment_methods['stripe-elements']['bancontact'] ) &&
						isset( $payment_methods['stripe-elements']['bancontact']['id'] )
					) ||
					(
						isset( $payment_methods['stripe-elements']['p24'] ) &&
						isset( $payment_methods['stripe-elements']['p24']['id'] )
					) ||
					(
						isset( $payment_methods['stripe-elements']['sepa-debit'] ) &&
						isset( $payment_methods['stripe-elements']['sepa-debit']['id'] )
					) ||
					(
						isset( $payment_methods['stripe-elements']['klarna'] ) &&
						isset( $payment_methods['stripe-elements']['klarna']['id'] )
					) ||
					(
						isset( $payment_methods['stripe-elements']['afterpay-clearpay'] ) &&
						isset( $payment_methods['stripe-elements']['afterpay-clearpay']['id'] )
					) ||
					(
						isset( $payment_methods['stripe-elements']['ach-debit'] ) &&
						isset( $payment_methods['stripe-elements']['ach-debit']['id'] )
					)
				)
			) {
				$fields['customer_name'][] = array(
					'uid'      => $count,
					'id'       => 'simpay_' . $form_id . '_customer_name',
					'label'    => 'Full Name',
					'required' => 'yes',
				);

				$changes[] = __(
					'Customer Name field is required for one or more payment methods, and has been added to the payment form.',
					'stripe'
				);

				++$count;
			}

			// Ensure "Customer Email" exists.
			if ( ! isset( $fields['email'] ) ) {
				$fields['email'][] = array(
					'uid'   => $count,
					'id'    => 'simpay_' . $form_id . '_email',
					'label' => 'Email Address',
				);

				$changes[] = __(
					'Customer Email field is required, and has been added to the payment form.',
					'stripe'
				);

				++$count;
			}

			$needs_required_address = (
				(
					! simpay_is_upe() &&
					isset( $payment_methods['stripe-elements']['klarna'] ) &&
					isset( $payment_methods['stripe-elements']['klarna']['id'] )
				) ||
				(
					isset( $payment_methods['stripe-elements']['afterpay-clearpay'] ) &&
					isset( $payment_methods['stripe-elements']['afterpay-clearpay']['id'] )
				) ||
				'automatic' === $tax_status
			);

			// Ensure "Address" exists and is required if using Klarna, or automatic taxes.
			if (
				! isset( $fields['address'] ) &&
				true === $needs_required_address
			) {
				if ( ! simpay_is_upe() ) {
					$args = array(
						'uid'                     => $count,
						'id'                      => 'simpay_' . $form_id . '_address',
						'billing-container-label' => 'Billing Address',
						'label-street'            => 'Street Address',
						'label-city'              => 'City',
						'label-state'             => 'State',
						'label-zip'               => 'Postal Code',
						'label-country'           => 'Country',
						'required'                => 'yes',
					);
				} else {
					$args = array(
						'uid'              => $count,
						'id'               => 'simpay_' . $form_id . '_address',
						'collect-shipping' => 'no',
						'required'         => 'yes',
					);
				}

				if (
					isset( $payment_methods['stripe-elements']['afterpay-clearpay'] ) &&
					isset( $payment_methods['stripe-elements']['afterpay-clearpay']['id'] )
				) {
					$args['collect-shipping'] = 'yes';
				}

				$fields['address'][] = $args;

				$changes[] = __(
					'Address field is required, and has been added to the payment form.',
					'stripe'
				);
				++$count;

				// If the address field exists, ensure it is required.
			} elseif (
				isset( $fields['address'] ) &&
				true === $needs_required_address
			) {
				$current_address_field = current( $fields['address'] );

				$args['required'] = 'yes';

				if (
					isset( $payment_methods['stripe-elements']['afterpay-clearpay'] ) &&
					isset( $payment_methods['stripe-elements']['afterpay-clearpay']['id'] )
				) {
					$args['collect-shipping'] = 'yes';
				}

				$fields['address'] = array(
					array_merge(
						$current_address_field,
						$args
					),
				);
			}

			// Add "Amount Breakdown" if using automatic taxes.
			if (
				! isset( $fields['total_amount'] ) &&
				'automatic' === $tax_status
			) {
				$fields['total_amount'][] = array();

				++$count;
			}

			// Set "Phone" to optional if using Payment Request Button.
			if (
				isset( $fields['payment_request_button'] ) &&
				isset( $fields['telephone'] )
			) {
				$fields['telephone'] = array(
					array_merge(
						current( $fields['telephone'] ),
						array(
							'required' => 'no',
						)
					),
				);
			}

			// Ensure "Payment Methods" exist.
			if ( ! isset( $fields['card'] ) ) {
				$fields['card'][] = array(
					'order' => 9998,
					'uid'   => $count,
					'id'    => 'simpay_' . $form_id . '_card',
					'label' => ! simpay_is_upe() ? 'Payment Method' : '',
				);

				$changes[] = __(
					'Payment Methods is required, and has been added to the payment form.',
					'stripe'
				);

				++$count;
			}

			// Ensure "Checkout" button exists.
			if ( ! isset( $fields['checkout_button'] ) ) {
				$fields['checkout_button'][] = array(
					'order' => 9999,
					'uid'   => $count,
					'id'    => 'simpay_' . $form_id . '_checkout_button',
				);

				$changes[] = __(
					'Checkout Button field is required, and has been added to the payment form.',
					'stripe'
				);

				++$count;
			}

			// Ensure "Payment Button" exists.
			if ( 'overlay' === $form_display_type ) {
				if ( ! isset( $fields['payment_button'] ) ) {
					$fields['payment_button'][] = array(
						'uid' => $count,
						'id'  => 'simpay_' . $form_id . '_payment_button',
					);

					$changes[] = __(
						'Payment Button field is required, and has been added to the payment form.',
						'stripe'
					);
					++$count;
				}
				// Remove "Payment Button".
			} elseif ( isset( $fields['payment_button'] ) ) {
					unset( $fields['payment_button'] );
					$changes[] = __(
						'Payment Button has been removed from the payment form.',
						'stripe'
					);
			}

			break;
		default:
			// Remove "Address" if using automatic taxes.
			if ( 'automatic' === $tax_status ) {
				unset( $fields['customer_name'] );
				unset( $fields['email'] );
				unset( $fields['telephone'] );
				unset( $fields['address'] );
				unset( $fields['tax_id'] );
			}

			// Ensure "Payment Button" exists.
			if ( ! isset( $fields['payment_button'] ) ) {
				$fields['payment_button'][] = array(
					'order' => 9999,
					'uid'   => $count,
					'id'    => 'simpay_' . $form_id . '_payment_button',
				);

				$changes[] = __(
					'Payment Button field is required, and has been added to the payment form.',
					'stripe'
				);

				++$count;
			}

			// Remove unnecessary fields.
			unset( $fields['card'] );
			unset( $fields['checkout_button'] );
			unset( $fields['payment_request_button'] );
			unset( $fields['fee_recovery_toggle'] );
	}

	// Ensure "Price Selector" is always present.
	if ( ! isset( $fields['plan_select'] ) ) {
		$fields['plan_select'][] = array(
			'uid'   => $count,
			'id'    => 'simpay_' . $form_id . '_plan_select_' . $count,
			'label' => 'Choose an amount',
		);

		$changes[] = __( 'Price Selector is required, and has been added to the payment form. ', 'stripe' );
		++$count;
	}

	if ( 'yes' === $allow_line_items ) {
		if ( isset( $fields['custom_amount'] ) ) {
			unset( $fields['custom_amount'] );
			$changes[] = __( 'Custom Amount Input has been removed from the payment form. This field is automatically displayed with multiple line items.', 'stripe' );
		}

		if ( isset( $fields['recurring_amount_toggle'] ) ) {
			unset( $fields['recurring_amount_toggle'] );
			$changes[] = __( 'Recurring Amount Toggle has been removed from the payment form. This field is automatically displayed with multiple line items.', 'stripe' );
		}

		if ( isset( $fields['number'] ) ) {
			foreach ( $fields['number'] as $number_field_index => $number_field ) {
				if ( isset( $number_field['quantity'] ) && 'yes' === $number_field['quantity'] ) {
					unset( $fields['number'][ $number_field_index ] );
					$changes[] = __( 'Quantity Input has been removed from the payment form. This field is automatically displayed with multiple line items.', 'stripe' );
				}
			}
		}

		if ( isset( $fields['radio'] ) || isset( $fields['dropdown'] ) ) {
			foreach ( array( 'radio', 'dropdown' ) as $field_type ) {
				if ( isset( $fields[ $field_type ] ) ) {
					foreach ( $fields[ $field_type ] as $field_index => $field ) {
						if ( isset( $field['amount_quantity'] ) && 'yes' === $field['amount_quantity'] ) {
							unset( $fields[ $field_type ][ $field_index ] );
							$changes[] = __( 'Quantity Input has been removed from the payment form. This field is automatically displayed with multiple line items.', 'stripe' );
						}
					}
				}
			}
		}
	} else {
		// Ensure "Custom Amount" field exists if using a custom amount, and no Subscription.
		$has_custom_amount = simpay_payment_form_prices_has_custom_price( $prices );

		if ( true === $has_custom_amount && ! isset( $fields['custom_amount'] ) ) {
			$fields['custom_amount'][] = array(
				'uid'   => $count,
				'id'    => 'simpay_' . $form_id . '_custom_amount_' . $count,
				'label' => 'Custom Amount',
			);

			$changes[] = __(
				'The Custom Amount Input field is required because you have set a custom amount on your price list, and has been added to the payment form.',
				'stripe'
			);

			++$count;
		} elseif ( false === $has_custom_amount ) {
			if ( isset( $fields['custom_amount'] ) ) {
				unset( $fields['custom_amount'] );
				$changes[] = __( 'Custom Amount Input has been removed from the payment form. A price option that allows user-defined amounts is required. ', 'stripe' );
			}
		}
	}

	// Ensure "Recurring Amount Toggle" field exists if using an optional recurring price.
	$can_recur = simpay_payment_form_prices_has_recurring_price( $prices );

	if (
		'yes' !== $allow_line_items &&
		true === $can_recur && ! isset( $fields['recurring_amount_toggle'] )
	) {
		$fields['recurring_amount_toggle'][] = array(
			'uid'   => $count,
			'id'    => 'simpay_' . $form_id . '_recurring_amount_toggle_' . $count,
			'label' => 'Make this a recurring amount',
		);

		$changes[] = __(
			'Recurring Amount Toggle is required, and has been added to the payment form.',
			'stripe'
		);

		++$count;
	} elseif (
		'yes' !== $allow_line_items &&
		false === $can_recur
	) {
		if ( isset( $fields['recurring_amount_toggle'] ) ) {
			unset( $fields['recurring_amount_toggle'] );
			$changes[] = __(
				'Recurring Amount Toggle has been removed from the payment form. An optionally recurring price option is required.',
				'stripe'
			);
		}
	}

	// Remove "Coupon" field if using Fee Recovery.
	if ( isset( $fields['coupon'] ) ) {
		// Fee recovery toggle exists, remove Coupon.
		if ( isset( $fields['fee_recovery_toggle'] ) ) {
			unset( $fields['coupon'] );
			$changes[] = __(
				'Coupon field has been removed from the payment form.',
				'stripe'
			);
		}

		// Fee recovery exists in Payment Method configuration.
		if ( isset( $payment_methods['stripe-elements'] ) ) {
			foreach ( $payment_methods['stripe-elements'] as $payment_method ) {
				if (
					isset(
						$payment_method['fee_recovery'],
						$payment_method['fee_recovery']['enabled']
					) &&
					'yes' === $payment_method['fee_recovery']['enabled']
				) {
					unset( $fields['coupon'] );
					$changes[] = __(
						'Coupon field has been removed from the payment form.',
						'stripe'
					);
					break;
				}
			}
		}
	}

	// Remove "1-Click Payment Methods" (Payment Request Button) if using UPE.
	if ( simpay_is_upe() ) {
		unset( $fields['payment_request_button'] );
	}

	// General sorting template for auto-added fields.
	$fields = array_merge(
		array_flip(
			array(
				'customer_name',
				'email',
				'address',
				'plan_select',
				'custom_amount',
				'recurring_amount_toggle',
				'total_amount',
				'card',
				'checkout_button',
			)
		),
		$fields
	);

	// "Checkout Button" and "Payment Button" should always be last.
	if ( isset( $fields['payment_button'] ) ) {
		$payment_button = $fields['payment_button'];
		unset( $fields['payment_button'] );

		$fields['payment_button'] = $payment_button;
	}

	if ( isset( $fields['checkout_button'] ) ) {
		$checkout_button = $fields['checkout_button'];
		unset( $fields['checkout_button'] );

		$fields['checkout_button'] = $checkout_button;
	}

	// Remove empty/invalid fields after sorting.

	$fields = array_filter(
		$fields,
		function ( $field ) {
			return true === is_array( $field );
		}
	);

	return array(
		'fields'  => $fields,
		'changes' => $changes,
	);
}
