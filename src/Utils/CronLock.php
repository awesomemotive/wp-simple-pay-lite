<?php
/**
 * Utils: Cron lock
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Utils;

/**
 * CronLock class.
 *
 * Short-lived mutual exclusion for the cron callbacks that walk a cursor kept
 * in an option. Two overlapping runs read the same cursor, so both process --
 * and both pay Stripe for -- the same batch, and the second one's write moves
 * the cursor no further than the first's.
 *
 * WP-Cron makes overlap ordinary rather than exotic: it is spawned from front
 * end requests, so two visitors arriving together, or a loopback request that
 * races an external `wp-cron.php` hit, can start the same hook twice within
 * the same second.
 *
 * This is a best-effort lock, not a distributed mutex. `get_transient()` and
 * `set_transient()` are two operations with a gap between them, so two callers
 * landing inside that gap can both acquire. It removes the common case (runs
 * seconds apart) rather than guaranteeing exclusion, and every caller stays
 * correct without it -- reconciliation and backfilling are both idempotent, so
 * the cost of a lost race is duplicate API calls, not bad data. The TTL is the
 * safety net: a run that dies before releasing cannot hold the lock longer
 * than its lease.
 *
 * @since 4.17.4
 */
class CronLock {

	/**
	 * Prefix for every lock's transient name.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	const PREFIX = 'simpay_cron_lock_';

	/**
	 * Attempts to acquire a named lock.
	 *
	 * @since 4.17.4
	 *
	 * @param string $key Lock identifier, unique to the job being guarded.
	 * @param int    $ttl How long the lease is held, in seconds.
	 * @return bool True when the lock was acquired and the caller may proceed.
	 */
	public static function acquire( $key, $ttl ) {
		$name = self::PREFIX . $key;

		if ( false !== get_transient( $name ) ) {
			return false;
		}

		set_transient( $name, time(), max( 1, (int) $ttl ) );

		return true;
	}

	/**
	 * Releases a named lock.
	 *
	 * Callers release in a `finally` block so a thrown exception cannot leave
	 * the job locked out until the lease expires.
	 *
	 * @since 4.17.4
	 *
	 * @param string $key Lock identifier.
	 * @return void
	 */
	public static function release( $key ) {
		delete_transient( self::PREFIX . $key );
	}
}
