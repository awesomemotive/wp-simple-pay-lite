<?php
/**
 * Report: Period over Period Chart
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\Report\Chart;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;

/**
 * PeriodOverPeriodTrait trait.
 *
 * @template TStart of \DateTimeImmutable
 * @template TEnd of \DateTimeImmutable
 * @template _DatePeriod of \DatePeriod<TStart, TEnd, null>
 *
 * @since 4.6.7
 */
trait PeriodOverPeriodChartTrait {

	use ChartTrait;

	/**
	 * Returns the period over period chart's date periods and information.
	 *
	 * @since 4.6.7
	 *
	 * @param \SimplePay\Core\Report\DateRange $range The date range to use.
	 * @param string                           $interval The interval to use.
	 * @return array<string, _DatePeriod|int|string>
	 */
	protected function get_chart_period_over_period_date_periods( $range, $interval ) {
		$time = 'H' === $interval ? 'T' : '';

		// Create a DatePeriod for the current period.
		$current_period = new DatePeriod(
			$range->start,
			new DateInterval( "P{$time}1{$interval}" ),
			$range->end
		);

		// Determine how many intervals are in the current period.
		$count = iterator_count( $current_period );

		/** @var \DateTimeImmutable $current_period_start */
		$current_period_start = $current_period->getStartDate();

		/** @var \DateTimeImmutable $current_period_end */
		$current_period_end = $current_period->getEndDate();

		// Determine the start date of the previous period by subtracting the
		// number of intervals in the current period. The end date of the
		// previous period is not needed.
		/** @var \DateTime $previous_period_start */
		$previous_period_start = $current_period_start->sub(
			new DateInterval( 'P' . $time . $count . $interval )
		);

		/** @var \DateTime $previous_period_end */
		$previous_period_end = $previous_period_start->add(
			new DateInterval( 'P' . $time . $count . $interval )
		);

		// Create a DatePeriod for the previous period.
		$previous_period = new DatePeriod(
			$previous_period_start,
			new DateInterval( "P{$time}1{$interval}" ),
			$previous_period_end
		);

		// Create a DatePeriod that covers the entire range of the current and
		// previous periods.
		$full_period = new DatePeriod(
			$previous_period_start,
			new DateInterval( "P{$time}1{$interval}" ),
			$current_period_end
		);

		return array(
			'interval'        => $interval,
			'interval_count'  => $count,
			'full_period'     => $full_period,
			'current_period'  => $current_period,
			'previous_period' => $previous_period,
		);
	}

	/**
	 * Returns datasets with formatted data points for a given period and currency.
	 *
	 * @since 4.6.7
	 *
	 * @param array<string, _DatePeriod|string|int> $dates The date periods and information.
	 * @param array<string, \stdClass>              $data The results from the database query, keyed
	 *                                              by date (formatted based on the interval).
	 * @param callable                              $value_formatter A function that formats a datapoint value.
	 * @return array<int, array<int, array<string, mixed>>>
	 */
	private function get_chart_period_over_period_datasets( $dates, $data, $value_formatter ) {
		/** @var int $count */
		$count = $dates['interval_count'];

		/** @var string $interval */
		$interval = $dates['interval'];

		// Determine how many intervals are in the full period.
		/** @var _DatePeriod $full_period */
		$full_period    = $dates['full_period'];
		$iterable_items = iterator_to_array( $full_period );

		// Create two datasets: one for the current period and one for the
		// previous period. Each dataset will have the same number of data points.
		$datasets = array(
			array_slice( $iterable_items, 0, $count ),
			array_slice( $iterable_items, $count, $count ),
		);

		$formatted_datasets = array();

		// Format each dataset.
		foreach ( $datasets as $dataset ) {
			array_push(
				$formatted_datasets,
				$this->format_chart_period_over_period_dataset(
					$dataset,
					$datasets,
					$interval,
					$data,
					$value_formatter
				)
			);
		}

		return $formatted_datasets;
	}

	/**
	 * Formats a "Period over Period" dataset.
	 *
	 * @since 4.6.7
	 *
	 * @param array<int, \DateTimeInterface>        $dataset The dataset to format. Initial state is
	 *                                              an list of \DateTime objects.
	 * @param array<array<int, \DateTimeInterface>> $datasets The full list of datasets.
	 * @param string                                $interval The date interval used for the dataset.
	 * @param array<string, \stdClass>              $data The results from the database query, keyed
	 *                                              by date (formatted based on the interval).
	 * @param callable                              $value_formatter A function that formats a datapoint value.
	 * @return array<int, array<string, mixed>>
	 */
	function format_chart_period_over_period_dataset(
		$dataset,
		$datasets,
		$interval,
		$data,
		$value_formatter
	) {
		$datapoints = array();

		foreach ( $dataset as $x => $datapoint ) {
			$datapoints[] = $this->format_chart_period_over_period_datapoint(
				$datapoint,
				$x,
				$datasets,
				$interval,
				$data,
				$value_formatter
			);
		}

		return $datapoints;
	}

	/**
	 * Formats a datapoint in a dataset.
	 *
	 * @since 4.6.7
	 *
	 * @param \DateTimeInterface                    $datapoint The datapoint to format.
	 * @param int                                   $x Iteration key, used for the x-axis of each dataset.
	 *                                              Each dataset is the same length so this value allows
	 *                                              for the x-axis to be the same for each dataset.
	 * @param array<array<int, \DateTimeInterface>> $datasets The full list of datasets.
	 * @param string                                $interval The date interval used for the dataset.
	 * @param array<string, \stdClass>              $data The results from the database query, keyed
	 *                                              by date (formatted based on the interval).
	 * @param callable                              $value_formatter A function that formats a datapoint value.
	 * @return array<string, mixed>
	 */
	public function format_chart_period_over_period_datapoint(
		$datapoint,
		$x,
		$datasets,
		$interval,
		$data,
		$value_formatter
	) {
		// Generate X axis data.
		$x_axis = $this->get_chart_period_over_period_datapoint_x_axis(
			$datapoint,
			$x,
			$datasets,
			$interval
		);

		// Pull the total from the SQL query results. The results are grouped/keyed
		// by the date formatted based on the interval.
		$date_format = $this->get_php_date_format( $interval );
		$date        = $datapoint->format( $date_format );

		$y = isset( $data[ $date ] ) ? (int) $data[ $date ]->value : 0;

		return array(
			// Simplified x/y values for plotting the chart.
			'x'     => $x_axis['x'],
			'y'     => $y,

			// Formatted values for display.
			'label' => $x_axis['label'],
			'value' => call_user_func( $value_formatter, $y ),
		);
	}

	/**
	 * Generates X-axis data for a datapoint in a dataset.
	 *
	 * A "true" x-axis value is determined by using the iteration key ($x) and
	 * pulling the corresponding value from the current period dataset. This
	 * value remains static for both datasets, so they overlap when displayed.
	 *
	 * A "label" value is determined by using the iteration key ($x) and pulling
	 * the corresponding value from the current period dataset.
	 *
	 * @since 4.6.7
	 *
	 * @param \DateTimeInterface                    $datapoint The datapoint to format.
	 * @param int                                   $x Iteration key, used for the x-axis of each dataset.
	 *                                              Each dataset is the same length so this value allows
	 *                                              for the x-axis to be the same for each dataset.
	 * @param array<array<int, \DateTimeInterface>> $datasets The full list of datasets.
	 * @param string                                $interval The date interval used for the dataset.
	 * @return array<string, string>
	 */
	private function get_chart_period_over_period_datapoint_x_axis(
		$datapoint,
		$x,
		$datasets,
		$interval
	) {
		/** @var int $dynamic_period_datapoint_timestamp */
		$dynamic_period_datapoint_timestamp = $datapoint->getTimestamp();

		/** @var int $fixed_period_datapoint_timestamp */
		$fixed_period_datapoint_timestamp = $datasets[1][ $x ]->getTimestamp();

		/** @var string $date_i18n_format */
		$date_i18n_format = get_option( 'date_format', 'F jS' );

		/** @var string $time_i18n_format */
		$time_i18n_format = get_option( 'time_format', 'g:i a' );

		switch ( $interval ) {
			// Hourly.
			case 'H':
				$x = date_i18n(
					$date_i18n_format . ' ' . $time_i18n_format,
					$fixed_period_datapoint_timestamp
				);

				$label = date_i18n(
					$date_i18n_format . ' ' . $time_i18n_format,
					$dynamic_period_datapoint_timestamp
				);
				break;
			// Weekly.
			case 'W':
				$days = array(
					0 => 'sunday',
					1 => 'monday',
					2 => 'tuesday',
					3 => 'wednesday',
					4 => 'thursday',
					5 => 'friday',
					6 => 'saturday',
				);

				$first_day_of_week_no = absint( get_option( 'start_of_week' ) );
				$last_day_of_week_no  = 0 === $first_day_of_week_no
					? 6
					: $first_day_of_week_no - 1;

				$first_day_of_week = $days[ $first_day_of_week_no ];
				$last_day_of_week  = $days[ $last_day_of_week_no ];

				/** @var int $fixed_period_start_timestamp */
				$fixed_period_start_timestamp = strtotime(
					"$first_day_of_week -1 week",
					$fixed_period_datapoint_timestamp
				);

				/** @var int $fixed_period_end_timestamp */
				$fixed_period_end_timestamp = strtotime(
					"next $last_day_of_week",
					$fixed_period_datapoint_timestamp
				);

				$x = sprintf(
					'%s - %s',
					gmdate(
						$date_i18n_format,
						$fixed_period_start_timestamp
					),
					gmdate(
						$date_i18n_format,
						$fixed_period_end_timestamp
					)
				);

				/** @var int $dynamic_period_start_timestamp */
				$dynamic_period_start_timestamp = strtotime(
					"$first_day_of_week -1 week",
					$dynamic_period_datapoint_timestamp
				);

				/** @var int $dynamic_period_start_timestamp */
				$dynamic_period_end_timestamp = strtotime(
					"next $last_day_of_week",
					$dynamic_period_datapoint_timestamp
				);

				$label = sprintf(
					'%s - %s',
					gmdate(
						$date_i18n_format,
						$dynamic_period_start_timestamp
					),
					gmdate(
						$date_i18n_format,
						$dynamic_period_end_timestamp
					)
				);

				break;
			case 'M':
				/** @var string $fixed_period_date */
				$fixed_period_date = gmdate(
					'01-m-Y',
					$fixed_period_datapoint_timestamp
				);

				/** @var int $fixed_period_timestamp */
				$fixed_period_timestamp = strtotime( $fixed_period_date );

				/** @var string $dynamic_period_date */
				$dynamic_period_date = gmdate(
					'01-m-Y',
					$dynamic_period_datapoint_timestamp
				);

				/** @var int $dynamic_period_timestamp */
				$dynamic_period_timestamp = strtotime( $dynamic_period_date );

				$x     = gmdate( 'F Y', $fixed_period_timestamp );
				$label = gmdate( 'F Y', $dynamic_period_timestamp );

				break;
			case 'Y':
				/** @var string $fixed_period_date */
				$fixed_period_date = gmdate(
					'31-12-Y',
					$fixed_period_datapoint_timestamp
				);

				/** @var int $fixed_period_timestamp */
				$fixed_period_timestamp = strtotime( $fixed_period_date );

				/** @var string $dynamic_period_date */
				$dynamic_period_date = gmdate(
					'31-12-Y',
					$dynamic_period_datapoint_timestamp
				);

				/** @var int $dynamic_period_timestamp */
				$dynamic_period_timestamp = strtotime( $dynamic_period_date );

				$x     = gmdate( 'Y', $fixed_period_timestamp );
				$label = gmdate( 'Y', $dynamic_period_timestamp );

				break;
			default:
				$x = gmdate(
					$date_i18n_format,
					$fixed_period_datapoint_timestamp
				);

				$label = gmdate(
					$date_i18n_format,
					$dynamic_period_datapoint_timestamp
				);
		}

		return array(
			'x'     => $x,
			'label' => $label,
		);
	}

	/**
	 * Returns a value representing the "total" for the current period.
	 *
	 * @since 4.6.7
	 *
	 * @param array<int, array<int, array<string, mixed>>> $datasets The formatted datasets to use for the chart.
	 * @return int The total for the current period.
	 */
	private function get_chart_period_over_period_current_period_total( $datasets ) {
		if ( ! isset( $datasets[1] ) || ! is_array( $datasets[1] ) ) {
			return 0;
		}

		return $this->get_chart_formatted_dataset_total( $datasets[1] );
	}

	/**
	 * Returns a value representing the "total" for the previous period.
	 *
	 * @since 4.6.7
	 *
	 * @param array<int, array<int, array<string, mixed>>> $datasets The formatted datasets to use for the chart.
	 * @return int The total for the previous period.
	 */
	private function get_chart_period_over_period_previous_period_total( $datasets ) {
		if ( ! isset( $datasets[0] ) || ! is_array( $datasets[0] ) ) {
			return 0;
		}

		return $this->get_chart_formatted_dataset_total( $datasets[0] );
	}

	/**
	 * Calculates the delta/change between the current and previous periods.
	 *
	 * @since 4.6.7
	 *
	 * @param array<int, array<int, array<string, mixed>>> $datasets The formatted datasets to use for the chart.
	 * @return float
	 */
	private function get_chart_period_over_period_delta( $datasets ) {
		$curr_total = $this->get_chart_period_over_period_current_period_total(
			$datasets
		);

		$prev_total = $this->get_chart_period_over_period_previous_period_total(
			$datasets
		);

		return $this->get_delta( $curr_total, $prev_total );
	}

	/**
	 * Returns the primary color for the current period.
	 *
	 * @since 4.6.7
	 *
	 * @return array<int> RGB color values.
	 */
	protected function get_chart_period_over_period_current_period_primary_color() {
		return $this->get_user_color_scheme_pref_primary_color();
	}

	/**
	 * Returns the primary color for the previous period.
	 *
	 * @since 4.6.7
	 *
	 * @return array<int> RGB color values.
	 */
	protected function get_chart_period_over_period_previous_period_primary_color() {
		return array( 220, 220, 220 );
	}
}

