<?php
/**
 * Transactions: Reconciler
 *
 * Reconciles stale local Checkout Session placeholder records against Stripe.
 *
 * A Checkout Session is first stored as an `object = 'checkout_session'`,
 * `status = 'open'` placeholder and is only rewritten to the resulting
 * PaymentIntent/SetupIntent once `checkout.session.completed` is processed --
 * via webhook in Pro, or when the receipt is viewed in Lite. When neither
 * happens (a completed payment whose buyer never returned, or a missed
 * webhook) the placeholder lingers and the payment never appears. This
 * scheduled job asks Stripe for the current state of those placeholders and
 * finishes the ones that actually completed. It also reconciles two other
 * kinds of webhook drift: incomplete PaymentIntents that later settled or
 * canceled, and refunds issued outside the plugin (e.g. from the Stripe
 * Dashboard) on recently succeeded payments (#3533).
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Transaction;

use Exception;
use SimplePay\Core\API\CheckoutSessions;
use SimplePay\Core\API\Disputes;
use SimplePay\Core\API\PaymentIntents;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Utils\AccountScope;
use SimplePay\Core\Utils\CronLock;
use SimplePay\Core\Utils\DebugLog;
use SimplePay\Vendor\Stripe\PaymentIntent;
use SimplePay\Vendor\Stripe\Subscription;

/**
 * TransactionReconciler class.
 *
 * @since 4.17.4
 */
class TransactionReconciler implements SubscriberInterface {

	/**
	 * Cron hook that runs the reconciliation.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	const CRON_HOOK = 'simpay_reconcile_stale_transactions';

	/**
	 * How long one run may hold the reconciliation lock, in seconds.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	const LOCK_TTL = 300;

	/**
	 * Consecutive per-row failures before a row is rested rather than retried.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	const MAX_ROW_ATTEMPTS = 3;

	/**
	 * Most rows a pass remembers failures for.
	 *
	 * Bounds the option: without a cap, a site whose key lost access to a
	 * large back catalogue would grow one entry per row forever.
	 *
	 * @since 4.17.4
	 * @var int
	 */
	const MAX_TRACKED_FAILURES = 200;

	/**
	 * Dispute statuses that leave the payment still disputed.
	 *
	 * The open states plus `lost`; `won`, `warning_closed` and `prevented`
	 * resolve in our favour and are deliberately absent.
	 *
	 * @since 4.17.4
	 * @var string[]
	 */
	const UNRESOLVED_DISPUTE_STATUSES = array(
		'warning_needs_response',
		'warning_under_review',
		'needs_response',
		'under_review',
		'lost',
	);

	/**
	 * Transaction repository.
	 *
	 * @since 4.17.4
	 * @var \SimplePay\Core\Transaction\TransactionRepository
	 */
	private $transactions;

	/**
	 * Transaction observer.
	 *
	 * @since 4.17.4
	 * @var \SimplePay\Core\Transaction\TransactionObserver
	 */
	private $observer;

	/**
	 * TransactionReconciler.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Core\Transaction\TransactionRepository $transactions Transaction repository.
	 * @param \SimplePay\Core\Transaction\TransactionObserver   $observer Transaction observer.
	 */
	public function __construct(
		TransactionRepository $transactions,
		TransactionObserver $observer
	) {
		$this->transactions = $transactions;
		$this->observer     = $observer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init'          => 'schedule',
			self::CRON_HOOK => 'reconcile',
		);
	}

	/**
	 * Ensures the recurring reconciliation event is scheduled.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public function schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::CRON_HOOK );
		}
	}

	/**
	 * Reconciles stale Checkout Session placeholders against Stripe.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public function reconcile() {
		// Nothing to do without Stripe credentials or a connected account.
		if ( '' === simpay_get_secret_key() || false === simpay_get_account_id() ) {
			return;
		}

		// One run at a time: each pass's cursor lives in an option, so two
		// overlapping runs read the same cursor and re-retrieve the same batch
		// from Stripe.
		if ( ! CronLock::acquire( self::CRON_HOOK, self::LOCK_TTL ) ) {
			return;
		}

		try {
			$this->reconcile_stale_sessions();
			$this->reconcile_incomplete_payment_intents();
			$this->reconcile_refunds();
		} finally {
			CronLock::release( self::CRON_HOOK );
		}
	}

	/**
	 * Finishes stale Checkout Session placeholders that completed in Stripe.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	private function reconcile_stale_sessions() {
		$limit = $this->get_limit();
		$rows  = $this->get_stale_rows( $limit );

		foreach ( $rows as $row ) {
			// Rested after repeated failures: skipped before the API call, so
			// a row Stripe can never return stops costing a request on every
			// pass.
			if ( $this->is_resting( 'stale_sessions', $row ) ) {
				continue;
			}

			try {
				$this->reconcile_row( $row );
				$this->clear_row_failure( 'stale_sessions', $row );
			} catch ( Exception $e ) {
				// A single row that cannot be retrieved (deleted in Stripe,
				// transient API error) must not stop the batch. The cursor
				// advances past it either way, so a permanently unretrievable
				// row cannot block the rows behind it.
				$this->record_row_failure( 'stale_sessions', $row, $e->getMessage() );

				continue;
			}
		}

		$this->advance_cursor( 'stale_sessions', $rows, $limit );
	}

	/**
	 * Retrieves the stale Checkout Session placeholder rows for the current mode.
	 *
	 * Scoped to the connected Stripe account: a placeholder written under a
	 * previous account cannot be retrieved with the current account's key, so
	 * including it would spend the batch on rows that can only ever fail.
	 * Unstamped legacy rows are still included, matching how the list tables
	 * treat them.
	 *
	 * @since 4.17.4
	 *
	 * @param int $limit Maximum number of rows to return.
	 * @return array<int, \stdClass>
	 */
	private function get_stale_rows( $limit ) {
		global $wpdb;

		$livemode = simpay_is_test_mode() ? 0 : 1;

		// Assembled from prepared fragments rather than one call so the account
		// predicate can come from AccountScope, like every other
		// account-scoped read (#3533), instead of being restated here.
		$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Only the table name is interpolated; all values use placeholders.
			"SELECT * FROM {$wpdb->prefix}wpsp_transactions
			WHERE object = 'checkout_session'
			AND status = 'open'
			AND livemode = %d
			AND id > %d",
			$livemode,
			$this->get_cursor( 'stale_sessions' )
		);

		$query .= AccountScope::get_where_fragment();
		$query .= $wpdb->prepare( ' ORDER BY id ASC LIMIT %d', $limit );

		/** @var array<int, \stdClass> $rows */
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Assembled above from $wpdb->prepare() fragments.
		$rows = $wpdb->get_results( $query );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Returns the option name holding one pass's cursor.
	 *
	 * Per pass, account and mode: each pass walks a different set of rows, and
	 * the rows an account and mode can see differ.
	 *
	 * @since 4.17.4
	 *
	 * @param string $pass Pass identifier.
	 * @return string
	 */
	private function get_cursor_key( $pass ) {
		$account_id = (string) simpay_get_account_id();
		$mode       = simpay_is_test_mode() ? 'test' : 'live';

		return 'simpay_reconcile_cursor_' . $pass . '_' . $account_id . '_' . $mode;
	}

	/**
	 * Returns the ID a pass's next batch resumes after.
	 *
	 * @since 4.17.4
	 *
	 * @param string $pass Pass identifier.
	 * @return int
	 */
	private function get_cursor( $pass ) {
		$cursor = get_option( $this->get_cursor_key( $pass ), 0 );

		return is_numeric( $cursor ) ? (int) $cursor : 0;
	}

	/**
	 * Moves a pass's cursor past the rows it just processed.
	 *
	 * Every pass selects from a set larger than one batch, so ordering by `id`
	 * alone would re-read the same end of that set every run: rows beyond the
	 * first batch would never be examined, and a row that can never be
	 * resolved would starve the rows behind it. Walking a cursor forward and
	 * clearing it on a short batch means each pass covers its whole set over
	 * successive runs and then starts again.
	 *
	 * @since 4.17.4
	 *
	 * @param string                $pass  Pass identifier.
	 * @param array<int, \stdClass> $rows  Rows processed this run.
	 * @param int                   $limit Batch size used for the query.
	 * @return void
	 */
	private function advance_cursor( $pass, $rows, $limit ) {
		$last = empty( $rows ) ? null : $rows[ count( $rows ) - 1 ];

		// A short batch is the end of the set: start over next run so rows
		// that have since become eligible -- and any row that failed this time
		// around -- are picked up again.
		if ( null === $last || count( $rows ) < $limit ) {
			delete_option( $this->get_cursor_key( $pass ) );

			return;
		}

		update_option( $this->get_cursor_key( $pass ), (int) $last->id, false );
	}

	/**
	 * Returns the option name holding one pass's per-row failure counts.
	 *
	 * Keyed like the cursor -- per pass, account and mode -- because the rows
	 * a pass can retrieve differ along exactly those lines.
	 *
	 * @since 4.17.4
	 *
	 * @param string $pass Pass identifier.
	 * @return string
	 */
	private function get_failures_key( $pass ) {
		$account_id = (string) simpay_get_account_id();
		$mode       = simpay_is_test_mode() ? 'test' : 'live';

		return 'simpay_reconcile_failures_' . $pass . '_' . $account_id . '_' . $mode;
	}

	/**
	 * Returns a pass's per-row failure counts.
	 *
	 * @since 4.17.4
	 *
	 * @param string $pass Pass identifier.
	 * @return array<int, array{attempts: int, retry_after: int, last_seen?: int}>
	 */
	private function get_row_failures( $pass ) {
		$failures = get_option( $this->get_failures_key( $pass ), array() );

		return is_array( $failures ) ? $failures : array();
	}

	/**
	 * Determines whether a row is resting after repeated failures.
	 *
	 * @since 4.17.4
	 *
	 * @param string    $pass Pass identifier.
	 * @param \stdClass $row  Transaction row.
	 * @return bool
	 */
	private function is_resting( $pass, $row ) {
		$failures = $this->get_row_failures( $pass );
		$id       = (int) $row->id;

		if ( ! isset( $failures[ $id ]['retry_after'] ) ) {
			return false;
		}

		return time() < (int) $failures[ $id ]['retry_after'];
	}

	/**
	 * Records a failed row and rests it once it has failed enough times.
	 *
	 * The cursor already stops one bad row from starving the rows behind it,
	 * but it does not stop the row itself being re-retrieved on every pass:
	 * a Checkout Session deleted in Stripe, or a PaymentIntent belonging to
	 * revoked credentials, can never resolve, and each wrap-around spends
	 * another API call discovering that again.
	 *
	 * So the first few failures are free -- the common case is a transient
	 * API error that resolves by the next pass -- and after that the row is
	 * rested for a day per extra failure, up to a week. It is rested rather
	 * than retired permanently: access can be restored, and a row that then
	 * resolves clears its own record.
	 *
	 * @since 4.17.4
	 *
	 * @param string    $pass    Pass identifier.
	 * @param \stdClass $row     Transaction row.
	 * @param string    $message Failure message.
	 * @return void
	 */
	private function record_row_failure( $pass, $row, $message ) {
		$failures = $this->get_row_failures( $pass );
		$id       = (int) $row->id;
		$attempts = isset( $failures[ $id ]['attempts'] )
			? (int) $failures[ $id ]['attempts'] + 1
			: 1;

		$rest = 0;

		if ( $attempts >= self::MAX_ROW_ATTEMPTS ) {
			$rest = min(
				7 * DAY_IN_SECONDS,
				DAY_IN_SECONDS * ( $attempts - self::MAX_ROW_ATTEMPTS + 1 )
			);

			DebugLog::log(
				sprintf(
					'Transaction reconciliation: %1$s row #%2$d (%3$s) failed %4$d times, resting for %5$d seconds: %6$s',
					$pass,
					$id,
					isset( $row->_object_id ) ? (string) $row->_object_id : '',
					$attempts,
					$rest,
					$message
				)
			);
		}

		$failures[ $id ] = array(
			'attempts'    => $attempts,
			'retry_after' => 0 === $rest ? 0 : time() + $rest,
			'last_seen'   => time(),
		);

		// Drop the least recently seen rows when the cap is reached. Trimming
		// by `last_seen` rather than by row ID matters: a pass walks IDs
		// ascending from its cursor, so the rows being worked on now are the
		// low IDs, and trimming by ID would throw away the entry just written
		// while keeping stale high-ID ones.
		if ( count( $failures ) > self::MAX_TRACKED_FAILURES ) {
			uasort(
				$failures,
				function ( $a, $b ) {
					$a_seen = isset( $a['last_seen'] ) ? (int) $a['last_seen'] : 0;
					$b_seen = isset( $b['last_seen'] ) ? (int) $b['last_seen'] : 0;

					return $b_seen <=> $a_seen;
				}
			);

			$failures = array_slice( $failures, 0, self::MAX_TRACKED_FAILURES, true );
		}

		update_option( $this->get_failures_key( $pass ), $failures, false );
	}

	/**
	 * Clears a row's failure record after it reconciles successfully.
	 *
	 * @since 4.17.4
	 *
	 * @param string    $pass Pass identifier.
	 * @param \stdClass $row  Transaction row.
	 * @return void
	 */
	private function clear_row_failure( $pass, $row ) {
		$failures = $this->get_row_failures( $pass );
		$id       = (int) $row->id;

		if ( ! isset( $failures[ $id ] ) ) {
			return;
		}

		unset( $failures[ $id ] );

		if ( empty( $failures ) ) {
			delete_option( $this->get_failures_key( $pass ) );

			return;
		}

		update_option( $this->get_failures_key( $pass ), $failures, false );
	}

	/**
	 * Reconciles a single placeholder row against its Stripe Checkout Session.
	 *
	 * @since 4.17.4
	 *
	 * @param \stdClass $row Transaction row.
	 * @return void
	 */
	private function reconcile_row( $row ) {
		if ( empty( $row->_object_id ) ) {
			return;
		}

		$session = CheckoutSessions\retrieve(
			array(
				'id'     => $row->_object_id,
				'expand' => array(
					'payment_intent.payment_method',
					'customer',
					'subscription.latest_invoice.payment_intent',
					'subscription.default_payment_method',
				),
			),
			array(
				'api_key' => simpay_get_secret_key(),
			)
		);

		$status = isset( $session->status ) ? $session->status : '';

		// Completed since we last saw it: rewrite the placeholder to the real
		// PaymentIntent/SetupIntent and mark it succeeded.
		if ( 'complete' === $status ) {
			// A completed session always has a customer; without one there is
			// nothing to write, so leave the placeholder for a later run.
			if ( ! isset( $session->customer ) || ! is_object( $session->customer ) ) {
				return;
			}

			$transaction = new Transaction( (array) $row );

			// Expanded fields are objects at runtime, but the Stripe stubs type
			// them as object|string|null; narrow to the concrete objects the
			// observer expects.
			$payment_intent = isset( $session->payment_intent ) && $session->payment_intent instanceof PaymentIntent
				? $session->payment_intent
				: null;

			$subscription = isset( $session->subscription ) && $session->subscription instanceof Subscription
				? $session->subscription
				: null;

			$this->observer->rewrite_from_checkout_session(
				$transaction,
				$session,
				$session->customer,
				$payment_intent,
				$subscription
			);

			return;
		}

		// The session can no longer be completed; record it as canceled so it
		// stops being reconciled.
		if ( 'expired' === $status ) {
			$this->transactions->update(
				(int) $row->id,
				array( 'status' => 'canceled' )
			);
		}
	}

	/**
	 * Reconciles stale incomplete PaymentIntents against Stripe.
	 *
	 * A PaymentIntent can settle (or be canceled) without the plugin receiving
	 * the matching webhook, leaving the local row stuck in an incomplete state.
	 * This asks Stripe for the current status of older incomplete rows and
	 * updates the ones that have moved on.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	private function reconcile_incomplete_payment_intents() {
		global $wpdb;

		$livemode = simpay_is_test_mode() ? 0 : 1;

		/**
		 * Filters how old (in seconds) an incomplete PaymentIntent must be
		 * before it is reconciled, so in-progress payments are given time to
		 * settle normally first.
		 *
		 * @since 4.17.4
		 *
		 * @param int $min_age Age in seconds. Default 1 day.
		 */
		$min_age = (int) apply_filters( 'simpay_reconcile_incomplete_min_age', DAY_IN_SECONDS );
		$cutoff  = gmdate( 'Y-m-d H:i:s', time() - $min_age );

		$limit = $this->get_limit();

		// Assembled from prepared fragments rather than one call so the account
		// predicate can come from AccountScope, like every other
		// account-scoped read (#3533), instead of being restated here.
		$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Only the table name is interpolated; all values use placeholders.
			"SELECT * FROM {$wpdb->prefix}wpsp_transactions
			WHERE object = 'payment_intent'
			AND status IN ( 'requires_payment_method', 'requires_confirmation', 'requires_action', 'processing' )
			AND livemode = %d
			AND date_created < %s
			AND id > %d",
			$livemode,
			$cutoff,
			$this->get_cursor( 'incomplete' )
		);

		$query .= AccountScope::get_where_fragment();
		$query .= $wpdb->prepare( ' ORDER BY id ASC LIMIT %d', $limit );

		/** @var array<int, \stdClass> $rows */
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Assembled above from $wpdb->prepare() fragments.
		$rows = $wpdb->get_results( $query );

		if ( ! is_array( $rows ) ) {
			return;
		}

		foreach ( $rows as $row ) {
			if ( $this->is_resting( 'incomplete', $row ) ) {
				continue;
			}

			try {
				$this->reconcile_incomplete_row( $row );
				$this->clear_row_failure( 'incomplete', $row );
			} catch ( Exception $e ) {
				$this->record_row_failure( 'incomplete', $row, $e->getMessage() );

				continue;
			}
		}

		$this->advance_cursor( 'incomplete', $rows, $limit );
	}

	/**
	 * Reconciles a single incomplete PaymentIntent row against Stripe.
	 *
	 * @since 4.17.4
	 *
	 * @param \stdClass $row Transaction row.
	 * @return void
	 */
	private function reconcile_incomplete_row( $row ) {
		if ( empty( $row->_object_id ) ) {
			return;
		}

		$payment_intent = PaymentIntents\retrieve(
			array(
				'id'     => $row->_object_id,
				'expand' => array( 'latest_charge' ),
			),
			array( 'api_key' => simpay_get_secret_key() )
		);

		$status = isset( $payment_intent->status ) ? (string) $payment_intent->status : '';

		if ( '' === $status ) {
			return;
		}

		// A refund can land before we ever see the success webhook; record it.
		$refunded = $this->get_charge_amount_refunded( $payment_intent );

		if ( $refunded > 0 ) {
			$this->transactions->update(
				(int) $row->id,
				array(
					'status'          => 'refunded',
					'amount_refunded' => $refunded,
				)
			);

			return;
		}

		// Otherwise store Stripe's current status verbatim (it maps 1:1 to the
		// values the observer writes for a PaymentIntent).
		if ( $status !== $row->status ) {
			$this->transactions->update(
				(int) $row->id,
				array( 'status' => $status )
			);
		}
	}

	/**
	 * Reconciles refunds issued outside the plugin (e.g. from the Stripe
	 * Dashboard) for recently succeeded payments.
	 *
	 * The window holds far more rows than one batch on any busy site, so the
	 * pass walks it with a cursor rather than repeatedly reading one end of
	 * it. A refund is usually issued days or weeks after the charge, which is
	 * exactly the part of the window a fixed `ORDER BY id DESC LIMIT` never
	 * reached.
	 *
	 * Every row whose refund or dispute state can still change is in scope,
	 * not just `succeeded` ones:
	 *
	 * - a **partial** refund is stored as `status = 'refunded'` with
	 *   `amount_refunded < amount_total`, so restricting the pass to
	 *   `succeeded` dropped a payment out of reconciliation the moment it was
	 *   partly refunded -- a later Dashboard refund topping it up to full was
	 *   then never recorded, and the list kept showing the stale partial;
	 * - a `disputed` row needs re-checking too, so that a dispute resolved in
	 *   our favour is reflected when the `charge.dispute.closed` webhook is
	 *   the one that goes missing.
	 *
	 * A fully refunded row is excluded: nothing further can be refunded, so
	 * re-reading it would only spend API calls.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	private function reconcile_refunds() {
		global $wpdb;

		$livemode = simpay_is_test_mode() ? 0 : 1;

		/**
		 * Filters how far back (in seconds) succeeded payments are checked for
		 * refunds that arrived without a webhook.
		 *
		 * @since 4.17.4
		 *
		 * @param int $window Window in seconds. Default 30 days.
		 */
		$window = (int) apply_filters( 'simpay_reconcile_refund_window', 30 * DAY_IN_SECONDS );
		$since  = gmdate( 'Y-m-d H:i:s', time() - $window );

		$limit = $this->get_limit();

		// Assembled from prepared fragments rather than one call so the account
		// predicate can come from AccountScope, like every other
		// account-scoped read (#3533), instead of being restated here.
		$query = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Only the table name is interpolated; all values use placeholders.
			"SELECT * FROM {$wpdb->prefix}wpsp_transactions
			WHERE object = 'payment_intent'
			AND (
				status = 'succeeded'
				OR status = 'disputed'
				OR ( status = 'refunded' AND amount_refunded < amount_total )
			)
			AND livemode = %d
			AND date_created >= %s
			AND id > %d",
			$livemode,
			$since,
			$this->get_cursor( 'refunds' )
		);

		$query .= AccountScope::get_where_fragment();
		$query .= $wpdb->prepare( ' ORDER BY id ASC LIMIT %d', $limit );

		/** @var array<int, \stdClass> $rows */
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Assembled above from $wpdb->prepare() fragments.
		$rows = $wpdb->get_results( $query );

		if ( ! is_array( $rows ) ) {
			return;
		}

		foreach ( $rows as $row ) {
			if ( $this->is_resting( 'refunds', $row ) ) {
				continue;
			}

			try {
				$this->reconcile_refund_row( $row );
				$this->clear_row_failure( 'refunds', $row );
			} catch ( Exception $e ) {
				$this->record_row_failure( 'refunds', $row, $e->getMessage() );

				continue;
			}
		}

		$this->advance_cursor( 'refunds', $rows, $limit );
	}

	/**
	 * Reconciles a single succeeded row's refunded amount against Stripe.
	 *
	 * @since 4.17.4
	 *
	 * @param \stdClass $row Transaction row.
	 * @return void
	 */
	private function reconcile_refund_row( $row ) {
		if ( empty( $row->_object_id ) ) {
			return;
		}

		$payment_intent = PaymentIntents\retrieve(
			array(
				'id'     => $row->_object_id,
				'expand' => array( 'latest_charge' ),
			),
			array( 'api_key' => simpay_get_secret_key() )
		);

		$charge = isset( $payment_intent->latest_charge ) && is_object( $payment_intent->latest_charge )
			? $payment_intent->latest_charge
			: null;

		// A dispute (chargeback) raised outside the plugin -- e.g. a missed
		// charge.dispute.created webhook.
		if ( null !== $charge && $this->charge_has_unresolved_dispute( $charge ) ) {
			if ( 'disputed' !== $row->status ) {
				$this->transactions->update(
					(int) $row->id,
					array( 'status' => 'disputed' )
				);
			}

			return;
		}

		$refunded = $this->get_charge_amount_refunded( $payment_intent );

		// Locally disputed, but Stripe no longer has a dispute against us --
		// the `charge.dispute.closed` webhook was missed. Mirror what
		// TransactionObserver::dispute_closed() would have done, unless a
		// refund below describes the row better. A row that was already
		// partially refunded before the dispute goes back to `refunded`, not
		// `succeeded`, so it stays in the Partially Refunded view.
		if ( 'disputed' === $row->status && $refunded <= (int) $row->amount_refunded ) {
			$this->transactions->update(
				(int) $row->id,
				array(
					'status' => TransactionObserver::get_undisputed_status(
						(int) $row->amount_refunded
					),
				)
			);

			return;
		}

		if ( $refunded > (int) $row->amount_refunded ) {
			$this->transactions->update(
				(int) $row->id,
				array(
					'status'          => 'refunded',
					'amount_refunded' => $refunded,
				)
			);
		}
	}

	/**
	 * Determines whether a charge carries a dispute that is still against us.
	 *
	 * `Charge::$disputed` is documented as "whether the charge has been
	 * disputed" and is not a live flag: it stays true once a dispute exists,
	 * including after one closes in our favour. Reading it directly would
	 * therefore re-mark a row `disputed` on the next run after
	 * TransactionObserver::dispute_closed() had returned it to `succeeded`,
	 * flip-flopping the status every hour. The dispute's own status is the
	 * authority, so resolve it and mirror the observer: an open dispute or a
	 * `lost` one is still against us; `won`, `warning_closed` and `prevented`
	 * are not.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\Charge $charge Charge.
	 * @return bool
	 */
	private function charge_has_unresolved_dispute( $charge ) {
		// Nothing has ever been disputed: no need to ask Stripe.
		if ( ! isset( $charge->disputed ) || true !== $charge->disputed ) {
			return false;
		}

		if ( ! isset( $charge->id ) ) {
			return false;
		}

		$disputes = Disputes\all(
			array(
				'charge' => $charge->id,
				'limit'  => 1,
			),
			array( 'api_key' => simpay_get_secret_key() )
		);

		if ( ! isset( $disputes->data[0]->status ) ) {
			// The flag says there was a dispute but we cannot see it. Leave the
			// row alone rather than guessing at its state.
			return false;
		}

		return in_array(
			(string) $disputes->data[0]->status,
			self::UNRESOLVED_DISPUTE_STATUSES,
			true
		);
	}

	/**
	 * Returns the amount refunded on a PaymentIntent's latest charge.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent PaymentIntent.
	 * @return int
	 */
	private function get_charge_amount_refunded( $payment_intent ) {
		if (
			! isset( $payment_intent->latest_charge ) ||
			! is_object( $payment_intent->latest_charge ) ||
			! isset( $payment_intent->latest_charge->amount_refunded )
		) {
			return 0;
		}

		return (int) $payment_intent->latest_charge->amount_refunded;
	}

	/**
	 * Returns the maximum number of rows to process per reconciliation pass.
	 *
	 * @since 4.17.4
	 *
	 * @return int
	 */
	private function get_limit() {
		/** @var int $limit */
		$limit = (int) apply_filters( 'simpay_reconcile_stale_transactions_limit', 50 );

		return $limit < 1 ? 50 : $limit;
	}
}
