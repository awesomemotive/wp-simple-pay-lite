<?php
/**
 * Recurring Description Formats: Invoice limit display format helpers
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the available recurring invoice limit description formats.
 *
 * Each format has a singular variant (interval_count === 1, uses adjective)
 * and a plural variant (interval_count > 1, uses "every N intervals").
 *
 * All sprintf strings use 6 positional args:
 *   %1$d = payment count
 *   %2$s = formatted amount
 *   %3$s = interval adjective ("monthly", "weekly", etc.)
 *   %4$s = interval noun singular ("month", "week", etc.)
 *   %5$s = interval noun plural ("months", "weeks", etc.)
 *   %6$d = interval count (1, 2, 3, etc.)
 *
 * @since 4.17.1
 *
 * @return array<string, array{singular: string, plural: string, label: string}>
 */
function simpay_get_recurring_invoice_limit_formats() {
	return array(
		'count_adj_amount' => array(
			/* translators: %1$d Number of payments. %2$s Recurring amount. %3$s Interval adjective (e.g. monthly). */
			'singular' => _x(
				'%1$d %3$s payments of %2$s',
				'recurring invoice limit format',
				'stripe'
			),
			/* translators: %1$d Number of payments. %2$s Recurring amount. %5$s Interval noun plural (e.g. months). %6$d Interval count. */
			'plural'   => _x(
				'%1$d payments of %2$s every %6$d %5$s',
				'recurring invoice limit format',
				'stripe'
			),
			/* translators: Example label for recurring invoice limit format setting. */
			'label'    => __( '12 monthly payments of $24', 'stripe' ),
		),
		'amount_per'       => array(
			/* translators: %1$d Number of payments. %2$s Recurring amount. %4$s Interval noun singular (e.g. month). */
			'singular' => _x(
				'%2$s per %4$s for %1$d payments',
				'recurring invoice limit format',
				'stripe'
			),
			/* translators: %1$d Number of payments. %2$s Recurring amount. %5$s Interval noun plural (e.g. months). %6$d Interval count. */
			'plural'   => _x(
				'%2$s per %6$d %5$s for %1$d payments',
				'recurring invoice limit format',
				'stripe'
			),
			/* translators: Example label for recurring invoice limit format setting. */
			'label'    => __( '$24 per month for 12 payments', 'stripe' ),
		),
		'amount_every'     => array(
			/* translators: %1$d Number of payments. %2$s Recurring amount. %4$s Interval noun singular (e.g. month). */
			'singular' => _x(
				'%2$s every %4$s, %1$d payments total',
				'recurring invoice limit format',
				'stripe'
			),
			/* translators: %1$d Number of payments. %2$s Recurring amount. %5$s Interval noun plural (e.g. months). %6$d Interval count. */
			'plural'   => _x(
				'%2$s every %6$d %5$s, %1$d payments total',
				'recurring invoice limit format',
				'stripe'
			),
			/* translators: Example label for recurring invoice limit format setting. */
			'label'    => __( '$24 every month, 12 payments total', 'stripe' ),
		),
	);
}

/**
 * Formats a recurring invoice limit description string.
 *
 * @since 4.17.1
 *
 * @param string $format_key     Format key (e.g. 'count_adj_amount').
 * @param int    $count          Number of payments (invoice limit).
 * @param string $amount         Formatted recurring amount (e.g. '$24').
 * @param string $adjective      Interval adjective (e.g. 'monthly').
 * @param string $noun_singular  Interval noun singular (e.g. 'month').
 * @param string $noun_plural    Interval noun plural (e.g. 'months').
 * @param int    $interval_count Interval count (e.g. 1, 3).
 * @param int    $form_id        Payment form ID for per-form filtering.
 * @return string Formatted description string.
 */
function simpay_format_recurring_invoice_limit(
	$format_key,
	$count,
	$amount,
	$adjective,
	$noun_singular,
	$noun_plural,
	$interval_count,
	$form_id = 0
) {
	/**
	 * Filters the recurring description format key.
	 *
	 * Allows overriding the format on a per-form basis.
	 *
	 * @since 4.17.1
	 *
	 * @param string $format_key Format key (e.g. 'count_adj_amount').
	 * @param int    $form_id    Payment form ID. 0 if unknown.
	 */
	$format_key = apply_filters(
		'simpay_recurring_description_format',
		$format_key,
		$form_id
	);

	$formats = simpay_get_recurring_invoice_limit_formats();
	$format  = isset( $formats[ $format_key ] )
		? $formats[ $format_key ]
		: $formats['count_adj_amount'];

	$template = 1 === $interval_count
		? $format['singular']
		: $format['plural'];

	return sprintf(
		$template,
		$count,
		$amount,
		$adjective,
		$noun_singular,
		$noun_plural,
		$interval_count
	);
}

/**
 * Resolves the invoice limit format key for a given form.
 *
 * Checks the global setting, then looks for a per-form override stored
 * as `_recurring_amount_format` post meta. Returns a validated format key.
 *
 * @since 4.17.1
 *
 * @param int $form_id Form post ID. 0 for global only.
 * @return string Validated format key.
 */
function simpay_get_recurring_invoice_limit_format_key( $form_id = 0 ) {
	$format_key = simpay_get_setting(
		'recurring_amount_format',
		'count_adj_amount'
	);

	// Check for a per-form override.
	if ( $form_id > 0 ) {
		$form_format = get_post_meta(
			$form_id,
			'_recurring_amount_format',
			true
		);

		if ( ! empty( $form_format ) ) {
			$format_key = $form_format;
		}
	}

	// Validate the key exists.
	$formats = simpay_get_recurring_invoice_limit_formats();

	if ( ! isset( $formats[ $format_key ] ) ) {
		$format_key = 'count_adj_amount';
	}

	return $format_key;
}

/**
 * Returns the invoice limit format string for a given variant and form.
 *
 * @since 4.17.1
 *
 * @param string $variant 'singular' or 'plural'.
 * @param int    $form_id Form post ID. 0 for global only.
 * @return string Format string with sprintf placeholders.
 */
function simpay_get_invoice_limit_format_string( $variant, $form_id = 0 ) {
	$format_key = simpay_get_recurring_invoice_limit_format_key( $form_id );
	$formats    = simpay_get_recurring_invoice_limit_formats();
	$format     = $formats[ $format_key ];

	$valid_variants = array( 'singular', 'plural' );

	if ( ! in_array( $variant, $valid_variants, true ) ) {
		$variant = 'singular';
	}

	return $format[ $variant ];
}

/**
 * Returns the global invoice limit format string for the given variant.
 *
 * Used by shared script variables where no per-form context is available.
 *
 * @since 4.17.1
 *
 * @param string $variant 'singular' or 'plural'.
 * @return string Format string with sprintf placeholders.
 */
function simpay_get_global_invoice_limit_format_string( $variant ) {
	return simpay_get_invoice_limit_format_string( $variant );
}
