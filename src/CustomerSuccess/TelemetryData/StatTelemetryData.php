<?php
/**
 * Telemetry: Transactions
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess\TelemetryData;

/**
 * TransactionTelemetryData class.
 *
 * @since 4.7.10
 */
class StatTelemetryData extends AbstractTransactionTelemetryData {

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		$lifetime = $this->get_transaction_amount_by_pm_by_date_range();

		/** @var string $currency */
		$currency = simpay_get_setting( 'currency', 'usd' );
		$currency = strtolower( $currency );

		return array(
			sprintf( 'transaction_average_%s', $currency ) => $this->get_average_transaction_amount(),
			sprintf( 'transaction_total_%s', $currency )   => array_reduce(
				$lifetime,
				function( $carry, $amount ) {
					return (int) $carry + (int) $amount['amount'];
				},
				0
			),
			sprintf( 'transaction_count_%s', $currency )   => array_reduce(
				$lifetime,
				function( $carry, $amount ) {
					return (int) $carry + (int) $amount['count'];
				},
				0
			),

			'form_count'                                   => $this->get_form_count(),
			'form_price_options_average'                   => $this->get_price_options_average(),
			'form_custom_fields_average'                   => $this->get_custom_fields_average(),
			'form_payment_methods_average'                 => $this->get_payment_methods_average(),
		);
	}

	/**
	 * Returns the number of `simple-pay` post_type posts that are not auto drafts.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_form_count() {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT( ID ) FROM $wpdb->posts WHERE post_type = %s AND post_status != %s",
				'simple-pay',
				'auto-draft'
			)
		);
	}

	/**
	 * Returns the average transaction amount for the site's default currency.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_average_transaction_amount() {
		global $wpdb;

		/** @var string $currency */
		$currency = simpay_get_setting( 'currency', 'usd' );
		$currency = strtolower( $currency );

		$select_value = simpay_is_zero_decimal( $currency )
			? 'AVG(amount_total)'
			: 'AVG(amount_total / 100)';

		$average = $wpdb->get_var(
			// @phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT {$select_value} AS average_amount
				FROM {$wpdb->prefix}wpsp_transactions
				WHERE status = 'succeeded'
				AND currency = %s;",
				$currency
			)
			// @phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return (int) round( $average );
	}

	/**
	 * Returns the average number of price options on each payment form.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_price_options_average() {
		global $wpdb;

		$payment_mode = simpay_is_test_mode() ? 'test' : 'live';

		$price_option_meta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
				sprintf( '_simpay_prices_%s', $payment_mode )
			)
		);

		$average = 0;

		foreach ( $price_option_meta as $price_option ) {
			/** @var array<string, mixed> $price_option_data */
			$price_option_data = maybe_unserialize( $price_option );

			if ( ! is_array( $price_option_data ) ) {
				$average += 0;
			}

			$average += count( $price_option_data );
		}

		return (int) floor( $average / count( $price_option_meta ) );
	}

	/**
	 * Returns the average number of payment methods on each payment form.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_payment_methods_average() {
		global $wpdb;

		$payment_methods_meta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
				'_payment_methods'
			)
		);

		$payment_form_payment_methods = array_map(
			function( $payment_methods ) {
				/** @var array<string, string> $payment_methods */
				$payment_methods = maybe_unserialize( $payment_methods );
				/** @var array<string, array<string, string>> $payment_methods */
				$payment_methods = current( $payment_methods );

				return array_map(
					function( $payment_method ) {
						return $payment_method['id'];
					},
					$payment_methods
				);
			},
			$payment_methods_meta
		);

		$average = 0;

		foreach ( $payment_form_payment_methods as $payment_form_payment_method ) {
			$average += count( $payment_form_payment_method );
		}

		return (int) floor( $average / count( $payment_methods_meta ) );
	}

	/**
	 * Returns the average number of custom fields per form.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_custom_fields_average() {
		global $wpdb;

		$custom_fields_meta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
				'_custom_fields'
			)
		);

		foreach ( $custom_fields_meta as $key => $custom_field ) {
			$custom_fields_meta[ $key ] = maybe_unserialize( $custom_field );
		}

		if ( ! is_array( $custom_fields_meta ) ) {
			return 0;
		}

		$average = 0;

		foreach ( $custom_fields_meta as $custom_field_data ) {
			foreach ( $custom_field_data as $custom_field_children ) {
				$average += count( $custom_field_children );
			}
		}

		return (int) floor( $average / count( $custom_fields_meta ) );
	}

}
