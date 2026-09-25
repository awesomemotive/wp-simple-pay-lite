<?php
/**
 * Subscriptions: Backfiller
 *
 * Imports existing/historical subscriptions from Stripe into the local table
 * so the Subscriptions admin page is populated on sites that already have
 * subscriptions (the observer alone is forward-only). Runs incrementally on a
 * cron, one page per run, and restarts when a different Stripe account is
 * connected (#3533).
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription;

use SimplePay\Core\API\Subscriptions;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Utils\CronLock;
use SimplePay\Core\Utils\DebugLog;

/**
 * SubscriptionBackfiller class.
 *
 * @since 4.17.4
 */
class SubscriptionBackfiller implements SubscriberInterface {

	/**
	 * Cron hook that advances the backfill by one page.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	const HOOK = 'simpay_backfill_subscriptions';

	/**
	 * Number of subscriptions retrieved from Stripe per run.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	const BATCH_SIZE = 100;

	/**
	 * How long one run may hold the backfill lock, in seconds.
	 *
	 * Comfortably longer than a single page of 100 expanded subscriptions
	 * takes, and short enough that a run killed mid-flight does not lock the
	 * job out for long.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	const LOCK_TTL = 300;

	/**
	 * Longest a run backs off after consecutive failures, in seconds.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	const MAX_BACKOFF = 86400;

	/**
	 * Subscription observer.
	 *
	 * @since 4.17.4
	 * @var \SimplePay\Core\Subscription\SubscriptionObserver
	 */
	private $observer;

	/**
	 * SubscriptionBackfiller.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Core\Subscription\SubscriptionObserver $observer Subscription observer.
	 */
	public function __construct( SubscriptionObserver $observer ) {
		$this->observer = $observer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init'                            => 'schedule',
			self::HOOK                        => 'run',
			'simpay_stripe_account_connected' => 'restart',
		);
	}

	/**
	 * Schedules the recurring backfill event.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public function schedule() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::HOOK );
		}
	}

	/**
	 * Imports one page of subscriptions from Stripe for the connected account.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public function run() {
		$secret_key = simpay_get_secret_key();
		$account_id = simpay_get_account_id();

		// Nothing to import without a connected account and a usable key.
		if ( empty( $secret_key ) || false === $account_id ) {
			return;
		}

		$state = $this->get_state();

		// Already imported everything for this account and mode.
		if ( ! empty( $state['complete'] ) ) {
			return;
		}

		// Backing off after consecutive failures.
		if ( $this->is_backing_off( $state ) ) {
			return;
		}

		// One run at a time: the cursor lives in an option, so two overlapping
		// runs would import the same page twice.
		$lock = $this->get_state_key();

		if ( ! CronLock::acquire( $lock, self::LOCK_TTL ) ) {
			return;
		}

		try {
			// Re-read inside the lock: a run that finished between the check
			// above and the lock being taken has already moved the cursor.
			$this->run_batch( $this->get_state(), $secret_key );
		} finally {
			CronLock::release( $lock );
		}
	}

	/**
	 * Imports one page of subscriptions, assuming the lock is already held.
	 *
	 * @since 4.17.4
	 *
	 * @param array{cursor?: string|null, complete?: bool, failures?: int, retry_after?: int, last_error?: string} $state      Backfill state.
	 * @param string                                                                                               $secret_key Stripe secret key.
	 * @return void
	 */
	private function run_batch( $state, $secret_key ) {
		if ( ! empty( $state['complete'] ) ) {
			return;
		}

		$args = array(
			'limit'  => self::BATCH_SIZE,
			'status' => 'all',
			'expand' => array(
				'data.customer',
				'data.items.data.price',
				'data.latest_invoice',
			),
		);

		if ( ! empty( $state['cursor'] ) ) {
			$args['starting_after'] = $state['cursor'];
		}

		try {
			$result = Subscriptions\all(
				$args,
				array( 'api_key' => $secret_key )
			);
		} catch ( \Exception $e ) {
			// Could be transient (network, rate limit) or permanent (revoked
			// key, insufficient permissions). Both look identical here, so
			// record it and back off instead of retrying blindly every hour.
			$this->record_failure( $state, $e->getMessage() );

			return;
		}

		if ( ! isset( $result->data ) || ! is_array( $result->data ) ) {
			$this->record_failure( $state, 'Unexpected response shape from Stripe.' );

			return;
		}

		$last_id = null;

		foreach ( $result->data as $subscription ) {
			if ( ! $subscription instanceof \SimplePay\Vendor\Stripe\Subscription ) {
				continue;
			}

			$last_id = $subscription->id;

			// Only import subscriptions created by WP Simple Pay; the account
			// may hold unrelated subscriptions from other integrations.
			if ( ! isset( $subscription->metadata->simpay_form_id ) ) {
				continue;
			}

			$this->observer->import_from_stripe( $subscription );
		}

		$complete = empty( $result->has_more );

		// A successful page clears any recorded failure, so a run that
		// recovers stops backing off.
		$this->set_state(
			array(
				'cursor'      => $complete ? null : $last_id,
				'complete'    => $complete,
				'failures'    => 0,
				'retry_after' => 0,
				'last_error'  => '',
			)
		);
	}

	/**
	 * Determines whether the backfill is waiting out a backoff period.
	 *
	 * @since 4.17.4
	 *
	 * @param array{retry_after?: int} $state Backfill state.
	 * @return bool
	 */
	private function is_backing_off( $state ) {
		if ( empty( $state['retry_after'] ) ) {
			return false;
		}

		return time() < (int) $state['retry_after'];
	}

	/**
	 * Records a failed run and schedules the next attempt.
	 *
	 * The failure is kept next to the cursor rather than only logged, so the
	 * reason a site's Subscriptions page never filled in is answerable from
	 * the database after the fact. The delay doubles per consecutive failure
	 * so a permanently broken account is retried daily instead of hourly,
	 * while a transient blip still recovers on the following run.
	 *
	 * @since 4.17.4
	 *
	 * @param array{cursor?: string|null, failures?: int} $state   Backfill state.
	 * @param string                                      $message Failure message.
	 * @return void
	 */
	private function record_failure( $state, $message ) {
		$failures = isset( $state['failures'] ) ? (int) $state['failures'] + 1 : 1;
		$delay    = min( self::MAX_BACKOFF, 3600 * ( 2 ** ( $failures - 1 ) ) );

		$this->set_state(
			array(
				'cursor'      => isset( $state['cursor'] ) ? $state['cursor'] : null,
				'complete'    => false,
				'failures'    => $failures,
				'retry_after' => time() + $delay,
				'last_error'  => (string) $message,
			)
		);

		DebugLog::log(
			sprintf(
				'Subscription backfill failed (attempt %1$d, retrying in %2$d seconds): %3$s',
				$failures,
				$delay,
				$message
			)
		);
	}

	/**
	 * Restarts the backfill when a Stripe account is connected.
	 *
	 * @since 4.17.4
	 *
	 * @param mixed $account_data Connected account data. Unused.
	 * @return void
	 */
	public function restart( $account_data = null ) {
		// Clears any recorded failure too: a freshly connected account is a
		// new set of credentials, so a previous account's auth failure must
		// not keep the new one backing off.
		$this->set_state(
			array(
				'cursor'      => null,
				'complete'    => false,
				'failures'    => 0,
				'retry_after' => 0,
				'last_error'  => '',
			)
		);
	}

	/**
	 * Returns the option key holding backfill state for the current account
	 * and mode.
	 *
	 * @since 4.17.4
	 *
	 * @return string
	 */
	private function get_state_key() {
		$account_id = (string) simpay_get_account_id();
		$mode       = simpay_is_test_mode() ? 'test' : 'live';

		return 'simpay_subscription_backfill_' . $account_id . '_' . $mode;
	}

	/**
	 * Returns the backfill state for the current account and mode.
	 *
	 * @since 4.17.4
	 *
	 * @return array{cursor?: string|null, complete?: bool, failures?: int, retry_after?: int, last_error?: string}
	 */
	private function get_state() {
		$state = get_option( $this->get_state_key(), array() );

		return is_array( $state ) ? $state : array();
	}

	/**
	 * Persists the backfill state for the current account and mode.
	 *
	 * @since 4.17.4
	 *
	 * @param array{cursor: string|null, complete: bool, failures?: int, retry_after?: int, last_error?: string} $state Backfill state.
	 * @return void
	 */
	private function set_state( $state ) {
		update_option( $this->get_state_key(), $state, false );
	}
}
