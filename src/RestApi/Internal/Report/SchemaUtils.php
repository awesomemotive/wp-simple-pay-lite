<?php
/**
 * Rest API: Report schema utilities
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\RestApi\Internal\Report;

use SimplePay\Core\Report\DateRange;
use InvalidArgumentException;
use WP_Error;

/**
 * SchemaUtils class.
 *
 * @since 4.7.3
 */
class SchemaUtils {

	/**
	 * Returns the schema for the date range parameter.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string, mixed>
	 */
	public static function get_date_range_schema() {
		return array(
			'type'              => 'object',
			'context'           => array( 'view', 'edit' ),
			'description'       => __(
				'The date range to retrieve results from.',
				'stripe'
			),
			'sanitize_callback' => array( __CLASS__, 'sanitize_date_range_arg' ),
			'validate_callback' => array( __CLASS__, 'validate_date_range_arg' ),
			'properties'        => array(
				'type'  => array(
					'type'     => 'string',
					'required' => true,
					'enum'     => DateRange::RANGE_TYPES,
					'default'  => '7days',
				),
				'start' => array(
					'type'     => 'string',
					'format'   => 'date-time',
					'required' => true,
					'default'  => gmdate(
						'Y-m-d 00:00:00',
						strtotime( '-6 days' )
					),
				),
				'end'   => array(
					'type'     => 'string',
					'format'   => 'date-time',
					'required' => true,
					'default'  => gmdate(
						'Y-m-d 23:59:59',
						strtotime( 'today' )
					),
				),
			),
		);
	}

	/**
	 * Validates that a date range parameter can be transformed into a DateRange object.
	 *
	 * @since 4.7.3
	 *
	 * @param array<string, string> $value The date range parameter value.
	 * @param \WP_REST_Request      $request The request object.
	 * @param string                $param The parameter name.
	 * @return \WP_Error|bool True if the date range is valid, otherwise a WP_Error object.
	 */
	public static function validate_date_range_arg( $value, $request, $param ) {
		$validate = rest_validate_request_arg( $value, $request, $param );

		if ( is_wp_error( $validate ) ) {
			return $validate;
		}

		try {
			new DateRange(
				$value['type'],
				$value['start'],
				$value['end']
			);
		} catch ( InvalidArgumentException $e ) {
			return new WP_Error(
				'rest_invalid_param',
				$e->getMessage(),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Sanitizes the standard date range parameter and converts start and end
	 * dates to \DateTimeImmutable objects.
	 *
	 * @since 4.7.3
	 *
	 * @param array<string, string> $value The date range parameter value.
	 * @param \WP_REST_Request      $request The request object.
	 * @param string                $param The parameter name.
	 * @return mixed|\SimplePay\Core\Report\DateRange The sanitized date range, or a WP_Error object.
	 */
	public static function sanitize_date_range_arg( $value, $request, $param ) {
		$value = rest_sanitize_request_arg( $value, $request, $param );

		if ( is_wp_error( $value ) ) {
			return $value;
		}

		/** @var array<string, string> $value */

		/** @var string $type */
		$type = isset( $value['type'] ) ? $value['type'] : '';

		/** @var string $start */
		$start = isset( $value['start'] ) ? $value['start'] : '';

		/** @var string $end */
		$end = isset( $value['end'] ) ? $value['end'] : '';

		return new DateRange( $type, $start, $end );
	}

	/**
	 * Returns the schema for the currency parameter.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string, mixed>
	 */
	public static function get_default_date_range() {
		return array(
			'start' => gmdate(
				'Y-m-d 00:00:00',
				strtotime( '-6 days', time() )
			),
			'end'   => gmdate(
				'Y-m-d 23:59:59',
				time()
			),
			'type'  => '7days',
		);
	}

	/**
	 * Returns the site's default currency.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public static function get_default_currency() {
		/** @var string $default_currency */
		$default_currency = simpay_get_setting( 'currency', 'USD' );

		return strtolower( $default_currency );
	}

	/**
	 * Returns the schema for the currency parameter.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string, mixed>
	 */
	public static function get_currency_schema() {
		return array(
			'description'       => __(
				'The currency to use for the report.',
				'stripe'
			),
			'type'              => 'string',
			'sanitize_callback' => 'rest_sanitize_request_arg',
			'validate_callback' => 'rest_validate_request_arg',
			'required'          => true,
			'enum'              => array_map(
				'strtolower',
				array_keys( simpay_get_currencies() )
			),
			'default'           => self::get_default_currency(),
		);
	}

	/**
	 * Returns the arguments to register the date range user meta.
	 *
	 * @since 4.7.3
	 *
	 * @return array<mixed>
	 */
	public static function get_date_range_user_preferences_args() {
		return array(
			'type'              => 'object',
			'single'            => true,
			'default'           => self::get_default_date_range(),
			'sanitize_callback' => function( $data ) {
				return array_map( 'sanitize_text_field', $data );
			},
			'show_in_rest'      => array(
				'type'   => 'object',
				'schema' => self::get_date_range_schema(),
			),
		);
	}

	/**
	 * Returns the arguments to register the currency user meta.
	 *
	 * @since 4.7.3
	 *
	 * @return array<mixed>
	 */
	public static function get_currency_user_preferences_args() {
		return array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => self::get_default_currency(),
			'show_in_rest'      => array(
				'type'   => 'string',
				'schema' => self::get_currency_schema(),
			),
		);
	}

}
