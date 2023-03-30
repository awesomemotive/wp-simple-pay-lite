<?php
/**
 * Report: Trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Report;

/**
 * Report trait.
 *
 * @since 4.7.3
 */
trait ReportTrait {

	/**
	 * Returns the delta between two numbers.
	 *
	 * @since 4.7.3
	 *
	 * @param float $current Current number.
	 * @param float $previous Previous number.
	 * @return float
	 */
	protected function get_delta( $current, $previous ) {
		if ( $current > 0 && $previous > 0 ) {
			return round( ( $current - $previous ) / $previous * 100 );
		}

		return 0;
	}

	/**
	 * Determines a sane date range interval based on the date range type.
	 *
	 * E.g. if the date range type is '3months', the interval will be 'W' (week).
	 * This prevents the chart from displaying too many datapoints.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Core\Report\DateRange $range The date range to use for the report.
	 * @return string The DatePeriod interval to use for the report.
	 */
	protected function get_date_range_interval( $range ) {
		$range_diff = $range->start->diff( $range->end );

		switch ( $range->type ) {
			case 'today':
				return 'H';
			case '3months':
				return 'W';
			case '12months':
				return 'M';
			case 'yeartodate':
				if ( $range_diff->m > 1 ) {
					return 'M';
				} elseif ( 1 === $range_diff->m ) {
					return 'W';
				} else {
					return 'D';
				}
			case 'custom':
				if ( $range_diff->days <= 2 ) {
					return 'H';
				} elseif ( $range_diff->days <= 30 ) {
					return 'D';
				} elseif ( $range_diff->days <= 90 ) {
					return 'W';
				} else {
					return 'M';
				}
				// last7.
				// 4weeks.
			default:
				return 'D';
		}
	}

	/**
	 * Determines the SQL date format to use for the given interval.
	 *
	 * @since 4.7.3
	 *
	 * @param string $interval The DatePeriod interval to use for the report.
	 * @return string The SQL date format to use for the report.
	 */
	protected function get_sql_select_date_format( $interval ) {
		switch ( $interval ) {
			case 'H':
				return '%%Y-%%m-%%d %%H:00:00';
			case 'D':
				return '%%Y-%%m-%%d';
			case 'W':
				$first_day_of_week = absint( get_option( 'start_of_week' ) );

				// Week begins on Monday, ISO 8601.
				if ( 1 === $first_day_of_week ) {
					return '%%x-%%v';
				}

				// Week begins on day other than specified by ISO 8601.
				return '%%X-%%V';
			case 'M':
				return '%%Y-%%m';
			default:
				return '%%Y';
		}
	}

	/**
	 * Determines the PHP date format to use for the given interval.
	 *
	 * @since 4.7.3
	 *
	 * @param string $interval The DatePeriod interval to use for the report.
	 * @return string The PHP date format to use for the report.
	 */
	protected function get_php_date_format( $interval ) {
		switch ( $interval ) {
			case 'H':
				return 'Y-m-d H:i:s';
			case 'D':
				return 'Y-m-d';
			case 'W':
				return 'Y-W';
			case 'M':
				return 'Y-m';
			default:
				return 'Y';
		}
	}

	/**
	 * Determines the SQL date SELECT string to use for the given interval.
	 *
	 * This ensures the query groups the results by the correct date interval.
	 *
	 * @since 4.7.3
	 *
	 * @param string $interval The DatePeriod interval to use for the report.
	 * @return string The SQL date SELECT string to use for the report.
	 */
	protected function get_sql_select_date_as( $interval ) {
		$date_format = $this->get_sql_select_date_format( $interval );
		$column      = 'date_created';

		switch ( $interval ) {
			case 'H':
			case 'D':
				return "DATE_FORMAT($column, '$date_format')";
			case 'W':
				$first_day_of_week = absint( get_option( 'start_of_week' ) );

				// Week begins on Monday, ISO 8601.
				if ( 1 === $first_day_of_week ) {
					return "DATE_FORMAT($column, '$date_format')";
				}

				// Week begins on day other than specified by ISO 8601.
				return "CONCAT(YEAR($column), '-', LPAD( FLOOR( ( DAYOFYEAR($column) + ( ( DATE_FORMAT(MAKEDATE(YEAR($column),1), '%%w') - $first_day_of_week + 7 ) %% 7 ) - 1 ) / 7  ) + 1 , 2, '0'))";
			case 'M':
				return "DATE_FORMAT($column, '$date_format')";
			default:
				return "YEAR($column)";
		}
	}

	/**
	 * Returns the timezone offset for the current site.
	 *
	 * @since 4.7.3
	 *
	 * @return string The timezone offset for the current site.
	 */
	protected function get_timezone_offset() {
		/** @var string $offset  */
		$offset = get_option( 'gmt_offset', '0' );
		/** @var float $offset */
		$offset = (float) $offset;

		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign      = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );
		$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

		return $tz_offset;
	}

}
