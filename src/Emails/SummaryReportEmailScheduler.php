<?php
/**
 * Emails: Summary report scheduler
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails;

use SimplePay\Core\Emails\Email\SummaryReportEmail;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Scheduler\SchedulerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SummaryReportEmailScheduler class.
 *
 * @since 4.7.3
 */
class SummaryReportEmailScheduler implements SubscriberInterface {

	/**
	 * Scheduler.
	 *
	 * @since 4.7.3
	 * @var \SimplePay\Core\Scheduler\SchedulerInterface
	 */
	private $scheduler;

	/**
	 * Summary report email.
	 *
	 * @since 4.7.3
	 * @var \SimplePay\Core\Emails\Email\SummaryReportEmail
	 */
	private $email;

	/**
	 * SummaryReportEmailSchedulerer.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Core\Scheduler\SchedulerInterface    $scheduler Scheduler.
	 * @param \SimplePay\Core\Emails\Email\SummaryReportEmail $email Email.
	 */
	public function __construct( SchedulerInterface $scheduler, SummaryReportEmail $email ) {
		$this->scheduler = $scheduler;
		$this->email     = $email;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		// Do not schedule the email if it is disabled.
		if ( ! $this->email->is_enabled() ) {
			return array();
		}

		$subscribers = array(
			'init' => 'schedule_email',
		);

		// Subscribe to all possible intervals, exposing a single hook that
		// the email subscriber can listen for.
		$intervals = array_keys( $this->email->get_intervals() );

		foreach ( $intervals as $interval ) {
			$hook = sprintf(
				'simpay_send_email_%s_%s',
				$this->email->get_id(),
				$interval
			);

			$subscribers[ $hook ] = 'send_email';
		}

		return $subscribers;
	}

	/**
	 * Schedules the summary report email.
	 *
	 * @since 4.7.3
	 *
	 * @return void
	 */
	public function schedule_email() {
		$intervals        = $this->email->get_intervals();
		$send_interval    = $this->email->get_send_interval();
		$unused_intervals = array_diff( $intervals, array( $send_interval ) );

		switch ( $send_interval ) {
			case 'monthly':
				$interval = MONTH_IN_SECONDS;
				break;
			default:
				$interval = WEEK_IN_SECONDS;
				break;
		}

		$hook = sprintf(
			'simpay_send_email_%s_%s',
			$this->email->get_id(),
			$send_interval
		);

		$has_next = $this->scheduler->has_next( $hook );

		if ( $has_next ) {
			return;
		}

		// If there isn't a scheduled event for the current interval then the
		// setting has changed. We need to cancel the existing event and schedule
		// a new one.
		foreach ( $unused_intervals as $unused_interval ) {
			$unused_hook = sprintf(
				'simpay_send_email_%s_%s',
				$this->email->get_id(),
				$unused_interval
			);

			$this->scheduler->unschedule_all( $unused_hook );
		}

		if ( function_exists( 'jddayofweek' ) ) {
			/** @var string $start_of_week_no */
			$start_of_week_no = get_option( 'start_of_week', '0' );
			$start_of_week_no = (int) $start_of_week_no;

			$start_of_week = jddayofweek( $start_of_week_no - 1, 1 );
		} else {
			$start_of_week = 'monday';
		}

		/** @var string $start_of_week */

		switch ( $send_interval ) {
			case 'monthly':
				$start = strtotime( sprintf( 'first %s of next month 9:00am', $start_of_week ) );
				break;
			default:
				$start = strtotime( sprintf( 'next %s 9:00am', $start_of_week ) );
				break;
		}

		/** @var int $start */

		$this->scheduler->schedule_recurring( $start, $interval, $hook );
	}

	/**
	 * Sends the summary report email by exposing an event that the email subscriber
	 * will listen for.
	 *
	 * @since 4.7.3
	 *
	 * @return void
	 */
	public function send_email() {
		/**
		 * Notifies that the summary report email should send.
		 *
		 * @since 4.7.3
		 */
		do_action( 'simpay_send_summary_report_email' );
	}

}
