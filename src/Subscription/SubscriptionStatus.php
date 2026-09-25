<?php
/**
 * Subscriptions: Status helpers
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Subscription;

/**
 * SubscriptionStatus class.
 *
 * Single source of truth for how a Stripe Subscription status is labelled,
 * grouped into list views, and whether its period end is a renewal.
 *
 * @since 4.17.4
 */
class SubscriptionStatus {

	/**
	 * The period end is the next renewal.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	const PERIOD_END_RENEWS = 'renews';

	/**
	 * The period end is when the subscription stops.
	 *
	 * @since 4.17.4
	 * @var string
	 */
	const PERIOD_END_ENDS = 'ends';

	/**
	 * Statuses that will never bill again, so have no upcoming period end.
	 *
	 * `paused` is included: a paused subscription does not renew until it is
	 * resumed, so a date would read as a promise it will not keep.
	 *
	 * @since 4.17.4
	 * @var array<int, string>
	 */
	const NON_RENEWING = array( 'canceled', 'incomplete_expired', 'paused' );

	/**
	 * Returns every known status mapped to its label.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, string>
	 */
	public static function get_labels() {
		return array(
			'active'             => __( 'Active', 'stripe' ),
			'trialing'           => __( 'Trialing', 'stripe' ),
			'past_due'           => __( 'Past Due', 'stripe' ),
			'canceled'           => __( 'Canceled', 'stripe' ),
			'incomplete'         => __( 'Incomplete', 'stripe' ),
			'incomplete_expired' => __( 'Incomplete Expired', 'stripe' ),
			'unpaid'             => __( 'Unpaid', 'stripe' ),
			'paused'             => __( 'Paused', 'stripe' ),
		);
	}

	/**
	 * Returns the label for a status, falling back to a humanized key.
	 *
	 * @since 4.17.4
	 *
	 * @param string $status Status key.
	 * @return string
	 */
	public static function get_label( $status ) {
		$labels = self::get_labels();

		if ( isset( $labels[ $status ] ) ) {
			return $labels[ $status ];
		}

		return ucwords( str_replace( '_', ' ', $status ) );
	}

	/**
	 * Returns the list-table status views, keyed by view key, each with a
	 * label and the stored statuses it matches.
	 *
	 * Every status Stripe can report belongs to exactly one view, so the view
	 * counts always add up to the "All" count. `incomplete_expired` is folded
	 * into "Incomplete" rather than given a view of its own.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, array{label: string, statuses: array<int, string>}>
	 */
	public static function get_views() {
		$labels = self::get_labels();

		return array(
			'active'     => array(
				'label'    => $labels['active'],
				'statuses' => array( 'active' ),
			),
			'trialing'   => array(
				'label'    => $labels['trialing'],
				'statuses' => array( 'trialing' ),
			),
			'past_due'   => array(
				'label'    => $labels['past_due'],
				'statuses' => array( 'past_due' ),
			),
			'unpaid'     => array(
				'label'    => $labels['unpaid'],
				'statuses' => array( 'unpaid' ),
			),
			'incomplete' => array(
				'label'    => $labels['incomplete'],
				'statuses' => array( 'incomplete', 'incomplete_expired' ),
			),
			'paused'     => array(
				'label'    => $labels['paused'],
				'statuses' => array( 'paused' ),
			),
			'canceled'   => array(
				'label'    => $labels['canceled'],
				'statuses' => array( 'canceled' ),
			),
		);
	}

	/**
	 * Returns what a subscription's current period end means.
	 *
	 * @since 4.17.4
	 *
	 * @param string               $status               Status key.
	 * @param bool|int|string|null $cancel_at_period_end Whether it cancels at period end.
	 * @return string One of the PERIOD_END_* constants, or an empty string when
	 *                the period end should not be shown at all.
	 */
	public static function get_period_end_type( $status, $cancel_at_period_end ) {
		if ( in_array( $status, self::NON_RENEWING, true ) ) {
			return '';
		}

		return ! empty( $cancel_at_period_end )
			? self::PERIOD_END_ENDS
			: self::PERIOD_END_RENEWS;
	}
}
