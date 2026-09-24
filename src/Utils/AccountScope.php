<?php
/**
 * Utils: Connected account scope
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Utils;

/**
 * AccountScope class.
 *
 * The transactions and subscriptions tables hold rows from every Stripe
 * account the site has ever been connected to, and #3533 made the currently
 * connected one the boundary of what the admin pages may read. That boundary
 * is only worth as much as its least-scoped query, so the predicate lives
 * here rather than being restated per query: the list tables, the status
 * counts, the CSV export, the detail views, the refund handler and the
 * reconciler's passes all read through this one definition.
 *
 * @since 4.17.4
 */
class AccountScope {

	/**
	 * Returns the `WHERE` fragment restricting a query to the connected
	 * Stripe account, ready to append to an existing clause.
	 *
	 * Rows stamped with a different account are excluded; rows with no account
	 * (legacy/unstamped) are left visible -- the backfill migration stamps
	 * existing rows, so these only occur before an account exists. With no
	 * account connected there is nothing to scope to and the fragment is
	 * empty.
	 *
	 * The fragment is produced by `$wpdb->prepare()` and contains no `LIKE`
	 * wildcards, so it is safe to interpolate into a query.
	 *
	 * Both tables name the column `stripe_account_id`, so it is hard-coded
	 * here: nothing about the fragment is caller-supplied.
	 *
	 * @since 4.17.4
	 *
	 * @return string Either an ` AND ( ... )` fragment or an empty string.
	 */
	public static function get_where_fragment() {
		global $wpdb;

		$account_id = simpay_get_account_id();

		if ( ! is_string( $account_id ) || '' === $account_id ) {
			return '';
		}

		return $wpdb->prepare(
			" AND ( stripe_account_id = %s OR stripe_account_id IS NULL OR stripe_account_id = '' )",
			$account_id
		);
	}
}
