<?php
/**
 * Payment Methods: Functions
 *
 * @package SimplePay\Core\PaymentMethods
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 * @version 4.16.0 Moved from legacy code.
 */

namespace SimplePay\Core\PaymentMethods;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a list of registered Payment Methods.
 *
 * @since 3.8.0
 *
 * @return array<string, \SimplePay\Core\PaymentMethods\PaymentMethod> List of Payment Methods.
 */
function get_payment_methods(): array {
	$payment_methods = Utils\get_collection( 'payment-methods' );

	if ( false === $payment_methods ) {
		return array();
	}

	return $payment_methods->get_items();
}

/**
 * Returns a Payment Method.
 *
 * @since 3.8.0
 *
 * @param string $payment_method_id ID of the registered Payment Method.
 * @return \SimplePay\Core\PaymentMethods\PaymentMethod|false Payment Method if found, otherwise `false`.
 */
function get_payment_method( $payment_method_id ) {
	$payment_methods = Utils\get_collection( 'payment-methods' );

	if ( false === $payment_methods ) {
		return false;
	}

	$item = $payment_methods->get_item( $payment_method_id );
	if ( false === $item || ! $item instanceof \SimplePay\Core\PaymentMethods\PaymentMethod ) {
		return false;
	}
	return $item;
}


/**
 * Retrieves saved Payment Methods for a specific form.
 *
 * @since 3.8.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return \SimplePay\Core\PaymentMethods\PaymentMethod[] List of Payment Methods.
 */
function get_form_payment_methods( $form ) {
	$payment_form = 'stripe_checkout' === $form->get_display_type()
			? 'stripe-checkout'
			: 'stripe-elements';

	$payment_methods = simpay_get_filtered(
		'payment_methods',
		simpay_get_saved_meta( $form->id, '_payment_methods', array() ),
		$form->id
	);

	// Form hasn't been updated since 3.8.0.
	if ( empty( $payment_methods ) ) {
		$payment_methods = array(
			'card' => array(
				'id' => 'card',
			),
		);
		// Use saved Payment Methods for Payment Form.
	} elseif ( is_array( $payment_methods ) && isset( $payment_methods[ $payment_form ] ) && is_array( $payment_methods[ $payment_form ] ) ) {
			$payment_methods = $payment_methods[ $payment_form ];
	} else {
		$payment_methods = array();
	}

	$payment_methods = array_map(
		/**
		 * Attach saved Payment Method settings to the \SimplePay\Core\PaymentMethods\PaymentMethod.
		 *
		 * @since 3.8.0
		 *
		 * @param array $payment_method Saved Payment Method data.
		 * @return false|\SimplePay\Core\PaymentMethods\PaymentMethod Payment Method object if available
		 *                                                             otherwise false.
		 */
		function ( $payment_method ) {
			if ( ! is_array( $payment_method ) || ! isset( $payment_method['id'] ) ) {
				return false;
			}

			$payment_method_obj = get_payment_method( (string) $payment_method['id'] );

			if ( false === $payment_method_obj ) {
				return false;
			}

			$payment_method_obj->config = is_array( $payment_method ) ? $payment_method : array();

			return $payment_method_obj;
		},
		is_array( $payment_methods ) ? $payment_methods : array()
	);

	// Ensure at least one Payment Method is enabled.
	// Giropay might have been disabled due to deprecation.
	$payment_methods = array_filter( $payment_methods );

	if ( empty( $payment_methods ) ) {
		$payment_methods[] = get_payment_method( 'card' );
	}

	return array_filter(
		$payment_methods,
		function ( $payment_method ) {
			return is_object( $payment_method ) && is_a( $payment_method, 'SimplePay\\Core\\PaymentMethods\\PaymentMethod' );
		}
	);
}

/**
 * Returns a list of payment method IDs (slugs) that are enabled for a specific form.
 *
 * @since 4.6.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return string[] List of payment method IDs.
 */
function get_form_payment_method_ids( $form ) {
	$allowed_payment_methods = get_form_payment_methods( $form );

	return array_reduce(
		$allowed_payment_methods,
		function ( array $carry, $payment_method ) {
			switch ( $payment_method->id ) {
				case 'sepa-debit':
					$id = 'sepa_debit';
					break;
				case 'ach-debit':
					$id = 'us_bank_account';
					break;
				case 'afterpay-clearpay':
					$id = 'afterpay_clearpay';
					break;
				default:
					$id = $payment_method->id;
			}

			array_push( $carry, $id );

			return $carry;
		},
		array()
	);
}

/**
 * Determines if Link by Stripe is enabled for an on-site payment form.
 *
 * Link is enabled per form via the Email field's "Enable Link" setting. It
 * only applies to on-site (Payment Element) forms; Stripe Checkout manages
 * Link itself.
 *
 * @since 4.17.6
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return bool
 */
function is_form_link_enabled( $form ) {
	if ( 'stripe_checkout' === $form->get_display_type() ) {
		return false;
	}

	/** @var array<string, array<int|string, array<string, mixed>>> $custom_fields */
	$custom_fields = simpay_get_saved_meta( $form->id, '_custom_fields', array() );

	if ( ! isset( $custom_fields['email'] ) || ! is_array( $custom_fields['email'] ) ) {
		return false;
	}

	foreach ( $custom_fields['email'] as $field ) {
		if ( is_array( $field ) && is_email_field_link_enabled( $field ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Determines if an Email field's settings have Link by Stripe enabled.
 *
 * @since 4.17.6
 *
 * @param array<string, mixed> $settings Email field settings.
 * @return bool
 */
function is_email_field_link_enabled( $settings ) {
	return isset( $settings['link'] ) &&
		is_array( $settings['link'] ) &&
		isset( $settings['link']['enabled'] ) &&
		'yes' === $settings['link']['enabled'];
}

/**
 * Retrieves saved Payment Method settings for a specific form.
 *
 * @since 3.9.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @param string                         $payment_method Payment Method ID.
 * @return array<string, mixed> List of Payment Method settings.
 */
function get_form_payment_method_settings( $form, $payment_method ) {
	$payment_form = 'stripe_checkout' === $form->get_display_type()
			? 'stripe-checkout'
			: 'stripe-elements';

	// Reset payment method IDs to WP Simple Pay IDs, not Stripe IDs.
	// i.e us_bank_account back to ach-debit.
	switch ( $payment_method ) {
		case 'sepa_debit':
			$payment_method = 'sepa-debit';
			break;
		case 'us_bank_account':
			$payment_method = 'ach-debit';
			break;
		case 'afterpay_clearpay':
			$payment_method = 'afterpay-clearpay';
			break;
		default:
			$payment_method = $payment_method;
	}

	$payment_methods = simpay_get_filtered(
		'payment_methods',
		simpay_get_saved_meta( $form->id, '_payment_methods', array() ),
		$form->id
	);

	// Form hasn't been updated since 3.8.0.
	if ( empty( $payment_methods ) ) {
		$payment_methods = array(
			'card' => array(
				'id' => 'card',
			),
		);
		// Use saved Payment Methods for Payment Form.
	} elseif ( is_array( $payment_methods ) && isset( $payment_methods[ $payment_form ] ) && is_array( $payment_methods[ $payment_form ] ) ) {
			$payment_methods = $payment_methods[ $payment_form ];
	} else {
		$payment_methods = array();
	}

	if ( ! is_array( $payment_methods ) ) {
		return array();
	}

	if ( ! isset( $payment_methods[ $payment_method ] ) || ! is_array( $payment_methods[ $payment_method ] ) ) {
		return array();
	}

	return $payment_methods[ $payment_method ];
}

/**
 * Determines the amount needed to recover fees for a specific form's payment
 * method configuration.
 *
 * @since 4.6.6
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @param string                         $payment_method_id Payment Method ID.
 * @param int                            $amount Amount to recover fees for.
 * @param bool                           $include_fixed_amount Whether to include the fixed amount in the total.
 *                                                             This is helpful for subscriptions with multiple
 *                                                             line items.
 * @return int
 */
function get_form_payment_method_fee_recovery_amount(
	$form,
	$payment_method_id,
	$amount,
	$include_fixed_amount = true
): int {
	$tax_status = get_post_meta( $form->id, '_tax_status', true );

	if ( 'none' !== $tax_status ) {
		return 0;
	}

	$payment_method_settings = get_form_payment_method_settings(
		$form,
		$payment_method_id
	);

	if (
		! is_array( $payment_method_settings ) ||
		! isset( $payment_method_settings['fee_recovery'] ) ||
		! is_array( $payment_method_settings['fee_recovery'] ) ||
		! isset( $payment_method_settings['fee_recovery']['enabled'] ) ||
		'yes' !== $payment_method_settings['fee_recovery']['enabled']
	) {
		return 0;
	}

	$fixed   = $include_fixed_amount
		? ( isset( $payment_method_settings['fee_recovery']['amount'] ) ? $payment_method_settings['fee_recovery']['amount'] : 0 )
		: 0;
	$percent = isset( $payment_method_settings['fee_recovery']['percent'] ) ? $payment_method_settings['fee_recovery']['percent'] : 0;

	return (int) round( ( $amount + $fixed ) / ( 1 - ( $percent / 100 ) ) - $amount );
}

/**
 * Returns the per-currency transaction amount limits for payment method types
 * that Stripe only accepts within a specific amount range.
 *
 * WP Simple Pay sends an explicit `payment_method_types` list to Stripe, so a
 * payment method whose amount range does not include the payment amount must
 * be removed before the intent is created. Otherwise Stripe rejects the
 * entire intent, which blocks every payment method on the form.
 *
 * Keys are Stripe payment method types (e.g. `afterpay_clearpay`). Each entry
 * contains:
 *
 * - `limits`: Limits keyed by lowercase currency code. The `*` key applies to
 *   any currency without its own entry. Each limit may define an inclusive
 *   `min` and/or `max`, in the currency's smallest unit (cents for two-decimal
 *   currencies, whole units for zero-decimal currencies).
 * - `basis`: Optional. The server-side amount that is compared: `amount` (the
 *   total charged, default) or `unit_amount` (the price option's unit amount).
 *   The frontend always compares the total due today.
 *
 * @link https://docs.stripe.com/payments/afterpay-clearpay#payment-options
 * @link https://docs.stripe.com/payments/zip
 * @link https://docs.stripe.com/payments/alma
 * @since 4.17.5
 *
 * @return array<string, array<string, mixed>>
 */
function get_payment_method_amount_limits(): array {
	$limits = array(
		// Affirm has historically required a unit amount of at least 100.00.
		'affirm'            => array(
			'basis'  => 'unit_amount',
			'limits' => array(
				'*' => array(
					'min' => 10000,
				),
			),
		),
		// Afterpay/Clearpay transaction ranges, as published by Stripe.
		'afterpay_clearpay' => array(
			'basis'  => 'amount',
			'limits' => array(
				'aud' => array(
					'min' => 100,
					'max' => 400000,
				),
				'cad' => array(
					'min' => 100,
					'max' => 200000,
				),
				'gbp' => array(
					'min' => 100,
					'max' => 120000,
				),
				'nzd' => array(
					'min' => 100,
					'max' => 400000,
				),
				'usd' => array(
					'min' => 100,
					'max' => 400000,
				),
			),
		),
		// Zip transaction ranges, as published by Stripe.
		'zip'               => array(
			'basis'  => 'amount',
			'limits' => array(
				'aud' => array(
					'min' => 100,
					'max' => 5000000,
				),
				'usd' => array(
					'min' => 3500,
					'max' => 150000,
				),
			),
		),
		// Alma transaction range, as published by Stripe.
		'alma'              => array(
			'basis'  => 'amount',
			'limits' => array(
				'eur' => array(
					'min' => 5000,
					'max' => 500000,
				),
			),
		),
	);

	/**
	 * Filters the per-currency transaction amount limits of payment method types.
	 *
	 * Amounts are in the currency's smallest unit.
	 *
	 * @since 4.17.5
	 *
	 * @param array<string, array<string, mixed>> $limits Amount limits, keyed by Stripe payment method type.
	 */
	$limits = apply_filters( 'simpay_payment_method_amount_limits', $limits );

	return normalize_payment_method_amount_limits( $limits );
}

/**
 * Validates and normalizes a payment method amount limits table.
 *
 * This is the single source of truth for the shape of the limits used by the
 * payment form builder notices, server-side enforcement, and the Payment
 * Element (frontend) enforcement, so a (filtered) limit is displayed if, and
 * only if, it is enforced.
 *
 * - Payment method type keys must be non-empty strings.
 * - Currency keys are trimmed and lowercased (e.g. `USD` becomes `usd`). The
 *   `*` (any currency) key is kept. If two keys normalize to the same
 *   currency, the last one wins.
 * - `min` and `max` bounds must be numeric, and are cast to integers. Other
 *   bounds are dropped, as are currencies without a valid bound and payment
 *   method types without a valid currency limit.
 * - `basis` is `unit_amount` or `amount` (default).
 *
 * @since 4.17.6
 *
 * @param mixed $limits Amount limits, keyed by Stripe payment method type.
 * @return array<string, array{basis: string, limits: array<string, array<string, int>>}>
 */
function normalize_payment_method_amount_limits( $limits ): array {
	if ( ! is_array( $limits ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $limits as $payment_method_type => $payment_method_limits ) {
		if (
			! is_string( $payment_method_type ) ||
			'' === $payment_method_type ||
			! is_array( $payment_method_limits ) ||
			! isset( $payment_method_limits['limits'] )
		) {
			continue;
		}

		$currency_limits = normalize_payment_method_currency_amount_limits(
			$payment_method_limits['limits']
		);

		if ( empty( $currency_limits ) ) {
			continue;
		}

		$normalized[ $payment_method_type ] = array(
			'basis'  => isset( $payment_method_limits['basis'] ) &&
				'unit_amount' === $payment_method_limits['basis']
				? 'unit_amount'
				: 'amount',
			'limits' => $currency_limits,
		);
	}

	return $normalized;
}

/**
 * Validates and normalizes the per-currency limits of a single payment method
 * type. See `normalize_payment_method_amount_limits()`.
 *
 * @since 4.17.6
 *
 * @param mixed $currency_limits Limits keyed by currency code.
 * @return array<string, array<string, int>> Limits keyed by lowercase currency
 *                                           code (or `*`), each with optional
 *                                           `min` and `max` keys.
 */
function normalize_payment_method_currency_amount_limits( $currency_limits ): array {
	if ( ! is_array( $currency_limits ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $currency_limits as $currency => $limit ) {
		if ( ! is_string( $currency ) || ! is_array( $limit ) ) {
			continue;
		}

		$currency = strtolower( trim( $currency ) );

		if ( '' === $currency ) {
			continue;
		}

		$bounds = array();

		foreach ( array( 'min', 'max' ) as $bound ) {
			if ( isset( $limit[ $bound ] ) && is_numeric( $limit[ $bound ] ) ) {
				$bounds[ $bound ] = (int) $limit[ $bound ];
			}
		}

		if ( empty( $bounds ) ) {
			continue;
		}

		$normalized[ $currency ] = $bounds;
	}

	return $normalized;
}

/**
 * Returns the normalized per-currency limits of a payment method type.
 *
 * @since 4.17.6
 *
 * @param string                                   $payment_method_type Stripe payment method type, e.g. `afterpay_clearpay`.
 * @param array<string, array<string, mixed>>|null $limits Optional. The limits table returned by
 *                                                         `get_payment_method_amount_limits()`, to
 *                                                         avoid running its filter again. Read if
 *                                                         not an array. Default null.
 * @return array<string, array<string, int>> Limits keyed by lowercase currency code (or `*`).
 */
function get_payment_method_currency_amount_limits( $payment_method_type, $limits = null ): array {
	if ( ! is_array( $limits ) ) {
		$limits = get_payment_method_amount_limits();
	}

	if (
		! isset( $limits[ $payment_method_type ] ) ||
		! is_array( $limits[ $payment_method_type ] ) ||
		! isset( $limits[ $payment_method_type ]['limits'] )
	) {
		return array();
	}

	// Normalizing an already normalized table is a no-op, but a table passed
	// by a caller may not have come from `get_payment_method_amount_limits()`.
	return normalize_payment_method_currency_amount_limits(
		$limits[ $payment_method_type ]['limits']
	);
}

/**
 * Returns a payment method type's amount limit for a currency, falling back
 * to the `*` (any currency) limit.
 *
 * @since 4.17.5
 *
 * @param string                                   $payment_method_type Stripe payment method type, e.g. `afterpay_clearpay`.
 * @param string                                   $currency Currency code.
 * @param array<string, array<string, mixed>>|null $limits Optional. The limits table returned by
 *                                                         `get_payment_method_amount_limits()`, to
 *                                                         avoid running its filter again. Read if
 *                                                         not an array. Default null.
 * @return array<string, int> Limit with optional `min` and `max` keys. Empty if there is no limit.
 */
function get_payment_method_currency_amount_limit( $payment_method_type, $currency, $limits = null ): array {
	$currency_limits = get_payment_method_currency_amount_limits(
		$payment_method_type,
		$limits
	);
	$currency        = strtolower( trim( (string) $currency ) );

	if ( '' !== $currency && isset( $currency_limits[ $currency ] ) ) {
		return $currency_limits[ $currency ];
	}

	return isset( $currency_limits['*'] ) ? $currency_limits['*'] : array();
}

/**
 * Returns the server-side amount basis used to compare a payment method type
 * against its amount limits.
 *
 * @since 4.17.5
 *
 * @param string                                   $payment_method_type Stripe payment method type, e.g. `afterpay_clearpay`.
 * @param array<string, array<string, mixed>>|null $limits Optional. The limits table returned by
 *                                                         `get_payment_method_amount_limits()`, to
 *                                                         avoid running its filter again. Read if
 *                                                         not an array. Default null.
 * @return string `amount` (the total charged) or `unit_amount`.
 */
function get_payment_method_amount_limit_basis( $payment_method_type, $limits = null ): string {
	if ( ! is_array( $limits ) ) {
		$limits = get_payment_method_amount_limits();
	}

	if (
		isset( $limits[ $payment_method_type ] ) &&
		is_array( $limits[ $payment_method_type ] ) &&
		isset( $limits[ $payment_method_type ]['basis'] ) &&
		'unit_amount' === $limits[ $payment_method_type ]['basis']
	) {
		return 'unit_amount';
	}

	return 'amount';
}

/**
 * Determines if an amount is within a payment method type's amount limits
 * for a currency.
 *
 * Payment method types without limits (or without limits for the currency)
 * are always considered within limits. Bounds are inclusive.
 *
 * @since 4.17.5
 *
 * @param string                                   $payment_method_type Stripe payment method type, e.g. `afterpay_clearpay`.
 * @param string                                   $currency Currency code.
 * @param int                                      $amount Amount in the currency's smallest unit.
 * @param array<string, array<string, mixed>>|null $limits Optional. The limits table returned by
 *                                                         `get_payment_method_amount_limits()`, to
 *                                                         avoid running its filter again. Read if
 *                                                         not an array. Default null.
 * @return bool
 */
function is_amount_within_payment_method_limits( $payment_method_type, $currency, $amount, $limits = null ): bool {
	$limit = get_payment_method_currency_amount_limit(
		$payment_method_type,
		$currency,
		$limits
	);

	if ( empty( $limit ) ) {
		return true;
	}

	$amount = (int) $amount;

	if ( isset( $limit['min'] ) && $amount < $limit['min'] ) {
		return false;
	}

	if ( isset( $limit['max'] ) && $amount > $limit['max'] ) {
		return false;
	}

	return true;
}

/**
 * Returns the per-currency amount ranges of a payment method type that
 * define both a minimum and a maximum, for display.
 *
 * The `*` (any currency) limit and limits that only define one bound are
 * skipped. Reads the same normalized limits that are enforced (see
 * `normalize_payment_method_amount_limits()`), so malformed (e.g. filtered)
 * entries are ignored and currency codes are lowercased.
 *
 * @since 4.17.6
 *
 * @param string                                   $payment_method_type Stripe payment method type, e.g. `afterpay_clearpay`.
 * @param array<string, array<string, mixed>>|null $limits Optional. The limits table returned by
 *                                                         `get_payment_method_amount_limits()`, to
 *                                                         avoid running its filter again. Read if
 *                                                         not an array. Default null.
 * @return array<string, array{min: int, max: int}> Ranges keyed by lowercase currency code.
 */
function get_payment_method_amount_ranges( $payment_method_type, $limits = null ): array {
	$ranges = array();

	$currency_limits = get_payment_method_currency_amount_limits(
		$payment_method_type,
		$limits
	);

	foreach ( $currency_limits as $currency => $limit ) {
		if ( '*' === $currency || ! isset( $limit['min'], $limit['max'] ) ) {
			continue;
		}

		$ranges[ $currency ] = array(
			'min' => $limit['min'],
			'max' => $limit['max'],
		);
	}

	return $ranges;
}
