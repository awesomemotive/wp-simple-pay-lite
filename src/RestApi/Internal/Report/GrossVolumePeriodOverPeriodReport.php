<?php
/**
 * Report: Gross Volume Period Over Period
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\RestApi\Internal\Report;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Report;
use WP_REST_Response;
use WP_REST_Server;

/**
 * GrossVolumePeriodOverPeriodReport class.
 *
 * @template TStart of \DateTimeImmutable
 * @template TEnd of \DateTimeImmutable
 * @template _DatePeriod of \DatePeriod<TStart, TEnd, null>
 *
 * @since 4.6.7
 */
class GrossVolumePeriodOverPeriodReport implements SubscriberInterface {

	use Report\ReportTrait;
	use Report\Chart\PeriodOverPeriodChartTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API route for `GET /wpsp/__internal__/report/gross-volume-period-over-period`.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			'wpsp/__internal__',
			'report/gross-volume-period-over-period',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_report' ),
					'permission_callback' => array( $this, 'can_view_report' ),
					'args'                => array(
						'range'    => SchemaUtils::get_date_range_schema(),
						'currency' => SchemaUtils::get_currency_schema(),
					),
				),
			)
		);
	}

	/**
	 * Determines if the current user can view the report.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function can_view_report() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns the dashboard widget report.
	 *
	 * @since 4.6.7
	 *
	 * @param \WP_REST_Request $request REST API request.
	 * @return \WP_REST_Response REST API response.
	 */
	public function get_report( $request ) {
		/** @var \SimplePay\Core\Report\DateRange $range */
		$range = $request->get_param( 'range' );

		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		// Determine the interval used for the date range.
		// This is used to ensure a reasonable number of data points are returned.
		$interval = $this->get_date_range_interval( $range );

		// Determine the relevant date periods for the given range and interval.
		$dates = $this->get_chart_period_over_period_date_periods(
			$range,
			$interval
		);

		// Query for the data.
		$results = $this->get_results( $dates, $range, $currency );

		// Format the found data for a Period Over Period chart.
		$datasets = $this->get_chart_period_over_period_datasets(
			$dates,
			$results,
			function ( $value ) use ( $currency ) {
				return simpay_format_currency(
					$value,
					$currency,
					true,
					false
				);
			}
		);

		$total = $this->get_chart_period_over_period_current_period_total(
			$datasets
		);

		$total_formatted = simpay_format_currency(
			$total,
			$currency,
			true,
			false
		);

		$primary_color = $this->get_chart_period_over_period_current_period_primary_color();

		$report = array(
			'start'           => $range->start,
			'end'             => $range->end,
			'currency'        => array(
				'code'               => $currency,
				'symbol'             => simpay_get_currency_symbol(
					$currency
				),
				'position'           => simpay_get_currency_position(),
				'thousand_separator' => simpay_get_thousand_separator(),
				'decimal_separator'  => simpay_get_decimal_separator(),
			),
			'total'           => $total,
			'total_formatted' => $total_formatted,
			'delta'           => $this->get_chart_period_over_period_delta( $datasets ),
			'chart'           => array(
				'primary_color' => $primary_color,
				'datasets'      => array(
					array(
						'label' => __( 'Current period', 'stripe' ),
						'rgb'   => $primary_color,
						'data'  => $datasets[1],
						'type'  => 'line',
					),
					array(
						'label' => __( 'Previous period', 'stripe' ),
						'rgb'   =>
							$this->get_chart_period_over_period_previous_period_primary_color(),
						'data'  => $datasets[0],
						'type'  => 'line',
					),
				),
			),
		);

		return new WP_REST_Response( $report );
	}

	/**
	 * Returns the transaction data for the report.
	 *
	 * @since 4.6.7
	 *
	 * @param array<string, _DatePeriod|int|string> $dates Report date periods.
	 * @param \SimplePay\Core\Report\DateRange      $range Report date range.
	 * @param string                                $currency Report currency.
	 * @return array<\stdClass>
	 */
	private function get_results( $dates, $range, $currency ) {
		// Determine an appropriate interval for the date range.
		$interval = $this->get_date_range_interval( $range );

		$dates = $this->get_chart_period_over_period_date_periods(
			$range,
			$interval
		);

		/** @var _DatePeriod $previous_period */
		$previous_period = $dates['previous_period'];

		/** @var _DatePeriod $current_period */
		$current_period = $dates['current_period'];

		/** @var \DateTimeInterface $previous_period_start */
		$previous_period_start = $previous_period->getStartDate();

		/** @var \DateTimeInterface $current_period_end */
		$current_period_end = $current_period->getEndDate();

		// Determine how the date should be selected from the database based on
		// the date range interval.
		$select_date = $this->get_sql_select_date_as(
			$this->get_date_range_interval( $range )
		);

		// Determine how the amount value should be retrieved based on the currency.
		$select_value = simpay_is_zero_decimal( $currency )
			? 'SUM(amount_total)'
			: 'SUM(amount_total / 100)';

		// Query for results.
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$select_date} as date, {$select_value} as value FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s GROUP BY date", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				simpay_is_test_mode() ? 0 : 1,
				$currency,
				$previous_period_start->format( 'Y-m-d 00:00:00' ),
				$current_period_end->format( 'Y-m-d 23:59:59' )
			),
			OBJECT_K
		);
	}
}
