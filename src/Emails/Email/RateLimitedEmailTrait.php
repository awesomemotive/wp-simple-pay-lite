<?php
/**
 * Emails: Rate limited email trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.6
 */

namespace SimplePay\Core\Emails\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RateLimitedEmailTrait.
 *
 * Throttles how many emails of a single type are sent for a payment form
 * within a time window. Shared by the payment failure notification (admin)
 * and confirmation (customer) emails so a burst of declined payments cannot
 * flood the site admin or send email to arbitrary customer addresses.
 *
 * Hook names and transient keys are derived from the email ID, so each email
 * type is limited independently.
 *
 * @since 4.17.6
 */
trait RateLimitedEmailTrait {

	/**
	 * Returns the maximum number of emails that can be sent for a single form
	 * within the rate limit window.
	 *
	 * @since 4.17.6
	 *
	 * @param int|string $form_id Form ID.
	 * @return int Maximum number of emails. 0 or less disables the limit.
	 */
	public function get_rate_limit( $form_id ) {
		/**
		 * Filters the maximum number of emails of a rate limited type sent for
		 * a single form within the rate limit window.
		 *
		 * The dynamic portion of the hook name, `$email_key`, is the email ID
		 * with dashes replaced by underscores, e.g.
		 * `simpay_payment_failure_notification_email_rate_limit` or
		 * `simpay_payment_failure_confirmation_email_rate_limit`.
		 *
		 * Prevents a burst of declined payments (e.g. a card testing attack)
		 * from flooding the site admin, or from using the site to send email
		 * to arbitrary addresses. Return 0 to disable the limit.
		 *
		 * @since 4.17.6
		 *
		 * @param int        $limit   Maximum number of emails. Default 10.
		 * @param int|string $form_id Form ID.
		 */
		$limit = apply_filters(
			sprintf( 'simpay_%s_email_rate_limit', $this->get_rate_limit_email_key() ),
			10,
			$form_id
		);

		return (int) $limit;
	}

	/**
	 * Returns the rate limit window, in seconds.
	 *
	 * @since 4.17.6
	 *
	 * @param int|string $form_id Form ID.
	 * @return int
	 */
	public function get_rate_limit_window( $form_id ) {
		/**
		 * Filters the rate limit window of a rate limited email type.
		 *
		 * The dynamic portion of the hook name, `$email_key`, is the email ID
		 * with dashes replaced by underscores.
		 *
		 * @since 4.17.6
		 *
		 * @param int        $window  Window length, in seconds. Default 1 hour.
		 * @param int|string $form_id Form ID.
		 */
		$window = apply_filters(
			sprintf( 'simpay_%s_email_rate_limit_window', $this->get_rate_limit_email_key() ),
			HOUR_IN_SECONDS,
			$form_id
		);

		return max( 1, (int) $window );
	}

	/**
	 * Determines if the email rate limit has been reached for a form.
	 *
	 * @since 4.17.6
	 *
	 * @param int|string $form_id Form ID.
	 * @return bool
	 */
	public function is_rate_limited( $form_id ) {
		$limit = $this->get_rate_limit( $form_id );

		if ( $limit <= 0 ) {
			return false;
		}

		$usage = $this->get_rate_limit_usage( $form_id );

		return $usage['count'] >= $limit;
	}

	/**
	 * Records an email send towards the form's rate limit.
	 *
	 * @since 4.17.6
	 *
	 * @param int|string $form_id Form ID.
	 * @return void
	 */
	public function record_rate_limited_send( $form_id ) {
		$window = $this->get_rate_limit_window( $form_id );
		$usage  = $this->get_rate_limit_usage( $form_id );

		if ( 0 === $usage['count'] ) {
			$usage['start'] = time();
		}

		++$usage['count'];

		set_transient(
			$this->get_rate_limit_key( $form_id ),
			$usage,
			max( 1, $window - ( time() - $usage['start'] ) )
		);
	}

	/**
	 * Returns the current rate limit usage for a form.
	 *
	 * Usage from an expired window is treated as empty.
	 *
	 * @since 4.17.6
	 *
	 * @param int|string $form_id Form ID.
	 * @return array{count: int, start: int}
	 */
	private function get_rate_limit_usage( $form_id ) {
		$empty = array(
			'count' => 0,
			'start' => time(),
		);

		$usage = get_transient( $this->get_rate_limit_key( $form_id ) );

		if (
			! is_array( $usage ) ||
			! isset( $usage['count'], $usage['start'] )
		) {
			return $empty;
		}

		$start = (int) $usage['start'];

		if ( ( time() - $start ) >= $this->get_rate_limit_window( $form_id ) ) {
			return $empty;
		}

		return array(
			'count' => (int) $usage['count'],
			'start' => $start,
		);
	}

	/**
	 * Returns the rate limit transient key for a form.
	 *
	 * @since 4.17.6
	 *
	 * @param int|string $form_id Form ID.
	 * @return string
	 */
	private function get_rate_limit_key( $form_id ) {
		return sprintf(
			'simpay_%s_rate_%d',
			$this->get_rate_limit_email_key(),
			absint( $form_id )
		);
	}

	/**
	 * Returns the email ID formatted for use in hook names and transient keys.
	 *
	 * @since 4.17.6
	 *
	 * @return string
	 */
	private function get_rate_limit_email_key() {
		return str_replace( '-', '_', $this->get_id() );
	}
}
