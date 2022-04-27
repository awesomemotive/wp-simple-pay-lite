<?php
/**
 * Scheduler: WordPress cron
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\Scheduler;

use SimplePay\Core\EventManagement\EventManager;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * WpCronScheduler class.
 *
 * @link https://developer.wordpress.org/plugins/cron/scheduling-wp-cron-events/
 * @since 4.4.5
 */
class WpCronScheduler implements SchedulerInterface, SubscriberInterface {

	/**
	 * Event management.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\EventManagement\EventManager
	 */
	private $events;

	/**
	 * WpCronScheduler.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\EventManagement\EventManager $events Event management.
	 */
	public function __construct( EventManager $events ) {
		$this->events = $events;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'cron_schedules' => 'add_custom_schedule_intervals',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run( $hook, $args = array() ) {
		do_action_ref_array( $hook, $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function schedule_once( $timestamp, $hook, $args = array() ) {
		if ( false !== $this->get_next( $hook, $args ) ) {
			return;
		}

		wp_schedule_single_event( $timestamp, $hook, $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function schedule_recurring( $timestamp, $interval, $hook, $args = array() ) {
		if ( false !== $this->get_next( $hook, $args ) ) {
			return;
		}

		$recurrance = $this->get_recurrence_schedule( $interval );

		if ( false === $recurrance ) {
			return;
		}

		wp_schedule_event( $timestamp, $recurrance, $hook, $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function unschedule( $hook, $args = array() ) {
		$next = $this->get_next( $hook, $args );

		if ( false === $next ) {
			return;
		}

		wp_unschedule_event( (int) $next, $hook, $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function unschedule_all( $hook, $args = array() ) {
		wp_unschedule_hook( $hook );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_next( $hook, $args = array() ) {
		return wp_next_scheduled( $hook, $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_next( $hook, $args = array() ) {
		return ! ! $this->get_next( $hook, $args );
	}

	/**
	 * Adds custom recurrence schedules to WordPress' defaults.
	 *
	 * @since 4.4.5
	 *
	 * @param array<string, array<string, int|string>> $schedules A list of registered recurrence schedules.
	 * @return array<string, array<string, int|string>> A list of registered recurrence schedules.
	 */
	public function add_custom_schedule_intervals( $schedules ) {
		return array_merge(
			$schedules,
			$this->get_custom_schedules()
		);
	}

	/**
	 * Returns a list of custom recurrence schedules.
	 *
	 * @since 4.4.5
	 *
	 * @return array<string, array<string, int|string>> A list of custom recurrence schedules.
	 */
	private function get_custom_schedules() {
		return array(
			// 'thirtydays' => array(
			// 	'interval' => 86400 * 30,
			// 	'display'  => __( 'Once every 30 days', 'simple-pay' ),
			// ),
		);
	}

	/**
	 * Returns a registered recurrence schedule based on a given interval.
	 *
	 * @since 4.4.5
	 *
	 * @param int $interval Schedule interval.
	 * @return string|false Registered recurrence schedule if registered, or false.
	 */
	private function get_recurrence_schedule( $interval ) {
		$this->events->remove_callback(
			'cron_schedules',
			array( $this, 'add_custom_schedule_intervals' )
		);

		$schedules = array_merge(
			wp_get_schedules(),
			$this->get_custom_schedules()
		);

		$this->events->add_callback(
			'cron_schedules',
			array( $this, 'add_custom_schedule_intervals' )
		);

		foreach ( $schedules as $schedule_id => $schedule ) {
			if ( $schedule['interval'] === $interval ) {
				return $schedule_id;
			}
		}

		return false;
	}

}
