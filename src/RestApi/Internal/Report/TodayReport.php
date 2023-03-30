<?php
/**
 * Report: Today Report
 *
 * Technically a Period Over Period to find deltas, but the range is forced.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\RestApi\Internal\Report;

use DateTimeImmutable;
use DateInterval;
use DateTimeZone;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Report;
use WP_REST_Response;
use WP_REST_Server;

/**
 * TodayReport class.
 *
 * @since 4.6.7
 */
class TodayReport implements SubscriberInterface {

	use Report\ReportTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init'          => 'register_user_preferences',
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers user meta to allow saving the selected filter values.
	 *
	 * These are actually shared by a few different reports, but we're going to
	 * register them here for now... easiest.
	 *
	 * @since 4.7.3
	 *
	 * @return void
	 */
	public function register_user_preferences() {
		register_meta(
			'user',
			'simpay_activity_reports_range',
			SchemaUtils::get_date_range_user_preferences_args() // @phpstan-ignore-line
		);

		register_meta(
			'user',
			'simpay_activity_reports_currency',
			SchemaUtils::get_currency_user_preferences_args() // @phpstan-ignore-line
		);
	}

	/**
	 * Registers the REST API route for `GET /wpsp/__internal__/report/today`.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			'wpsp/__internal__',
			'report/today',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_report' ),
					'permission_callback' => array( $this, 'can_view_report' ),
					'args'                => array(
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
		$today = new DateTimeImmutable( 'now' );
		$range = new Report\DateRange(
			'today',
			$today->format( 'Y-m-d 00:00:00' ),
			$today->format( 'Y-m-d 23:59:59' )
		);

		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		$report    = new Report\ActivityOverviewReport( $range, $currency );
		$stats     = $report->get_stats();
		$top_forms = $report->get_top_forms();

		return new WP_REST_Response(
			array(
				'currency'  => array(
					'code'               => $currency,
					'symbol'             => simpay_get_currency_symbol(
						$currency
					),
					'position'           => simpay_get_currency_position(),
					'thousand_separator' => simpay_get_thousand_separator(),
					'decimal_separator'  => simpay_get_decimal_separator(),
				),
				'stats'     => $stats,
				'top_forms' => $top_forms,
				'tip'       => count( $top_forms ) <= 2
					? $report->get_tip()
					: null,
			)
		);
	}

}
