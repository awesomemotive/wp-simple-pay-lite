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
 * AbstractTransactionTelemetryData class.
 *
 * @since 4.7.10
 */
abstract class AbstractTransactionTelemetryData extends AbstractTelemetryData {

	/**
	 * Returns transaction amounts for payment method types for a given date range
	 * for the site's default currency.
	 *
	 * @since 4.7.10
	 *
	 * @param 'all'|'7days'|'30days' $range Date range to select.
	 * @return array<int|string, array<string, int>> Payment method types and their transaction amounts.
	 */
	protected function get_transaction_amount_by_pm_by_date_range( $range = 'all' ) {
		global $wpdb;

		/** @var string $currency */
		$currency = simpay_get_setting( 'currency', 'usd' );
		$currency = strtolower( $currency );

		$select_value = simpay_is_zero_decimal( $currency )
			? 'SUM(amount_total) as total'
			: 'SUM(amount_total / 100) as total';

		$date_condition = '1=1';

		if ( '7days' === $range ) {
			$date_condition = 'date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
		} elseif ( '30days' === $range ) {
			$date_condition = 'date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
		}

		$transactions = $wpdb->get_results(
			// @phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT payment_method_type, {$select_value},
				COUNT(*) AS count
				FROM {$wpdb->prefix}wpsp_transactions
				WHERE status = 'succeeded'
				AND currency = %s
				AND {$date_condition}
				GROUP BY payment_method_type;",
				$currency
			),
			// @phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		$result = array();

		foreach ( $transactions as $transaction ) {
			if ( null === $transaction['payment_method_type'] ) {
				continue;
			}

			$result[ $transaction['payment_method_type'] ] = array(
				'total' => (int) round( $transaction['total'] ),
				'count' => (int) $transaction['count'],
			);
		}

		return $result;
	}

}
