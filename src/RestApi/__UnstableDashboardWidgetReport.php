<?php
/**
 * REST API: Dashboard widget
 *
 * Note: This is a temporary solution until the other REST API has been full migrated
 * to the new plugin container.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\RestApi;

use DateInterval;
use DatePeriod;
use DateTime;
use SimplePay\Core\EventManagement\SubscriberInterface;
use WP_REST_Response;
use WP_REST_Server;

/**
 * __UnstableDashboardWidgetReport class.
 *
 * @since 4.4.6
 */
class __UnstableDashboardWidgetReport implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init' => 'register_meta',
			'rest_api_init' => array(
				array( 'register_route' ),
			),
		);
	}

	/**
	 * Registers the REST API route for GET /wpsp/v2/notifications.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function register_route() {
		/** @var string $default_currency */
		$default_currency = simpay_get_setting( 'currency', 'USD' );

		register_rest_route(
			'wpsp/v2',
			'report/dashboard-widget',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_report' ),
					'permission_callback' => array( $this, 'can_view_report' ),
					'args'                => array(
						'range'   => array(
							'description'       => __(
								'The date range to retrieve results from. Only predefined ranges are currently supported.',
								'stripe'
							),
							'type'              => 'string',
							'enum'              => array( 'last7', 'last30' ),
							'default'           => 'last7',
							'required'          => false,
						),
						'currency' => array(
							'description'       => __(
								'The currency to use for the report.',
								'stripe'
							),
							'type'              => 'string',
							'default'           => strtolower(
								$default_currency
							),
							'required'          => false,
							'minLength'         => 3,
							'maxLength'         => 3,
							'pattern'           => '[a-z]{3}',
						)
					),
				)
			)
		);
	}

	/**
	 * Determines if the current user can view the report.
	 *
	 * @since 4.4.6
	 *
	 * @return bool
	 */
	public function can_view_report() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns the dashboard widget report.
	 *
	 * @since 4.4.6
	 *
	 * @param \WP_REST_Request $request REST API request.
	 * @return \WP_REST_Response REST API response.
	 */
	public function get_report( $request ) {
		/** @var string $range */
		$range = $request->get_param( 'range' );

		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		// @todo don't tie to days specifically?
		$days          = 'last7' === $range ? 7 : 30;
		$now           = strtotime( 'today 00:00:00' );
		$current_start = strtotime( sprintf( '-%d days', $days ), $now );

		if ( false === $current_start ) {
			return new WP_REST_Response(
				array(
					'error' => __( 'Invalid date range.', 'stripe' ),
				),
				400
			);
		}

		// Current period start.
		$current_start = date( 'Y-m-d H:i:s', $current_start );

		/** @var int $period_start All periods start. */
		$period_start = strtotime( sprintf( '-%d days', $days * 2 ), $now );

		// Start/end for query.
		$start = date( 'Y-m-d H:i:s', $period_start );
		$end   = date( 'Y-m-d H:i:s', $now );

		$datasets = $this->get_datasets( $start, $end, $days, $currency );

		/** @var int $curr_total Calculate the total for the current period. */
		$curr_total = array_reduce(
			$datasets[1],
			function( $total, $day ) {
				return (int) $total + (int) $day['y'];
			}
		);

		/** @var int $prev_total Calculate the total for the previous period. */
		$prev_total = array_reduce(
			$datasets[0],
			function( $total, $day ) {
				return (int) $total + (int) $day['y'];
			}
		);

		// Find overall total.
		$total = $prev_total + $curr_total;

		// Determine the percentage change in total.
		$delta = 0;

		if ( $curr_total > 0 && $prev_total > 0 ) {
			$delta = round(
				( ( $curr_total - $prev_total ) / $prev_total ) * 100
			);
		}

		// Form information.
		$forms = $this->get_forms(
			$current_start,
			$end,
			$currency
		);

		// Return a response with report details.
		return new WP_REST_Response(
			array(
				'data' => array(
					'start'             => $start,
					'end'               => $end,
					'currency'          => array(
						'code'               => $currency,
						'symbol'             => simpay_get_currency_symbol(
							$currency
						),
						'position'           => simpay_get_currency_position(),
						'thousand_separator' => simpay_get_thousand_separator(),
						'decimal_separator'  => simpay_get_decimal_separator(),
					),
					'total'             => $curr_total,
					'delta'             => $delta,
					'forms'             => array(
						'top'       => $forms['top'],
						'remaining' => array(
							'count'           => $forms['remaining']['count'],
							'total'           => $forms['remaining']['total'],
							'total_formatted' => simpay_format_currency(
								(int) $forms['remaining']['total'],
								$currency
							),
						),
					),
					'chart'             => array(
						'datasets' => array(
							array(
								'label' => __( 'Current period', 'stripe' ),
								'rgb'   => array( 66, 138, 202 ),
								'data'  => $datasets[1],
							),
							array(
								'label' => __( 'Previous period', 'stripe' ),
								'rgb'   => array( 220, 220, 220 ),
								'data'  => $datasets[0],
							),
						)
					),
				)
			)
		);
	}

	/**
	 * Registers user meta to allow saving the selected filter values.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	public function register_meta() {
		// Date range.
		register_meta(
			'user',
			'simpay_dashboard_widget_report_date_range',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => 'last7',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => array( $this, 'can_view_report' ),
				'show_in_rest'      => array(
					'schema' => array(
						'required' => false,
						'type'     => 'string',
						'enum'     => array( 'last7', 'last30' ),
					)
				),
			)
		);

		// Currency.
		/** @var string $default_currency */
		$default_currency = simpay_get_setting( 'currency', 'USD' );

		register_meta(
			'user',
			'simpay_dashboard_widget_report_currency',
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => array( $this, 'can_view_report' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'     => 'string',
						'default'  => strtolower( $default_currency ),
						'required' => false,
						'enum'     => array_map(
							'strtolower',
							array_keys( simpay_get_currencies() )
						),
					),
				),
			)
		);
	}

	/**
	 * Returns datasets with formatted data points for a given period and currency.
	 *
	 * @todo This is all relatively static/expecting only two periods and using only days.
	 *
	 * @since 4.4.6
	 *
	 * @param string $start Start date. Y-m-d H:i:s.
	 * @param string $end End date. Y-m-d H:i:s.
	 * @param int    $days The number of days in each dataset/period.
	 * @param string $currency The currency to use for the report.
	 * @return array<int, array<int, array<string, float|string>>>
	 */
	private function get_datasets( $start, $end, $days, $currency ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT date_format(date_created, '%%Y-%%m-%%d') as date, SUM(amount_total) as amount_total FROM {$wpdb->prefix}wpsp_transactions WHERE currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s GROUP BY date",
				$currency,
				$start,
				$end
			),
			OBJECT_K
		);

		$period = new DatePeriod(
			new DateTime( $start ),
			new DateInterval( 'P1D' ),
			new DateTime( $end )
		);

		$iterable_days = iterator_to_array( $period );

		// @todo tied to two periods.
		$datasets      = array(
			array_slice( $iterable_days, 0, $days ),
			array_slice( $iterable_days, $days, $days ),
		);

		$formatted_datasets = array();

		// Loop through each dataset (static at 2).
		foreach ( $datasets as $dataset ) {
			$formatted_datasets[] = array_map(
				/**
				 * Formats a data point for a dataset.
				 *
				 * @since 4.4.6
				 *
				 * @param \DateTime $date Date.
				 * @param int       $x Iteration key, used for the x-axis of each dataset.
				 *
				 * @return array<string, string|int>
				 */
				function( $date, $x ) use( $results, $currency ) {
					$format = 'Y-m-d';
					$date   = $date->format( $format );
					$total  = isset( $results[ $date ] )
						? (int) $results[ $date ]->amount_total
						: 0;
					$is_zero_decimal = simpay_is_zero_decimal( $currency );

					/** @var string $date */
					/** @var int $timestamp */
					$timestamp = strtotime( $date );

					return array(
						// 'x', representing ticks on the x-axis must be the
						// same for all datasets so the lines overlap.
						// This is why we are mapping twice so we end up
						// with the same set of keys.
						'x'     => (string) $x,
						'y'     => round( $is_zero_decimal ? $total : $total / 100 ),
						'label' => date( 'F jS', $timestamp ),
						'value' => simpay_format_currency( $total, $currency ),
					);
				},
				$dataset,
				array_keys( $dataset )
			);
		}

		return $formatted_datasets;
	}

	/**
	 * Returns information about the forms used during the current period.
	 *
	 * @since 4.4.6
	 *
	 * @param string $start Start date. Y-m-d H:i:s.
	 * @param string $end End date. Y-m-d H:i:s.
	 * @param string $currency Report currency.
	 * @return array<string, array<array<string, int|string>|int>>
	 */
	private function get_forms( $start, $end, $currency ) {
		global $wpdb;

		$forms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT form_id, SUM(amount_total) as amount_total FROM {$wpdb->prefix}wpsp_transactions WHERE currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s GROUP BY form_id ORDER BY amount_total DESC",
				$currency,
				$start,
				$end
			)
		);

		if ( empty( $forms ) ) {
			return array(
				'top'       => array(),
				'remaining' => array(
					'count' => 0,
					'total' => 0,
				)
			);
		}

		// Retrieve additional form information for top performing forms.
		$top_limit = 5;
		$top_forms = array_slice( $forms, 0, $top_limit );
		$top_forms = array_map(
			/**
			 * Returns formatted form data.
			 *
			 * @since 4.4.6
			 *
			 * @param stdClass $form Payment form data.
			 * @return array<int, string|int>
			 */
			function( $form ) use ( $currency ) {
				$form_id = (int) $form->form_id;
				$total   = (int) $form->amount_total;

				/** @var string $title */
				$title = get_post_meta(
					$form_id,
					'_company_name',
					true
				);

				return array(
					'id'              => $form_id,
					'title'           => $title,
					'total'           => $total,
					'total_formatted' => simpay_format_currency(
						$total,
						$currency
					),
				);
			},
			$top_forms
		);

		// Calculate the remaining form count and total.
		$remaining_forms = array_slice( $forms, $top_limit );
		$remaining_count = count( $remaining_forms );
		$remaining_total = array_reduce(
			$remaining_forms,
			function( $total, $form ) {
				return (int) $total + (int) $form->amount_total;
			},
			0
		);

		return array(
			'top'       => $top_forms,
			'remaining' => array(
				'total' => $remaining_total,
				'count' => $remaining_count,
			),
		);
	}

}
