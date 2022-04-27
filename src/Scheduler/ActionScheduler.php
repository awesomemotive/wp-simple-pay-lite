<?php
/**
 * Scheduler: Action Scheduler
 *
 * https://actionscheduler.org/
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\Scheduler;

/**
 * ActionScheduler class.
 *
 * @since 4.4.5
 */
class ActionScheduler implements SchedulerInterface {

	/**
	 * Group name.
	 *
	 * @since 4.4.5
	 * @var string
	 */
	const GROUP = 'simpay';

	/**
	 * {@inheritdoc}
	 */
	public function run( $hook, $args = array() ) {
		if ( false !== $this->has_next( $hook, $args ) ) {
			return;
		}

		as_enqueue_async_action( $hook, $args, self::GROUP );
	}

	/**
	 * {@inheritdoc}
	 */
	public function schedule_once( $timestamp, $hook, $args = array() ) {
		if ( false !== $this->has_next( $hook, $args ) ) {
			return;
		}

		as_schedule_single_action( $timestamp, $hook, $args, self::GROUP );
	}

	/**
	 * {@inheritdoc}
	 */
	public function schedule_recurring( $timestamp, $interval, $hook, $args = array() ) {
		if ( false !== $this->has_next( $hook, $args ) ) {
			return;
		}

		as_schedule_recurring_action(
			$timestamp,
			$interval,
			$hook,
			$args,
			self::GROUP
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function unschedule( $hook, $args = array() ) {
		as_unschedule_action( $hook, $args, self::GROUP );
	}

	/**
	 * {@inheritdoc}
	 */
	public function unschedule_all( $hook, $args = array() ) {
		as_unschedule_all_actions( $hook, $args, self::GROUP );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_next( $hook, $args = array() ) {
		return as_next_scheduled_action( $hook, $args, self::GROUP );
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_next( $hook, $args = array() ) {
		return as_has_scheduled_action( $hook, $args, self::GROUP );
	}

}
