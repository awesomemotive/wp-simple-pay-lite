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
class TransactionTelemetryData extends AbstractTransactionTelemetryData {

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		/** @var string $currency */
		$currency = simpay_get_setting( 'currency', 'usd' );
		$currency = strtolower( $currency );

		$periods = array(
			'lifetime' => $this->get_transaction_amount_by_pm_by_date_range(),
			'30days'   => $this->get_transaction_amount_by_pm_by_date_range( '30days' ),
			'7days'    => $this->get_transaction_amount_by_pm_by_date_range( '7days' ),
		);

		$transactions = array();

		foreach ( $periods as $period => $types ) {
			foreach ( $types as $type => $data ) {
				$transactions[] = array(
					'period'   => $period,
					'type'     => $type,
					'currency' => $currency,
					'count'    => $data['count'],
					'total'    => $data['total'],
				);
			}
		}

		return $transactions;
	}

}
