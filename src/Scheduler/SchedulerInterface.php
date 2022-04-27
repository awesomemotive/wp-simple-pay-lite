<?php
/**
 * Scheduler: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\Scheduler;

/**
 * SchedulerInterface interface.
 *
 * @since 4.4.5
 */
interface SchedulerInterface {

	/**
	 * Schedules an action to run immediately.
	 *
	 * @since 4.4.5
	 *
	 * @param string       $hook Hook to call immediately.
	 * @param array<mixed> $args Optional. Arguments to pass to the hook.
	 * @return void
	 */
	public function run( $hook, $args = array() );

	/**
	 * Schedules an action to run once at some point in the future.
	 *
	 * @since 4.4.5
	 *
	 * @param int          $timestamp Timestamp of when the callback should run.
	 * @param string       $hook Hook to call once on the set timestamp.
	 * @param array<mixed> $args Optional. Arguments to pass to the hook.
	 * @return void
	 */
	public function schedule_once( $timestamp, $hook, $args = array() );

	/**
	 * Schedules an action to run on a recurring schedule.
	 *
	 * @since 4.4.5
	 *
	 * @param int          $timestamp Timestamp of when the callback should run.
	 * @param int          $interval The number of seconds to wait between runs.
	 * @param string       $hook Hook to call once on the set timestamp.
	 * @param array<mixed> $args Optional. Arguments to pass to the hook.
	 * @return void
	 */
	public function schedule_recurring( $timestamp, $interval, $hook, $args = array() );

	/**
	 * Unschedules the next scheduled occurrance of an action run.
	 *
	 * @since 4.4.5
	 *
	 * @param string       $hook Hook to unschedule.
	 * @param array<mixed> $args Optional. Arguments to pass to the hook.
	 * @return void
	 */
	public function unschedule( $hook, $args = array() );

	/**
	 * Unschedules all scheduled occurrance of an action run.
	 *
	 * @since 4.4.5
	 *
	 * @param string       $hook Action to unschedule.
	 * @param array<mixed> $args Optional. Arguments to pass to the hook.
	 * @return void
	 */
	public function unschedule_all( $hook, $args = array() );

	/**
	 * Returns the timestamp for the actions next run.
	 *
	 * @since 4.4.5
	 *
	 * @param string       $hook Action to unschedule.
	 * @param array<mixed> $args Optional. Arguments to pass to the hook.
	 * @return int|bool Timestamp of next scheduled action, or false if not scheduled.
	 */
	public function get_next( $hook, $args = array() );

	/**
	 * Determines if there is a previously scheduled action.
	 *
	 * @since 4.4.5
	 *
	 * @param string       $hook Action to unschedule.
	 * @param array<mixed> $args Optional. Arguments to pass to the hook.
	 * @return bool If a hook has a previously scheduled action.
	 */
	public function has_next( $hook, $args = array() );

}
