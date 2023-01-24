<?php
/**
 * Report
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\Report;

/**
 * Report abstract class.
 *
 * @since 4.6.7
 */
abstract class AbstractReport {

	/**
	 * Determines if the current user can view the report.
	 *
	 * @since 4.6.7
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
	abstract public function get_report( $request );

	/**
	 * Returns the delta between two numbers.
	 *
	 * @since 4.6.7
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
	 * @since 4.6.7
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
	 * @since 4.6.7
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
	 * @since 4.6.7
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
	 * @since 4.6.7
	 *
	 * @param string $interval The DatePeriod interval to use for the report.
	 * @return string The SQL date SELECT string to use for the report.
	 */
	protected function get_sql_select_date_as( $interval ) {
		$date_format     = $this->get_sql_select_date_format( $interval );
		$timezone_offset = $this->get_timezone_offset();

		$column    = 'date_created';
		$column_tz = "CONVERT_TZ($column, '+00:00', '$timezone_offset'), '$date_format'";

		switch ( $interval ) {
			case 'H':
				return "DATE_FORMAT($column_tz)";
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
	 * Returns the arguments to register the date range user meta.
	 *
	 * @since 4.6.7
	 *
	 * @return array<string, mixed>
	 */
	protected function get_date_range_user_preferences_args() {
		return array_merge(
			SchemaUtils::get_date_range_user_preferences_args(),
			array(
				'auth_callback' => array( $this, 'can_view_report' ),
			)
		);
	}

	/**
	 * Returns the arguments to register the currency user meta.
	 *
	 * @since 4.6.7
	 *
	 * @return array<string, mixed>
	 */
	protected function get_currency_user_preferences_args() {
		return array_merge(
			SchemaUtils::get_currency_user_preferences_args(),
			array(
				'auth_callback' => array( $this, 'can_view_report' ),
			)
		);
	}

	/**
	 * Returns the primary color for the current user's admin color scheme.
	 *
	 * @since 4.6.7
	 *
	 * @return array<int> RGB color values.
	 */
	protected function get_user_color_scheme_pref_primary_color() {
		$color_scheme = get_user_meta(
			get_current_user_id(),
			'admin_color',
			true
		);

		switch ( $color_scheme ) {
			case 'modern':
				return array( 56, 88, 233 );
			case 'light':
			case 'blue':
				return array( 9, 100, 132 );
			case 'coffee':
				return array( 199, 165, 137 );
			case 'ectoplasm':
				return array( 163, 183, 69 );
			case 'midnight':
			case 'ocean':
				return array( 105, 168, 187 );
			case 'sunrise':
				return array( 207, 73, 68 );
			default:
				return array( 66, 138, 202 );
		}
	}

	/**
	 * Returns the timezone offset for the current site.
	 *
	 * @since 4.6.7
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
