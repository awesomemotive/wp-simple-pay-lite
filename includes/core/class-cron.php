<?php
/**
 * Cron
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron class.
 */
class Cron {

	/**
	 * Hooks in to WordPress.
	 *
	 * @since 3.6.0
	 */
	public function init() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
	}

	/**
	 * Registers new cron schedules.
	 *
	 * @since 3.6.0
	 *
	 * @param array $schedules Cron schedules.
	 * @return array
	 */
	public function add_schedules( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once a Week', 'stripe' ),
		);

		$schedules['twenty_three_hours'] = array(
			'interval' => ( DAY_IN_SECONDS - HOUR_IN_SECONDS ),
			'display'  => __( 'Every 23 Hours', 'stripe' ),
		);

		return $schedules;
	}

	/**
	 * Schedules events.
	 *
	 * @since 3.6.0
	 */
	public function schedule_events() {
		$this->one_time_events();
		$this->twenty_three_hour_events();
		$this->weekly_events();
	}

	/**
	 * Schedules a one time event a day after install.
	 *
	 * @since 3.6.0
	 */
	private function one_time_events() {
		if ( wp_next_scheduled( 'simpay_day_after_install_scheduled_events' ) ) {
			return;
		}

		wp_schedule_single_event( time() + DAY_IN_SECONDS, 'simpay_day_after_install_scheduled_events' );
	}

	/**
	 * Schedules 23 hour events.
	 *
	 * @since 3.7.0
	 */
	private function twenty_three_hour_events() {
		if ( wp_next_scheduled( 'simpay_twenty_three_hours_scheduled_events' ) ) {
			return;
		}

		wp_schedule_event( time(), 'twenty_three_hours', 'simpay_twenty_three_hours_scheduled_events' );
	}

	/**
	 * Schedules weekly events.
	 *
	 * @since 3.6.0
	 */
	private function weekly_events() {
		if ( wp_next_scheduled( 'simpay_weekly_scheduled_events' ) ) {
			return;
		}

		wp_schedule_event( time(), 'weekly', 'simpay_weekly_scheduled_events' );
	}

}
