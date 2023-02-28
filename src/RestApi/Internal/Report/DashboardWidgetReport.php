<?php
/**
 * Report: Dashboard widget
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
 * DashboardWidgetReport class.
 *
 * @since 4.6.7
 */
class DashboardWidgetReport extends GrossVolumePeriodOverPeriodReport implements SubscriberInterface {

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
	 * Registers the REST API route for `GET /wpsp/__internal__/report/dashboard-widget`.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			'wpsp/__internal__',
			'report/dashboard-widget',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_report' ),
					'permission_callback' => array( $this, 'can_view_report' ),
					'args'                => array(
						'range'    => Report\SchemaUtils::get_date_range_schema(),
						'currency' => Report\SchemaUtils::get_currency_schema(),
					),
				),
			)
		);
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
		// Get the standard Gross Volume report.
		/** @var array<string, mixed> $report */
		$report = parent::get_report( $request )->get_data();

		/** @var \SimplePay\Core\Report\DateRange $range */
		$range = $request->get_param( 'range' );

		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		// Add Top Forms data in addition to standard Gross Volume.
		$report['top_forms'] = $this->get_top_forms( $range, $currency );

		return new WP_REST_Response( $report );
	}

	/**
	 * Returns information about the forms used during the current period.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Core\Report\DateRange $range The date range to use for the report.
	 * @param string                           $currency Report currency.
	 * @return array<string, array<array<string, int|string>|int|string>>
	 */
	private function get_top_forms( $range, $currency ) {
		global $wpdb;

		$livemode = simpay_is_test_mode() ? 0 : 1;
		$forms    = array();

		$forms = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT DISTINCT form_id, SUM(amount_total) as amount_total FROM {$wpdb->prefix}wpsp_transactions WHERE livemode = %d AND currency = %s AND status = 'succeeded' AND object IN ('payment_intent', 'setup_intent') AND date_created BETWEEN %s AND %s GROUP BY form_id ORDER BY amount_total DESC",
				$livemode,
				$currency,
				$range->start->format( 'Y-m-d 00:00:00' ),
				$range->end->format( 'Y-m-d 23:59:59' )
			)
		);

		if ( empty( $forms ) ) {
			return array(
				'top'       => array(),
				'remaining' => array(
					'count' => 0,
					'total' => 0,
				),
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
				'count'           => $remaining_count,
				'total'           => $remaining_total,
				'total_formatted' => simpay_format_currency(
					(int) $remaining_total,
					$currency
				),
			),
		);
	}
}
