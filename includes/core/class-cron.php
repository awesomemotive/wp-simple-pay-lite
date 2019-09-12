<?php
/**
 * Cron.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cron.
 */
class Cron {

	/**
	 * Hook in to WordPress.
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
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'stripe' ),
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
		$this->weekly_events();
	}

	/**
	 * Schedule a one time event a day after install.
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
	 * Schedule weekly events.
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
